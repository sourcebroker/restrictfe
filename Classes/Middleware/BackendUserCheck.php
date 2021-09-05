<?php

declare(strict_types=1);

namespace SourceBroker\Restrictfe\Middleware;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SourceBroker\Restrictfe\Configuration\ConfigBuilder;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendUserCheck implements MiddlewareInterface
{
    protected ConfigBuilder $configBuilder;
    private array $config;

    /**
     * @throws JsonException
     */
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
        $frontendBackendUser = $GLOBALS['BE_USER'];
        $cookieParams = $request->getCookieParams();
        if (!empty($frontendBackendUser->user)
            && !empty($frontendBackendUser->user['uid'])
            && !isset($cookieParams['tx_restrictfe'])) {
            $cookieValue = (string)GeneralUtility::md5int(
                substr($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'], random_int(1, 5), random_int(5, 10)) . time()
            );
            GeneralUtility::makeInstance(Registry::class)->set('tx_restrictfe', $cookieValue, true);
            setcookie(
                'tx_restrictfe',
                $cookieValue,
                $this->config['cookie']['expire'],
                $this->config['cookie']['path'],
                $this->config['cookie']['domain'],
                $this->config['cookie']['secure'],
                $this->config['cookie']['httponly']
            );
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['restrictfe']['beLogged'] = true;

//          TODO: so far code below does not set cookie

//                $cookie = new Cookie(
//                    'tx_restrictfe',
//                    $cookieValue,
//                    $this->config['cookie']['expire'],
//                    $this->config['cookie']['path'],
//                    $this->config['cookie']['domain'],
//                    $this->config['cookie']['secure'],
//                );
//                $response = $handler->handle($request);
//                return $response->withAddedHeader('set-cookie', $cookie->__toString());
        }
        if (!empty($frontendBackendUser->user['tx_restrictfe_clearbesession'])) {
            $frontendBackendUser->removeCookie($frontendBackendUser::getCookieName());
        }
        return $handler->handle($request);
    }

}
