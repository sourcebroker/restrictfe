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

use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendUserAuthentication
{
    /**
     * Stores tx_restrictfe cookie after sucessful BE login. Logout user if tx_restrictfe_clearbesession set
     * in user profile.
     *
     * @param $params array Parameters passed from hook. It holds AbstractUserAuthentication in pObj key.
     */
    public function storeRestrictfeCookieAfterSuccessfulBeLogin($params)
    {
        /* @var $backendUserAuthentication \TYPO3\CMS\Core\Authentication\BackendUserAuthentication */
        $backendUserAuthentication = $params['pObj'];
        // TODO: 'user' is annotated as @internal - try not to use internal if possible
        if ($backendUserAuthentication instanceof \TYPO3\CMS\Core\Authentication\BackendUserAuthentication) {
            if (!empty($backendUserAuthentication->user)
                && !empty($backendUserAuthentication->user['uid'])
                && !isset($_COOKIE['tx_restrictfe'])) {
                $cookieValue = GeneralUtility::md5int(
                    substr($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'], rand(1, 5), rand(5, 10)) . time()
                );
                $config = GeneralUtility::makeInstance(Config::class)->getAll();
                setcookie(
                    'tx_restrictfe',
                    $cookieValue,
                    $config['cookie']['expire'],
                    $config['cookie']['path'],
                    $config['cookie']['domain'],
                    $config['cookie']['secure'],
                    $config['cookie']['httponly']);
                GeneralUtility::makeInstance(Registry::class)->set('tx_restrictfe', $cookieValue, true);
            }
            if (!empty($backendUserAuthentication->user['tx_restrictfe_clearbesession'])) {
                $backendUserAuthentication->removeCookie($backendUserAuthentication->getCookieName());
            }
        }
    }
}
