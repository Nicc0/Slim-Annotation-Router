<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Slim\AnnotationRouter\Loader\AnnotationClassLoader;
use Slim\AnnotationRouter\Loader\AnnotationFileLoader;

/**
 * Class AnnotationFileLoaderTest
 *
 * @since 27.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter\Tests\Controller
 */
class AnnotationFileLoaderTest extends TestCase
{
    /** @var AnnotationFileLoader */
    protected $fileLoader;

    /**
     * @return array
     */
    public function correctResourceDataProvider(): array
    {
        return [
            [ 'TestFile.php', null ],
            [ './directory/FileTest.php' , null ],
            [ 'TestFile.php', 'annotation' ],
        ];
    }

    /**
     * @return array
     */
    public function incorrectResourceDataProvider(): array
    {
        return [
            [ 'TestFile.sh', null ],
            [ 'TestFile.php', '???' ],
            [ 123.45, null ],
        ];
    }

    public function setUp(): void
    {
        /** @var AnnotationClassLoader $fileLoader */
        $fileLoader = $this->createMock(AnnotationClassLoader::class);

        $this->fileLoader = new AnnotationFileLoader($fileLoader);
    }

    /**
     * @dataProvider correctResourceDataProvider
     *
     * @param $resource
     * @param $type
     */
    public function testCanSupportMethodWorkProperlyForCorrectData($resource, $type): void
    {
        $this->assertTrue($this->fileLoader->supports($resource, $type ));
    }

    /**
     * @dataProvider incorrectResourceDataProvider
     *
     * @param $resource
     * @param $type
     */
    public function testCanSupportMethodWorkProperlyForIncorrectData($resource, $type): void
    {
        $this->assertFalse($this->fileLoader->supports($resource, $type));
    }
}
