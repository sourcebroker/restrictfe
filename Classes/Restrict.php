<?php

namespace SourceBroker\Restrictfe;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Krystian Szymukowicz
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class Restrict.
 */
class Restrict
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * This is hook that is called at the very beginning of the FE rendering process.
     *
     * @param $_params
     * @param $pObj
     *
     * @throws \Exception
     *
     * @return void
     */
    public function restrictFrontend($_params, &$pObj)
    {
        $this->config = [
            'templatePath' => ExtensionManagementUtility::siteRelPath('restrictfe').'Resources/Private/Templates/Restricted.html',
            'cookie'       => [
                'expire'   => time() + 86400 * 30,
                'path'     => '/',
                'domain'   => null,
                'secure'   => false,
                'httponly' => true,
            ],
            'exceptions' => [
                'backendUser' => true,
                'ip'          => '127.0.0.1',
            ],
        ];

        // Merge external config with defulat conifg
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe'])) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exeptions'])) {
                throw new \Exception('You have typo in config name. You set "exeptions" instead of "exceptions". '.json_encode($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']));
            }
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exception'])) {
                throw new \Exception('You have typo in config name. You set "exception" instead of "exceptions". '.json_encode($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']));
            }
            ArrayUtility::mergeRecursiveWithOverrule(
                $this->config,
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']
            );
        }

        // Hook that allows you to overwrite the config with some other data than available in ext_localconf.php and set in $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restrictfe/Class/Main.php']['restrictfe-PreProc'])) {
            $_paramsHook = ['pObj' => &$pObj];
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restrictfe/Class/Main.php']['restrictfe-PreProc'] as $_funcRef) {
                GeneralUtility::callUserFunction($_funcRef, $_paramsHook, $this);
            }
        }

        // By default access to frontend is blocked
        $blockFrontendAccess = true;
        // The 'enable' from previous versions of extension is deprecated
        if (isset($this->config['enable'])) {
            throw new \Exception('Extension restrictfe: The $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTCONF\'][\'restrictfe\'][\'enable\'] is deprecated. Read docs on https://github.com/sourcebroker/restrictfe');
        } else {
            if (isset($this->config['exceptions']) && is_array($this->config['exceptions'])) {
                if (true == $this->checkRules($this->config['exceptions'])) {
                    $blockFrontendAccess = false;
                }
            }
        }

        if (true === $blockFrontendAccess) {
            if (file_exists(PATH_site.$this->config['templatePath'])) {
                $templatePath = $this->config['templatePath'];
            } else {
                throw new \Exception('Template file can not be found:'.PATH_site.$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['templatePath']);
            }
            // TODO: choose label language based on browser headers
            $renderObj = GeneralUtility::makeInstance(StandaloneView::class);
            $renderObj->setTemplatePathAndFilename(PATH_site.$templatePath);
            $renderObj->assign('beLoginLink', GeneralUtility::getIndpEnv('TYPO3_SITE_URL').'typo3/index.php?redirect_url='.GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));

            header('X-Robots-Tag: noindex,nofollow');
            header('HTTP/1.0 403 Access Forbidden');
            header('Content-Type: text/html; charset=utf-8');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $renderObj->render();
            exit;
        }
    }

    /**
     * @param $conditionType
     * @param $conditionValues
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function checkCondition($conditionType, $conditionValues)
    {
        if (!is_array($conditionValues)) {
            $conditionValues = [$conditionValues];
        }
        $conditionResult = false;
        switch ($conditionType) {
            // Special condition. It will just return value of the condition
            case '*':
                foreach ($conditionValues as $conditionValue) {
                    $conditionResult = $conditionValue;
                    break;
                }
                break;

            case 'get':
                foreach ($conditionValues as $conditionValue) {
                    list($getName, $getValue) = explode('=', $conditionValue);
                    if (GeneralUtility::_GET(trim($getName)) == trim($getValue)) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!get':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    list($getName, $getValue) = explode('=', $conditionValue);
                    if (GeneralUtility::_GET(trim($getName)) != trim($getValue)) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                if (1 === count(array_unique($conditionResults)) && true === reset(array_unique($conditionResults))) {
                    $conditionResult = true;
                }
                break;

            case 'post':
                foreach ($conditionValues as $conditionValue) {
                    list($postName, $postValue) = explode('=', $conditionValue);
                    if (GeneralUtility::_POST(trim($postName)) == trim($postValue)) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!post':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    list($postName, $postValue) = explode('=', $conditionValue);
                    if (GeneralUtility::_POST(trim($postName)) != trim($postValue)) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                if (1 === count(array_unique($conditionResults)) && true === reset(array_unique($conditionResults))) {
                    $conditionResult = true;
                }
                break;

            case 'requestUri':
                foreach ($conditionValues as $conditionValue) {
                    if (GeneralUtility::isFirstPartOfStr(GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT'), trim($conditionValue))) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!requestUri':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    if (!GeneralUtility::isFirstPartOfStr(GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT'), trim($conditionValue))) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                if (1 === count(array_unique($conditionResults)) && true === reset(array_unique($conditionResults))) {
                    $conditionResult = true;
                }
                break;

            case 'ip':
                foreach ($conditionValues as $conditionValue) {
                    if (GeneralUtility::cmpIP(GeneralUtility::getIndpEnv('REMOTE_ADDR'), $conditionValue)) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!ip':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    if (!GeneralUtility::cmpIP(GeneralUtility::getIndpEnv('REMOTE_ADDR'), $conditionValue)) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                if (1 === count(array_unique($conditionResults)) && true === reset(array_unique($conditionResults))) {
                    $conditionResult = true;
                }
                break;

            case 'sysLanguageUid':
                foreach ($conditionValues as $conditionValue) {
                    if ($conditionValue == $GLOBALS['TSFE']->sys_language_uid) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!sysLanguageUid':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    if (intval($conditionValue) !== $GLOBALS['TSFE']->sys_language_uid) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                if (1 === count(array_unique($conditionResults)) && true === reset(array_unique($conditionResults))) {
                    $conditionResult = true;
                }
                break;

            case 'domain':
                foreach ($conditionValues as $conditionValue) {
                    if ($conditionValue == GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!domain':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    if ($conditionValue != GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                if (1 === count(array_unique($conditionResults)) && true === reset(array_unique($conditionResults))) {
                    $conditionResult = true;
                }
                break;

            case 'header':
                foreach ($conditionValues as $conditionValue) {
                    list($headerName, $headerValue) = explode('=', $conditionValue);
                    if (trim($headerValue) == $_SERVER['HTTP_'.trim($headerName)]) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!header':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    list($headerName, $headerValue) = explode('=', $conditionValue);
                    if (trim($headerValue) != $_SERVER['HTTP_'.trim($headerName)]) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                if (1 === count(array_unique($conditionResults)) && true === reset(array_unique($conditionResults))) {
                    $conditionResult = true;
                }
                break;

            case 'backendUser':
                foreach ($conditionValues as $conditionValue) {
                    if (is_bool($conditionValue)) {
                        if (true === $conditionValue) {
                            /** @var \TYPO3\CMS\Core\Registry $registry */
                            $registry = GeneralUtility::makeInstance(Registry::class);
                            if (isset($_COOKIE['tx_restrictfe']) || true == $registry->get('tx_restrictfe', intval($_COOKIE['tx_restrictfe']))) {
                                $conditionResult = true;
                            } elseif (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['backendUserRow'])) {
                                // We clear cookie of BE user in order to only authorize him in FE.
                                // That means that you can create special BE account that have no privilages at all
                                // and is used only for purspose to show frontend, let it name "preview" account.
                                if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['backendUserRow']['tx_restrictfe_clearbesession'])) {
                                    setcookie(BackendUserAuthentication::getCookieName(), null, 1);
                                }
                                // create random cookie value and set it in registry to later check if this cookie has right to see frontend
                                $cookieValue = GeneralUtility::md5int(
                                    substr($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'], rand(1, 5), rand(5, 10)).time()
                                );
                                setcookie(
                                    'tx_restrictfe',
                                    $cookieValue,
                                    $this->config['cookie']['expire'],
                                    $this->config['cookie']['path'],
                                    $this->config['cookie']['domain'],
                                    $this->config['cookie']['secure'],
                                    $this->config['cookie']['httponly']);
                                GeneralUtility::makeInstance(Registry::class)->set('tx_restrictfe', $cookieValue, true);
                                $conditionResult = true;
                            }
                        }
                    } else {
                        throw new \Exception('Extension restrictfe: The $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTCONF\'][\'restrictfe\'][\'exception\'][\'backendUser\'] must be boolean type.');
                    }
                }
                break;

            default:
                throw new \Exception('Extension restrictfe: The condition: "'.$conditionType.'" is not supported.');
        }

        return $conditionResult;
    }

    /**
     * @param $conditions
     * @param string $type
     *
     * @return bool
     */
    protected function checkRules($conditions, $type = 'OR')
    {
        // early return for always true
        if (in_array('*', array_keys($conditions))) {
            return true;
        }
        $conditionResults = [];
        foreach ($conditions as $conditionType => $conditionValue) {
            if ($conditionType == 'AND' || $conditionType == 'OR') {
                $conditionResult = $this->checkRules($conditionValue, $conditionType);
            } else {
                $conditionResult = $this->checkCondition($conditionType, $conditionValue);
            }
            $conditionResults[$conditionType] = $conditionResult;
            if ('OR' == $type && true === $conditionResult) {
                break;
            }
        }
        $finalResult = false;
        switch ($type) {
            case 'AND':
                if (count(array_unique(array_values($conditionResults))) === 1 && reset($conditionResults) === true) {
                    $finalResult = true;
                }
                break;

            case 'OR':
                if (count(array_unique(array_values($conditionResults))) === 2
                    || count(array_unique(array_values($conditionResults))) === 1 && reset($conditionResults) !== false
                ) {
                    $finalResult = true;
                }
                break;
        }

        return $finalResult;
    }


    /**
     * Store BE_USER just after authorization because later in typo3/sysext/frontend/Classes/Http/RequestHandler.php
     * BE_USER can be unset if he has no access to page tree, but we do not care about acceess to page tree
     * for restrictfe. We only want to know if user logged sucessfully.
     *
     * @param $params array Parameters passed from hook. It holds BE_USER key with Backend User Object.
     */
    public function storeBackendUserRow($params) {
        if(!empty($params['BE_USER']->user) && !empty($params['BE_USER']->user['uid'])) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['backendUserRow'] = $params['BE_USER']->user;
        } else {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['backendUserRow'] = null;
        }
    }
}
