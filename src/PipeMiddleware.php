<?php

declare(strict_types=1);

namespace Aidphp\Pipeline;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class PipeMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    protected $stack = [];

    public function __construct(array $middlewares = [])
    {
        foreach ($middlewares as $middleware)
        {
            $this->register($middleware);
        }
    }

    public function register(MiddlewareInterface $middleware): self
    {
        $this->stack[] = $middleware;
        return $this;
    }

    public function handle(ServerRequestInterface $req): ResponseInterface
    {
        if (! $this->stack)
        {
            throw new RuntimeException('The pipe is empty');
        }

        $pipe = clone $this;
        $middleware = array_shift($pipe->stack);
        return $middleware->process($req, $pipe);
    }

    public function process(ServerRequestInterface $req, RequestHandlerInterface $handler): ResponseInterface
    {
        $pipe = clone $this;
        $pipe->stack[] = $handler instanceof MiddlewareInterface ? $handler : new HandlerMiddleware($handler);
        return $pipe->handle($req);
    }
}