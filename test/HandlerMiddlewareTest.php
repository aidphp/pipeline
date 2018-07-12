<?php

declare(strict_types=1);

namespace Test\Aidphp\Pipeline;

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Aidphp\Pipeline\HandlerMiddleware;

class HandlerMiddlewareTest extends TestCase
{
    protected $request;
    protected $response;
    protected $handler;
    protected $middleware;

    public function setUp()
    {
        $this->request  = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willReturn($this->response);

        $this->middleware = new HandlerMiddleware($this->handler);
    }

    public function testProcess()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
            ->method('handle');

        $this->assertSame($this->response, $this->middleware->process($this->request, $handler));
    }

    public function testHandle()
    {
        $this->assertSame($this->response, $this->middleware->handle($this->request));
    }
}