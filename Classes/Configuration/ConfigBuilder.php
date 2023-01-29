<?php
declare(strict_types=1);

namespace SourceBroker\Restrictfe\Configuration;

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

use JsonException;
use RuntimeException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigBuilder
{
    /**
     * @throws JsonException
     */
    public function get(): array
    {
        $config = [
            'templatePath' => 'EXT:restrictfe/Resources/Private/Templates/Restricted.html',
            'cookie' => [
                'expire' => time() + 86400 * 30,
                'path' => '/',
                'domain' => '',
                'secure' => GeneralUtility::getIndpEnv('TYPO3_SSL'),
                'httponly' => true,
            ],
            'exceptions' => [
                'ip' => ['127.0.0.1', '192.168.0.0/16', '172.16.0.0/12', '10.0.0.0/8'],
                'backendUser' => true,
            ],
        ];

        // Merge external config with default conifg
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe'])) {
            if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exeptions']) || !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['exception'])) {
                throw new RuntimeException('You have typo in config name. You set "exeptions" or "exception" instead of "exceptions". ' . json_encode($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe'], JSON_THROW_ON_ERROR));
            }
            ArrayUtility::mergeRecursiveWithOverrule(
                $config,
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']
            );
        }

        if (isset($config['enable'])) {
            throw new RuntimeException('Extension restrictfe: The $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTCONF\'][\'restrictfe\'][\'enable\'] is deprecated. Read docs on https://github.com/sourcebroker/restrictfe');
        }
        return $config;
    }
}
