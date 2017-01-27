<?php
namespace SourceBroker\Restrictfe;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016
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

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class Main
 * @package SourceBroker\Restrictfe
 */
class Main
{
    /**
     * @var string
     */
    protected $extKey = 'restrictfe';
    /**
     * @var string
     */
    protected $restrictfeEmConf = '';
    /**
     * @var \SourceBroker\Restrictfe\Helper;
     */
    protected $helper;

    /**
     * This is hook that is called at the very beginning of the FE rendering process
     *
     * @param $_params
     * @param $pObj
     *
     * @return void
     */
    public function redirectCheckForLoggedBeUser($_params, &$pObj)
    {
        // hook that allows you to enable restrictfe for example for database conditions
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restrictfe/Class/Main.php']['restrictfe-PreProc'])) {
            $_params = ['pObj' => &$pObj];
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['restrictfe/Class/Main.php']['restrictfe-PreProc'] as $_funcRef) {
                GeneralUtility::callUserFunction($_funcRef, $_params, $this);
            }
        }

        if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['enable']) {
            $this->helper = GeneralUtility::makeInstance('SourceBroker\Restrictfe\Helper');
            $this->restrictfeEmConf = @unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);

            // 1 Early out on IP
            if (trim($this->restrictfeEmConf['ipAllowed'])) {
                if (GeneralUtility::cmpIP(GeneralUtility::getIndpEnv('REMOTE_ADDR'),
                    $this->restrictfeEmConf['ipAllowed'])
                ) {
                    return;
                }
            }

            // 2. Early out on header
            $headerAllowedArray = [];
            if (trim($this->restrictfeEmConf['headerAllowed'])) {
                $headerAllowedArray = GeneralUtility::trimExplode(',',
                    trim($this->restrictfeEmConf['headerAllowed']));
            }
            if (in_array(trim($_SERVER['HTTP_TX_RESTRICTFE']),
                $headerAllowedArray)) {
                return;
            }

            // 3. Standard check for cookie presence
            $isTheRightCookieSet = false;

            if ($_COOKIE['tx_restrictfe']) {
                /** @var \TYPO3\CMS\Core\Registry $registry */
                $registry = GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');
                $values = $registry->get('tx_restrictfe',
                    intval($_COOKIE['tx_restrictfe']));
                if ($values['cookieSet']) {
                    $isTheRightCookieSet = true;
                }
            }

            if (!$isTheRightCookieSet) {

                if (!$pObj->beUserLogin) {

                    $renderObj = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\View\StandaloneView');
                    $renderObj->setTemplatePathAndFilename(PATH_site.$this->getRelativeTemplatePath().'show.html');
                    $renderObj->assign('beLoginLink',
                        GeneralUtility::getIndpEnv('TYPO3_SITE_URL').'typo3/index.php?redirect_url='.GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
                    $this->helper->setNoCacheHeaders();
                    $this->helper->setNoIndexHeaders();
                    $this->helper->setAccessForbiddenHeaders();

                    echo $renderObj->render();
                    exit;
                } else {

                    // We clear cookie of BE user in order to only authorize him in FE. That means that there is need for special BE account that have no privilages at all and is used only for that purpose.
                    $beUserRow = $this->helper->getBeUserRow();
                    if ($beUserRow['tx_restrictfe_clearbesession']) {
                        setcookie('be_typo_user', null, 1);
                    }

                    // create random cookie value and set it in registry to later check if this cookie has right to see frontend
                    $cookieValue = GeneralUtility::md5int(substr($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'],
                            rand(1, 5), rand(5, 10)).time());
                    setcookie('tx_restrictfe', $cookieValue,
                        time() + 86400 * 100, '/', null, null, true);

                    /** @var \TYPO3\CMS\Core\Registry $registry */
                    $registry = GeneralUtility::makeInstance('TYPO3\CMS\Core\Registry');
                    $values = ['cookieSet' => 1];
                    $registry->set('tx_restrictfe', $cookieValue, $values);

                    header('Location:'.'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
                    exit;
                }
            }
        }
    }

    /**
     * @return string
     */
    protected function getRelativeTemplatePath()
    {
        if (isset($this->restrictfeEmConf['templatesRelativePath']) && strlen(trim($this->restrictfeEmConf['templatesRelativePath']))) {
            $relativeTemplatePath = $this->restrictfeEmConf['templatesRelativePath'];
        } else {
            $relativeTemplatePath = ExtensionManagementUtility::siteRelPath($this->extKey).'Resources/Private/Templates/';
        }

        return $relativeTemplatePath;
    }
}