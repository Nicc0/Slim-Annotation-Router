<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests;

use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\TestCase;
use Slim\AnnotationRouter\AnnotationRouteCollector;
use Slim\AnnotationRouter\Loader\AnnotationClassLoader;
use Slim\AnnotationRouter\Tests\Controller\AbstractController;
use Slim\AnnotationRouter\Tests\Controller\ExampleController;

/**
 * Class AnnotationClassLoaderTest
 *
 * @since 27.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter\Tests
 */
class AnnotationClassLoaderTest extends TestCase
{
    /** @var AnnotationClassLoader */
    private $classLoader;

    public function setUp()
    {
        /** @var Reader $reader */
        $reader = $this->createMock(Reader::class);

        /** @var AnnotationRouteCollector $collector */
        $collector = $this->createMock(AnnotationRouteCollector::class);

        $this->classLoader = new AnnotationClassLoader($reader, $collector);
    }

    public function testCanLoadAnnotationRoutesFromNotExistingClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->classLoader->load(\ExampleController::class);
    }

    public function testCanLoadAnnotationRoutesFromAbstractClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->classLoader->load(AbstractController::class);
    }

    public function testCanLoadAnnotationRoutesFromClass(): void
    {
        $routes = $this->classLoader->load(ExampleController::class);
    }
}
