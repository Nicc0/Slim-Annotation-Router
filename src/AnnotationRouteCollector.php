<?php

declare(strict_types=1);

namespace Slim\AnnotationRouter;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use Slim\AnnotationRouter\Annotations\Middleware;
use Slim\AnnotationRouter\Annotations\Route;
use Slim\AnnotationRouter\Annotations\RoutePrefix;
use Slim\AnnotationRouter\Loader\AnnotationClassLoader;
use Slim\AnnotationRouter\Loader\AnnotationDirectoryLoader;
use Slim\Routing\RouteCollector;
use Throwable;

use function dirname;
use function file_exists;
use function file_put_contents;
use function in_array;
use function is_dir;
use function is_readable;
use function is_writable;
use function mkdir;
use function scandir;
use function trigger_error;
use function var_export;

/**
 * Class AnnotationRouteCollector
 *
 * @since 21.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter
 */
class AnnotationRouteCollector extends RouteCollector
{
    /** @var string[] */
    protected $annotationImports = [
        'ignoreAnnotation' => IgnoreAnnotation::class,
        'route' => Route::class,
        'routeprefix' => RoutePrefix::class,
        'middleware' => Middleware::class,
    ];

    /** @var string[] */
    protected $defaultControllersPath = [];

    /** @var string|null */
    protected $defaultRoutesTemporaryFilePath;

    /**
     * @param string[] $path
     *
     * @return self
     */
    public function setDefaultControllersPath(string ...$path): AnnotationRouteCollector
    {
        $this->defaultControllersPath = $path;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getDefaultControllersPath(): array
    {
        return $this->defaultControllersPath;
    }

    /**
     * @param string $filePath
     *
     * @return self
     */
    public function setDefaultTemporaryFilePath(string $filePath): AnnotationRouteCollector
    {
        $this->defaultRoutesTemporaryFilePath = $filePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultTemporaryFilePath(): string
    {
        return $this->defaultRoutesTemporaryFilePath ?? sys_get_temp_dir();
    }

    /**
     * @param bool $forceFromAnnotation
     *
     * @return bool
     */
    public function collectRoutes(bool $forceFromAnnotation = false): bool
    {
        $routes = [];

        if ( $forceFromAnnotation === false && $this->isTemporaryFile() === true ) {
            $routes = $this->collectRoutesFromTemporaryFile();
        }

        if ( $forceFromAnnotation === true || $routes === [] ) {
            try {
                $routes = $this->collectRoutesFromAnnotations();
            } catch ( Throwable $exception ) {
                trigger_error( 'cannot collect routes from annotation, ' . $exception->getMessage(), E_USER_WARNING );
            }
        }

        foreach ( $routes as $annotationRoute ) {
            $this->createAnnotationRoute( $annotationRoute );
        }

        return $this->routeCounter > 0;
    }

    /**
     * @param array $annotationRoute
     */
    private function createAnnotationRoute(array $annotationRoute): void
    {
        $action = $annotationRoute[ 'action' ];
        $class = $annotationRoute[ 'class' ];
            
        if ( $this->container instanceof ContainerInterface && $this->container->has( $class ) ) {
            $callable = [ $this->container->get( $class ), $action ];
        } else {
            $callable = $class . ':' . $action;
        }

        $route = $this->createRoute(
            $annotationRoute[ 'methods' ], $annotationRoute[ 'pattern' ], $callable
        );

        if ( !empty($annotationRoute[ 'name' ]) && is_string( $annotationRoute[ 'name' ] ) ) {
            $route->setName( $annotationRoute[ 'name' ] );
        }

        if ( $annotationRoute[ 'middleware' ] !== [] && $this->container instanceof ContainerInterface ) {
            foreach ( $annotationRoute[ 'middleware' ] as $middlewareName ) {
                $route->addMiddleware( $this->container->get( $middlewareName ) );
            }
        }

        $this->routes[ $route->getIdentifier() ] = $route;
        $this->routeCounter++;
    }

    /**
     * @return array
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function collectRoutesFromAnnotations(): array
    {
        $directoriesPath = $this->getDefaultControllersPath();

        if ( $directoriesPath === [] ) {
            throw new RuntimeException( 'Directory path for controllers must be defined!', 500 );
        }

        $annotationDirectoryLoader = new AnnotationDirectoryLoader(
            new AnnotationClassLoader( $this->getAnnotationReader() )
        );

        $routes = [];

        foreach ( $directoriesPath as $directoryPath ) {
            $routes = array_merge( $routes, $annotationDirectoryLoader->load( $directoryPath ) );
        }

        if ( ! is_dir($dirname = dirname($this->getTemporaryFileName())) && !mkdir( $dirname, 0777, true ) && !is_dir( $dirname ) ) {
            throw new RuntimeException( sprintf( 'Directory "%s" was not created', $dirname ) );
        }

        if ( is_writable( dirname( $tempName = $this->getTemporaryFileName() ) ) ) {
            file_put_contents( $tempName, sprintf( '<?php return %s;', var_export( $routes, true ) ) );
        }

        return $routes;
    }

    /**
     * @return AnnotationReader
     *
     * @throws AnnotationException
     */
    private function getAnnotationReader(): AnnotationReader
    {
        $docParser = new DocParser();
        $docParser->setIgnoreNotImportedAnnotations( true );

        $annotationReader = new AnnotationReader($docParser);

        $this->registerAnnotations();

        $reflection = new ReflectionProperty(AnnotationReader::class, 'globalImports');
        $reflection->setAccessible(true);
        $reflection->setValue(null, $this->annotationImports);

        return $annotationReader;
    }

    /**
     * @deprecated
     */
    private function registerAnnotations(): void
    {
        $annotationsPath = __DIR__ . DIRECTORY_SEPARATOR . 'Annotations';

        foreach (scandir($annotationsPath) as $annotation) {
            if (!in_array($annotation, ['.', '..'], true)) {
                $annotationPath = $annotationsPath . DIRECTORY_SEPARATOR . $annotation;
                AnnotationRegistry::registerFile($annotationPath);
            }
        }
    }

    /**
     * @noinspection PhpIncludeInspection
     *
     * @return array
     */
    private function collectRoutesFromTemporaryFile(): array
    {
        return require $this->getTemporaryFileName();
    }

    /**
     * @return bool
     */
    private function isTemporaryFile(): bool
    {
        return file_exists($tempName = $this->getTemporaryFileName()) && is_writable($tempName) && is_readable($tempName);
    }

    /**
     * @return string
     */
    private function getTemporaryFileName(): string
    {
        return $this->getDefaultTemporaryFilePath() . DIRECTORY_SEPARATOR . 'annotation-router' . DIRECTORY_SEPARATOR . 'routes.php';
    }
}
