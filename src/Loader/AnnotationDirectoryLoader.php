<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Loader;

/**
 * AnnotationDirectoryLoader loads routing information from annotations set
 * on PHP classes and methods.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AnnotationDirectoryLoader extends AnnotationFileLoader
{

    public function load(string $path, string $type = null): array
    {
        if (!is_dir($path)) {
            return parent::supports($path, $type) ? parent::load($path, $type) : [];
        }

        $files = iterator_to_array(new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS),
                static function (\SplFileInfo $current) {
                    return strpos($current->getBasename(), '.') !== 0;
                }
            ),
            \RecursiveIteratorIterator::LEAVES_ONLY
        ));

        usort($files, static function (\SplFileInfo $a, \SplFileInfo $b) {
            return (string) $a > (string) $b ? 1 : -1;
        });

        $collection = [];

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
                continue;
            }

            if ($class = $this->findClass($file->getPathName())) {
                $reflection = new \ReflectionClass($class);

                if ($reflection->isAbstract()) {
                    continue;
                }

                $collection += $this->loader->load($class);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, string $type = null): bool
    {
        if ('annotation' === $type) {
            return true;
        }

        if ($type || !\is_string($resource)) {
            return false;
        }

        try {
            return is_dir($resource);
        } catch (\Exception $e) {
            return false;
        }
    }
}