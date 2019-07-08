<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Loader;

use Doctrine\Common\Annotations\Reader;
use Psr\Container\ContainerInterface;
use Slim\AnnotationRouter\AnnotationRouteCollector;
use Slim\AnnotationRouter\Annotations\Middleware;
use Slim\AnnotationRouter\Annotations\Route;
use Slim\AnnotationRouter\Annotations\RoutePrefix;
use Slim\Interfaces\RouteInterface;

/**
 * Class AnnotationClassLoader
 *
 * @since 21.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter\Loader
 */
class AnnotationClassLoader
{
    /** @var \Slim\AnnotationRouter\AnnotationRouteCollector */
    protected $collector;

    /** @var \Doctrine\Common\Annotations\Reader */
    protected $reader;

    /**
     * AnnotationClassLoader constructor.
     *
     * @param \Doctrine\Common\Annotations\Reader $reader
     * @param \Slim\AnnotationRouter\AnnotationRouteCollector $collector
     */
    public function __construct(Reader $reader, AnnotationRouteCollector $collector)
    {
        $this->reader = $reader;
        $this->collector = $collector;
    }

    /**
     * @param string $className
     *
     * @return array
     * @throws \ReflectionException
     */
    public function load(string $className): array
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $className));
        }

        $class = new \ReflectionClass($className);

        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class->getName()));
        }

        $invokeAnnotation = null;
        $middlewares = null;
        $routePrefix = '';
        $collection = [];

        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Route) {
                $invokeAnnotation = $annotation;
            }

            if ($annotation instanceof RoutePrefix) {
                $routePrefix = $annotation->value;
            }

            if ($annotation instanceof Middleware) {
                $middlewares[] = $annotation->value;
            }
        }

        $container = $this->collector->getContainer();


        foreach ($class->getMethods() as $method) {
            if ($method->isPublic()) {
                $methodRoutes = [];
                $methodMiddleware = [];

                foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                    if ($annotation instanceof Route) {
                        $route = $this->getRoute($className, $method->getName(), $routePrefix, $annotation);

                        if ($middlewares !== [] && $container instanceof ContainerInterface) {
                            foreach ($middlewares as $middlewareName) {
                                $route->addMiddleware($container->get($middlewareName));
                            }
                        }

                        $methodRoutes[] = $route;
                    }

                    if ($annotation instanceof Middleware && $container instanceof ContainerInterface) {
                        $methodMiddleware[] = $annotation->value;
                    }
                }

                foreach ($methodRoutes as $route) {
                    if ($methodMiddleware !== []) {
                        foreach ($methodMiddleware as $middlewareName) {
                            $route->addMiddleware($container->get($middlewareName));
                        }
                    }

                    $collection[] = $route;
                }
            }
        }

        if ($invokeAnnotation !== null && count($collection) === 0 && $class->hasMethod('__invoke')) {
            $collection[] = $this->getRoute($className, '__invoke', $routePrefix, $invokeAnnotation);
        }


        return $collection;
    }

    /**
     * @param string $class
     * @param string $method
     * @param string $routePrefix
     * @param \Slim\AnnotationRouter\Annotations\Route $annotation
     *
     * @return RouteInterface
     */
    private function getRoute(string $class, string $method, string $routePrefix, Route $annotation): RouteInterface
    {
        $methods = $annotation->methods;
        $pattern = $routePrefix . $annotation->value;

        $route = $this->collector->createAnnotationRoute($methods, $pattern, $class, $method);

        if (!empty($annotation->name) && is_string($annotation->name)) {
            $route->setName($annotation->name);
        }

        return $route;
    }
}
