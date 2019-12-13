<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests;

use PHPUnit\Framework\TestCase;
use Slim\AnnotationRouter\AnnotationRouteCollector;
use Slim\CallableResolver;
use Slim\Http\Factory\DecoratedResponseFactory;
use Slim\Interfaces\RouteInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
use const DIRECTORY_SEPARATOR;

/**
 * Class SlimIntegrationTest
 *
 * @since 27.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter\Tests
 */
class SlimIntegrationTest extends TestCase
{
    /** @var DecoratedResponseFactory */
    private $factory;

    /** @var CallableResolver */
    private $resolver;

    public function setUp(): void
    {
        $this->factory = new DecoratedResponseFactory( new ResponseFactory(), new StreamFactory() );
        $this->resolver = new CallableResolver( );
    }

    public function testIntegrationAnnotationRouterWithSlim()
    {
        $collector = new AnnotationRouteCollector( $this->factory, $this->resolver );
        $collector->setDefaultControllersPath( __DIR__ . DIRECTORY_SEPARATOR . 'Controller');
        $collector->setDefaultTemporaryFilePath(__DIR__ . DIRECTORY_SEPARATOR . 'Cache');
        $collector->collectRoutes();

        $routes = $collector->getRoutes();

        $this->assertCount(3, $routes);

        $this->assertArrayHasKey('route0', $routes);
        $this->assertArrayHasKey('route1', $routes);
        $this->assertArrayHasKey('route2', $routes);

        /** @var RouteInterface $route */
        $route = $routes['route0'];

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertEquals([ 'GET' ], $route->getMethods());
        $this->assertEquals('example.test', $route->getName());
        $this->assertEquals('/example/test', $route->getPattern());
    }
}
