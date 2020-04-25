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

use Exception;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class RestrictFrontend
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * Check for all exceptions defiend and block frontend if needed
     *
     * @throws Exception
     */
    public function checkExceptionsAndBlockFrontendIfNeeded(): void
    {
        $this->config = GeneralUtility::makeInstance(Config::class)->getAll();
        $blockFrontendAccess = true;
        if (isset($this->config['exceptions']) && is_array($this->config['exceptions'])
            && true === $this->checkRules($this->config['exceptions'])) {
            $blockFrontendAccess = false;
        }
        if (true === $blockFrontendAccess) {
            $templatePath = GeneralUtility::getFileAbsFileName($this->config['templatePath']);
            if (!file_exists($templatePath)) {
                throw new Exception('Template file can not be found:' . $templatePath);
            }
            // TODO: choose label language based on browser headers
            $renderObj = GeneralUtility::makeInstance(StandaloneView::class);
            $renderObj->setTemplatePathAndFilename($templatePath);
            $renderObj->assign('beLoginLink',
                GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/index.php?redirect_url=' . GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));

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
            die();
        }
    }

    /**
     * @param string $conditionType
     * @param mixed $conditionValues
     * @return bool
     */
    protected function checkCondition(string $conditionType, $conditionValues): bool
    {
        if (!is_array($conditionValues)) {
            $conditionValues = [$conditionValues];
        }
        $conditionResult = false;
        switch ($conditionType) {
            // Special condition. It will just return value of the condition
            case '*':
                $conditionResult = $conditionValues[0];
                break;

            case 'get':
                foreach ($conditionValues as $conditionValue) {
                    [$getName, $getValue] = explode('=', $conditionValue);
                    if (GeneralUtility::_GET(trim($getName)) === trim($getValue)) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!get':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    [$getName, $getValue] = explode('=', $conditionValue);
                    if (GeneralUtility::_GET(trim($getName)) !== trim($getValue)) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                $uniqueConditionResults = array_unique($conditionResults);
                if (1 === count($uniqueConditionResults) && true === reset($uniqueConditionResults)) {
                    $conditionResult = true;
                }
                break;

            case 'post':
                foreach ($conditionValues as $conditionValue) {
                    [$postName, $postValue] = explode('=', $conditionValue);
                    if (GeneralUtility::_POST(trim($postName)) === trim($postValue)) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!post':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    [$postName, $postValue] = explode('=', $conditionValue);
                    if (GeneralUtility::_POST(trim($postName)) !== trim($postValue)) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                $uniqueConditionResults = array_unique($conditionResults);
                if (1 === count($uniqueConditionResults) && true === reset($uniqueConditionResults)) {
                    $conditionResult = true;
                }
                break;

            case 'requestUri':
                foreach ($conditionValues as $conditionValue) {
                    if (GeneralUtility::isFirstPartOfStr(GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT'),
                        trim($conditionValue))) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!requestUri':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    if (!GeneralUtility::isFirstPartOfStr(GeneralUtility::getIndpEnv('TYPO3_SITE_SCRIPT'),
                        trim($conditionValue))) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                $uniqueConditionResults = array_unique($conditionResults);
                if (1 === count($uniqueConditionResults) && true === reset($uniqueConditionResults)) {
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
                $uniqueConditionResults = array_unique($conditionResults);
                if (1 === count($uniqueConditionResults) && true === reset($uniqueConditionResults)) {
                    $conditionResult = true;
                }
                break;

            case 'sysLanguageUid':
                foreach ($conditionValues as $conditionValue) {
                    if ((int)$conditionValue === $GLOBALS['TSFE']->sys_language_uid) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!sysLanguageUid':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    if ((int)$conditionValue !== $GLOBALS['TSFE']->sys_language_uid) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                $uniqueConditionResults = array_unique($conditionResults);
                if (1 === count($uniqueConditionResults) && true === reset($uniqueConditionResults)) {
                    $conditionResult = true;
                }
                break;

            case 'domain':
                foreach ($conditionValues as $conditionValue) {
                    if ($conditionValue === GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!domain':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    if ($conditionValue !== GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY')) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                $uniqueConditionResults = array_unique($conditionResults);
                if (1 === count($uniqueConditionResults) && true === reset($uniqueConditionResults)) {
                    $conditionResult = true;
                }
                break;

            case 'header':
                foreach ($conditionValues as $conditionValue) {
                    [$headerName, $headerValue] = explode('=', $conditionValue);
                    if (trim($headerValue) === $this->getHeaderValue($headerName)) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!header':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    [$headerName, $headerValue] = explode('=', $conditionValue);
                    if (trim($headerValue) !== $this->getHeaderValue($headerName)) {
                        $conditionResults[] = true;
                    } else {
                        $conditionResults[] = false;
                    }
                }
                $uniqueConditionResults = array_unique($conditionResults);
                if (1 === count($uniqueConditionResults) && true === reset($uniqueConditionResults)) {
                    $conditionResult = true;
                }
                break;

            case 'backendUser':
                foreach ($conditionValues as $conditionValue) {
                    if (is_bool($conditionValue)) {
                        if (true === $conditionValue) {
                            /** @var \TYPO3\CMS\Core\Registry $registry */
                            $conditionResult = false;
                            if (isset($_COOKIE['tx_restrictfe'])) {
                                if (true === GeneralUtility::makeInstance(Registry::class)->get(
                                        'tx_restrictfe',
                                        (int)$_COOKIE['tx_restrictfe'])) {
                                    $conditionResult = true;
                                } else {
                                    // Cookie exist but is wrong so unset it.
                                    setcookie(
                                        'tx_restrictfe',
                                        null,
                                        $this->config['cookie']['expire'],
                                        $this->config['cookie']['path'],
                                        $this->config['cookie']['domain'],
                                        $this->config['cookie']['secure'],
                                        $this->config['cookie']['httponly']);
                                }
                            }
                        }
                    } else {
                        throw new \RuntimeException('Extension restrictfe: The $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTCONF\'][\'restrictfe\'][\'exception\'][\'backendUser\'] must be boolean type.');
                    }
                }
                break;

            default:
                throw new \RuntimeException('Extension restrictfe: The condition: "' . $conditionType . '" is not supported.');
        }
        return $conditionResult;
    }

    /**
     * @param $conditions
     * @param string $type
     *
     * @return bool
     */
    protected function checkRules($conditions, string $type = 'OR'): bool
    {
        // early return for always true
        if (array_key_exists('*', $conditions)) {
            return true;
        }
        $conditionResults = [];
        foreach ($conditions as $conditionType => $conditionValue) {
            if ($conditionType === 'AND' || $conditionType === 'OR') {
                $conditionResult = $this->checkRules($conditionValue, $conditionType);
            } else {
                $conditionResult = $this->checkCondition($conditionType, $conditionValue);
            }
            $conditionResults[$conditionType] = $conditionResult;
            if ('OR' === $type && true === $conditionResult) {
                break;
            }
        }
        $finalResult = false;
        switch ($type) {
            case 'AND':
                if (reset($conditionResults) === true && count(array_unique(array_values($conditionResults))) === 1) {
                    $finalResult = true;
                }
                break;
            case 'OR':
                if (count(array_unique(array_values($conditionResults))) === 2
                    || (count(array_unique(array_values($conditionResults))) === 1 && reset($conditionResults) !== false)
                ) {
                    $finalResult = true;
                }
                break;
        }
        return $finalResult;
    }

    protected function getHeaderValue(string $headerName): ?string
    {
        $headerName = 'http_' . str_replace('-', '_', strtolower(trim($headerName)));
        $tmpServer = [];
        foreach ($_SERVER as $key => $value) {
            $tmpServer[str_replace('-', '_', strtolower($key))] = $value;
        }
        return !empty($tmpServer[$headerName]) ? $tmpServer[$headerName] : null;
    }
}
