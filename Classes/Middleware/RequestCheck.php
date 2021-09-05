<?php
declare(strict_types=1);

namespace SourceBroker\Restrictfe\Middleware;

use TYPO3\CMS\Core\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use SourceBroker\Restrictfe\Configuration\ConfigBuilder;
use Throwable;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class RequestCheck implements MiddlewareInterface
{
    protected ConfigBuilder $configBuilder;
    private StandaloneView $fluidStandalone;
    private array $config;
    private ServerRequestInterface $request;

    public function __construct(ConfigBuilder $configBuilder, StandaloneView $fluidStandalone)
    {
        $this->configBuilder = $configBuilder;
        $this->config = $this->configBuilder->get();
        $this->fluidStandalone = $fluidStandalone;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        $this->checkExceptionsAndBlockFrontendIfNeeded();
        return $handler->handle($request);
    }

    /**
     * Check for all exceptions defined and block frontend if needed
     */
    public function checkExceptionsAndBlockFrontendIfNeeded(): void
    {
        $blockFrontendAccess = true;
        if (isset($this->config['exceptions']) && is_array($this->config['exceptions'])
            && true === $this->checkRules($this->config['exceptions'])) {
            $blockFrontendAccess = false;
        }
        if (true === $blockFrontendAccess) {
            $templatePath = GeneralUtility::getFileAbsFileName($this->config['templatePath']);
            if (!file_exists($templatePath)) {
                throw new RuntimeException('Template file can not be found:' . $templatePath);
            }
            $this->fluidStandalone->setTemplatePathAndFilename($templatePath);
            $this->fluidStandalone->assign('beLoginLink', GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/');
            setcookie(
                'tx_restrictfe_redirect',
                GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
                0,
                '/',
                $this->config['cookie']['domain'],
                true,
                true);

            header('X-Robots-Tag: noindex,nofollow');
            header('HTTP/1.0 403 Access Forbidden');
            header('Content-Type: text/html; charset=utf-8');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $this->fluidStandalone->render();
            exit();
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
                    if ((int)$conditionValue === GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id')) {
                        $conditionResult = true;
                        break;
                    }
                }
                break;

            case '!sysLanguageUid':
                $conditionResults = [];
                foreach ($conditionValues as $conditionValue) {
                    if ((int)$conditionValue !== GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id')) {
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
                            /** @var Registry $registry */
                            $conditionResult = false;
                            if($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['beLogged'] === true) {
                                $conditionResult = true;
                            }
                            $cookieParams = $this->request->getCookieParams();
                            if (isset($cookieParams['tx_restrictfe'])) {
                                if (true === GeneralUtility::makeInstance(Registry::class)->get(
                                        'tx_restrictfe',
                                        (int)$cookieParams['tx_restrictfe'])
                                ) {
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
                        throw new RuntimeException('Extension restrictfe: The $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTCONF\'][\'restrictfe\'][\'exception\'][\'backendUser\'] must be boolean type.');
                    }
                }
                break;

            default:
                throw new RuntimeException('Extension restrictfe: The condition: "' . $conditionType . '" is not supported.');
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
