<?php

declare(strict_types=1);

namespace SourceBroker\Restrictfe\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendRedirect implements MiddlewareInterface
{
    protected Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws AspectNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->context->getAspect('backend.user')->isLoggedIn()) {
            $cookieParams = $request->getCookieParams();
            if(isset($cookieParams['tx_restrictfe_redirect'])) {
                setcookie('tx_restrictfe_redirect', '', -1, '/');
                $returnUrl = GeneralUtility::sanitizeLocalUrl($cookieParams['tx_restrictfe_redirect']);
                if($returnUrl) {
                    return new RedirectResponse($returnUrl);
                }
            }
        }
        return $handler->handle($request);
    }
}

