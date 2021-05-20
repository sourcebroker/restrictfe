<?php

declare(strict_types=1);

namespace SourceBroker\Restrictfe\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\Restrictfe\Configuration\ConfigBuilder;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendUserCheck implements MiddlewareInterface
{

    protected ConfigBuilder $configBuilder;
    private array $config;

    public function __construct(ConfigBuilder $configBuilder)
    {
        $this->configBuilder = $configBuilder;
        $this->config = $this->configBuilder->get();
    }

    /**
     * Creates a backend user authentication object, tries to authenticate a user
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $frontendBackendUserAuthentication = $GLOBALS['BE_USER'];
        if ($frontendBackendUserAuthentication instanceof FrontendBackendUserAuthentication) {
            if (!empty($frontendBackendUserAuthentication->user)
                && !empty($frontendBackendUserAuthentication->user['uid'])
                && !isset($_COOKIE['tx_restrictfe'])) {
                $cookieValue = (string)GeneralUtility::md5int(
                    substr($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'], random_int(1, 5), random_int(5, 10)) . time()
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
            }
            if (!empty($frontendBackendUserAuthentication->user['tx_restrictfe_clearbesession'])) {
                $frontendBackendUserAuthentication->removeCookie($frontendBackendUserAuthentication::getCookieName());
            }
        }
        return $handler->handle($request);
    }

}
