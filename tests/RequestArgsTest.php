<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests;

use PHPUnit\Framework\TestCase;
use Slim\AnnotationRouter\Strategies\InvocationWithArgs;
use Slim\Http\ServerRequest;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Psr7\Response;

/**
 * Class RequestArgsTest
 *
 * @since 26.04.2019
 * @author Daniel Tęcza
 */
class RequestArgsTest extends TestCase
{
    public function testCanRequestArgsIsInstanceOfInvocationStrategyInterface(): void
    {
        $this->assertInstanceOf(InvocationStrategyInterface::class, new InvocationWithArgs());
    }

    public function testCanRequestArgsCanBeInvoked(): void
    {
        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $this->createMock(ServerRequest::class);

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $this->createMock(Response::class);

        $arguments = [ 'hello' => 'world' ];
        $callable = function ($hello) {
            $this->assertIsString($hello);
            $this->assertEquals('world', $hello);

            return new Response();
        };

        $strategy = new InvocationWithArgs();

        $result = $strategy($callable, $request, $response, $arguments);

        $this->assertInstanceOf(InvocationStrategyInterface::class, $strategy);
        $this->assertIsCallable($strategy);
        $this->assertNotNull($result);
    }
}
