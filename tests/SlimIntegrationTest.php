<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests;

use PHPUnit\Framework\TestCase;
use Slim\AnnotationRouter\AnnotationRouteCollector;
use Slim\CallableResolver;
use Slim\Http\Factory\DecoratedResponseFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

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

    public function setUp()
    {
        $this->factory = new DecoratedResponseFactory( new ResponseFactory(), new StreamFactory() );
        $this->resolver = new CallableResolver( );
    }

    public function testIntegrationAnnotationRouterWithSlim()
    {
        $collector = new AnnotationRouteCollector( $this->factory, $this->resolver );
        $collector->setDefaultControllersPath( __DIR__ . DIRECTORY_SEPARATOR . 'Controller');
        $collector->collectRoutes();
    }
}
