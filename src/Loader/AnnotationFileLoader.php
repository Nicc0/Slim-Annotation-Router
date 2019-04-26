<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Loader;

/**
 * Class AnnotationFileLoader - Based on Symfony Annotation Loader
 *
 * @since 22.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter\Loader
 */
class AnnotationFileLoader
{

    /** @var \Slim\AnnotationRouter\Loader\AnnotationClassLoader */
    protected $loader;

    /**
     * @param \Slim\AnnotationRouter\Loader\AnnotationClassLoader|null $loader
     */
    public function __construct(AnnotationClassLoader $loader)
    {
        if (!\function_exists('token_get_all')) {
            throw new \LogicException('The Tokenizer extension is required for the routing annotation loaders.');
        }

        $this->loader = $loader;
    }

    /**
     * Loads from annotations from a file.
     *
     * @param string $filePath
     * @param string|null $type The resource type
     *
     * @return array A RouteCollection instance
     *
     * @throws \ReflectionException
     */
    public function load(string $filePath, string $type = null): array
    {
        $collection = [];

        if ($class = $this->findClass($filePath)) {
            $reflection = new \ReflectionClass($class);

            if ($reflection->isAbstract()) {
                return $collection;
            }

            $collection[] = $this->loader->load($class);
        }

        gc_mem_caches();

        return $collection;
    }

    /**
     * @param string $resource
     * @param string|null $type
     *
     * @return bool
     */
    public function supports($resource, string $type = null): bool
    {
        return \is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'annotation' === $type);
    }

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass(string $file): ?string
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));

        if (1 === \count($tokens) && T_INLINE_HTML === $tokens[0][0]) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain PHP code. Did you forgot to add the "<?php" start tag at the beginning of the file?', $file));
        }

        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];

            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1]) && \in_array($tokens[$i][0], [T_NS_SEPARATOR, T_STRING], true)) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (T_CLASS === $token[0]) {
                // Skip usage of ::class constant and anonymous classes
                $skipClassToken = false;

                for ($j = $i - 1; $j > 0; --$j) {
                    if (!isset($tokens[$j][1])) {
                        break;
                    }

                    if (T_DOUBLE_COLON === $tokens[$j][0] || T_NEW === $tokens[$j][0]) {
                        $skipClassToken = true;
                        break;
                    }

                    if (!\in_array($tokens[$j][0], [T_WHITESPACE, T_DOC_COMMENT, T_COMMENT], true)) {
                        break;
                    }
                }

                if (!$skipClassToken) {
                    $class = true;
                }
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return null;
    }
}
