<?php

declare(strict_types=1);

namespace Aidphp\Pipeline;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class HandlerMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    protected $handler;

    public function __construct(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function process(ServerRequestInterface $req, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handler->handle($req);
    }

    public function handle(ServerRequestInterface $req): ResponseInterface
    {
        return $this->handler->handle($req);
    }
}