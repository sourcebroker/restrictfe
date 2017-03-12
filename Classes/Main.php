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

use \TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use \TYPO3\CMS\Core\Registry;
use \TYPO3\CMS\Core\Utility\ArrayUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class Main
 * @package SourceBroker\Restrictfe
 */
class Main
{

    /**
     * @var array
     */
    protected $config = [];

    /**
     * This is hook that is called at the very beginning of the FE rendering process
     *
     * @param $_params
     * @param $pObj
     * @return void
     * @throws \Exception
     */
    public function redirectCheckForLoggedBeUser($_params, &$pObj)
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe'])) {
            // Hook that allows you to enable restrictfe for example for database conditions.
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restrictfe/Class/Main.php']['restrictfe-PreProc'])) {
                $_paramsHook = ['pObj' => &$pObj];
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restrictfe/Class/Main.php']['restrictfe-PreProc'] as $_funcRef) {
                    GeneralUtility::callUserFunction($_funcRef, $_paramsHook, $this);
                }
            }

            $this->config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe'];

            // Merge external config with defulat conifg
            ArrayUtility::mergeRecursiveWithOverrule(
                $this->config,
                [
                    'mode' => 'deny', // By default the access to frontend is blocked. You need to remember to unblock it for live instance.
                    'templatePath' => ExtensionManagementUtility::siteRelPath('restrictfe') . 'Resources/Private/Templates/show.html',
                    'cookie' => [
                        'expire' => time() + 86400 * 30,
                        'path' => '/',
                        'domain' => null,
                        'secure' => false,
                        'httponly' => true
                    ],
                ]);

            if (!in_array($this->config['mode'], ['allow', 'deny'])) {
                throw new \Exception('Extension restrictfe: The $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTCONF\'][\'restrictfe\'][\'mode\'] is wrong. 
                                The value must be either "block" or "show" but its set now to: ' . $this->config['mode']);
            }
            $blockFrontendAccess = $this->config['mode'] === 'deny' ? true : false;
            // The config['enable'] has precedence over 'mode' config with 'rule'. Do not set it if you like to use mode/rule.
            if (isset($this->config['enable'])) {
                if (is_bool($this->config['enable'])) {
                    if (true === $this->config['enable']) {
                        $blockFrontendAccess = true;
                    } else {
                        $blockFrontendAccess = false;
                    }
                } else {
                    throw new \Exception('Extension restrictfe: The $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTCONF\'][\'restrictfe\'] must be boolean type.');
                }
            } else {
                if (isset($this->config['rule']) && is_array($this->config['rule'])) {
                    if (true == $this->checkRules($this->config['rule'])) {
                        switch ($this->config['mode']) {
                            case 'deny':
                                $blockFrontendAccess = false;
                                break;
                            case 'allow':
                                $blockFrontendAccess = true;
                                break;
                        }
                    }
                }
            }

            if (true === $blockFrontendAccess) {
                /** @var \TYPO3\CMS\Core\Registry $registry */
                $registry = GeneralUtility::makeInstance(Registry::class);

                if (!isset($_COOKIE['tx_restrictfe']) || true !== $registry->get('tx_restrictfe', intval($_COOKIE['tx_restrictfe']))) {

                    // Restrictfe enabled and no tx_restrictfe cookie set and no BE user logged so we will show warning screen
                    if (!$pObj->beUserLogin) {
                        if (file_exists(PATH_site . $this->config['templatePath'])) {
                            $templatePath = $this->config['templatePath'];
                        } else {
                            throw new \Exception('Template file can not be found:' . PATH_site . $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['templatePath']);
                        }
                        // TODO: choose label language based on browser headers
                        $renderObj = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
                        $renderObj->setTemplatePathAndFilename(PATH_site . $templatePath);
                        $renderObj->assign('beLoginLink', GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/index.php?redirect_url=' . GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));

                        header('X-Robots-Tag: noindex,nofollow');
                        header('HTTP/1.0 403 Access Forbidden');
                        header('Content-Type: text/html; charset=utf-8');
                        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                        header('Cache-Control: no-store, no-cache, must-revalidate');
                        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
                        header('Pragma: no-cache');
                        header('Expires: 0');

                        echo $renderObj->render();
                        exit;
                    } else {
                        // We clear cookie of BE user in order to only authorize him in FE.
                        // That means that you can create special BE account that have no privilages at all
                        // and is used only for purspose to show frontend.
                        $beUserRow = $GLOBALS['BE_USER']->user;
                        if (isset($beUserRow['tx_restrictfe_clearbesession']) && $beUserRow['tx_restrictfe_clearbesession']) {
                            setcookie(BackendUserAuthentication::getCookieName(), null, 1);
                        }
                        // create random cookie value and set it in registry to later check if this cookie has right to see frontend
                        $cookieValue = GeneralUtility::md5int(
                            substr($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'], rand(1, 5), rand(5, 10)) . time()
                        );
                        setcookie(
                            'tx_restrictfe',
                            $cookieValue,
                            $this->config['cookie']['expire'],
                            $this->config['cookie']['path'],
                            $this->config['cookie']['domain'],
                            $this->config['cookie']['secure'],
                            $this->config['cookie']['httponly']);
                        $registry->set('tx_restrictfe', $cookieValue, true);
                    }
                }
            }
        }
    }


    /**
     * @param $conditionType
     * @param $conditionValues
     * @return bool
     */
    protected function checkCondition($conditionType, $conditionValues)
    {
        if (!is_array($conditionValues)) {
            $conditionValues = [$conditionValues];
        }
        $conditionResult = false;
        switch ($conditionType) {
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
                if (1 === count(array_unique($conditionResults)) && reset(array_unique($conditionResults)) === true) {
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
                if (1 === count(array_unique($conditionResults)) && reset(array_unique($conditionResults)) === true) {
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
                if (1 === count(array_unique($conditionResults)) && reset(array_unique($conditionResults)) === true) {
                    $conditionResult = true;
                }
                break;

            case 'domainPregmatch':
                foreach ($conditionValues as $conditionValue) {
                    if (preg_match($conditionValue, GeneralUtility::getIndpEnv('TYPO3_SITE_URL'))) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case 'header':
                foreach ($conditionValues as $conditionValue) {
                    list($headerName, $headerValue) = explode('=', $conditionValue);
                    if ($headerValue == $_SERVER[$headerName]) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;
        }
        return $conditionResult;
    }


    /**
     * @param $conditions
     * @param string $type
     * @return bool
     */
    protected function checkRules($conditions, $type = 'OR')
    {
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
}