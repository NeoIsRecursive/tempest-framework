<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

use Tempest\Http\HttpMiddleware;
use Tempest\Http\Request;
use Tempest\Http\Response;

final readonly class SetCookieMiddleware implements HttpMiddleware
{
    public function __construct()
    {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        foreach ($response->getCookies() as $cookie) {
            $response->addHeader('set-cookie', (string) $cookie);
        }

        return $response;
    }
}
