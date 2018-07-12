<?php

declare(strict_types=1);

namespace Test\Aidphp\Pipeline;

use PHPUnit\Framework\TestCase;
use Aidphp\Pipeline\PipeMiddleware;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class PipeMiddlewareTest extends TestCase
{
    private function getMiddleware(ServerRequestInterface $req): MiddlewareInterface
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->with($req, $this->isInstanceOf(RequestHandlerInterface::class))
            ->willReturnCallback(function ($req, $handler) {
                return $handler->handle($req);
            });

        return $middleware;
    }

    private function getMiddlewareWithResponse(ServerRequestInterface $req, ResponseInterface $res): MiddlewareInterface
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->with($req, $this->isInstanceOf(RequestHandlerInterface::class))
            ->willReturnCallback(function ($req, $handler) use ($res) {
                return $res;
            });

        return $middleware;
    }

    private function getMiddlewareNeverCalled() : MiddlewareInterface
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->never())
            ->method('process');

        return $middleware;
    }

    public function testRegister()
    {
        $pipe = new PipeMiddleware();
        $this->assertSame($pipe, $pipe->register($this->createMock(MiddlewareInterface::class)));
    }

    public function testHandle()
    {
        $req = $this->createMock(ServerRequestInterface::class);
        $res = $this->createMock(ResponseInterface::class);

        $pipe = new PipeMiddleware([
            $this->getMiddleware($req),
            $this->getMiddlewareWithResponse($req, $res),
            $this->getMiddlewareNeverCalled()
        ]);

        $this->assertSame($res, $pipe->handle($req));
    }

    public function testProcess()
    {
        $req = $this->createMock(ServerRequestInterface::class);
        $res = $this->createMock(ResponseInterface::class);

        $last = $this->createMock(RequestHandlerInterface::class);
        $last->expects($this->never())
            ->method('handle');

        $pipe = new PipeMiddleware([
            $this->getMiddleware($req),
            $this->getMiddlewareWithResponse($req, $res),
            $this->getMiddlewareNeverCalled()
        ]);

        $this->assertSame($res, $pipe->process($req, $last));
    }

    public function testProcessWithLastHandler()
    {
        $req = $this->createMock(ServerRequestInterface::class);
        $res = $this->createMock(ResponseInterface::class);

        $last = $this->createMock(RequestHandlerInterface::class);
        $last->expects($this->once())
            ->method('handle')
            ->with($req)
            ->willReturn($res);

        $pipe = new PipeMiddleware([
            $this->getMiddleware($req),
            $this->getMiddleware($req),
        ]);

        $this->assertSame($res, $pipe->process($req, $last));
    }

    public function testEmptyPipe()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The pipe is empty');

        $pipe = new PipeMiddleware();
        $pipe->handle($this->createMock(ServerRequestInterface::class));
    }
}