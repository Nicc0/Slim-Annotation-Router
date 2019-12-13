<?php

declare(strict_types=1);

namespace Slim\AnnotationRouter\Loader;

use Doctrine\Common\Annotations\Reader;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Slim\AnnotationRouter\AnnotationRouteCollector;
use Slim\AnnotationRouter\Annotations\Middleware;
use Slim\AnnotationRouter\Annotations\Route;
use Slim\AnnotationRouter\Annotations\RoutePrefix;

use function array_push;
use function class_exists;
use function count;

/**
 * Class AnnotationClassLoader
 *
 * @since 21.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter\Loader
 */
class AnnotationClassLoader
{
    /** @var Reader */
    protected $reader;

    /**
     * AnnotationClassLoader constructor.
     *
     * @param AnnotationRouteCollector $collector
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string $className
     *
     * @return array
     * @throws ReflectionException
     */
    public function load(string $className): array
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $className));
        }

        $class = new ReflectionClass($className);

        if ($class->isAbstract()) {
            throw new InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class->getName()));
        }

        $classMiddleware = $collection = [];
        $invokeAnnotation = null;
        $routePrefix = '';

        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            switch (true) {
                case $annotation instanceof Route:
                    $invokeAnnotation = $annotation; break;
                case $annotation instanceof RoutePrefix:
                    $routePrefix = $annotation->value; break;
                case $annotation instanceof Middleware;
                    $classMiddleware[] = $annotation->value; break;
            }
        }

        foreach ($class->getMethods() as $method) {
            if ($method->isPublic()) {
                $middleware = $routes = [];

                foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                    if ($annotation instanceof Route) {
                        $routes[] = $this->getRoute($className, $method->getName(), $routePrefix, $classMiddleware, $annotation);
                    }

                    if ($annotation instanceof Middleware) {
                        $middleware[] = $annotation->value;
                    }
                }

                foreach ($middleware as $middlewareName) {
                    foreach ($routes as $route) {
                        $route['middleware'][] = $middlewareName;
                    }
                }

                array_push($collection, ...$routes);
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
     * @param array  $middleware
     * @param Route  $annotation
     *
     * @return array
     */
    private function getRoute(string $class, string $method, string $routePrefix, array $middleware, Route $annotation): array
    {
        return [
            'name'       => $annotation->name,
            'methods'    => $annotation->methods,
            'pattern'    => $routePrefix . $annotation->value,
            'class'      => $class,
            'method'     => $method,
            'middleware' => $middleware,
        ];
    }
}
