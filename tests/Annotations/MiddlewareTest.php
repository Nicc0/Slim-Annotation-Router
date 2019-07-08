<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests\Annotations;

use PHPUnit\Framework\TestCase;
use Slim\AnnotationRouter\Annotations\Middleware;

/**
 * Class MiddlewareTest
 *
 * @since   08.07.2019
 * @author  Daniel Tęcza
 * @package Slim\AnnotationRouter\Tests\Annotations
 */
class MiddlewareTest extends TestCase
{
    /** @var Middleware */
    private $middleware;

    public function setUp(): void
    {
        $this->middleware = new Middleware();
    }

    public function testCanGetAnnotationFromDocComment(): void
    {
        $reflection = new \ReflectionClass(Middleware::class);

        $comment = $reflection->getDocComment();

        $result = preg_match('/\*( *)@Annotation( *)\n/', $comment);

        $this->assertNotFalse($result);
    }

    public function testCanGetTargetsFromDocComment(): void
    {
        $reflection = new \ReflectionClass(Middleware::class);

        $comment = $reflection->getDocComment();

        $result = preg_match('/*(?: *)@Target\({(?<=\{)(.*?)(?=\})}\)(?: *)\n/', $comment, $matches);

        $this->assertNotFalse($result);

        $targets = \explode(',', $matches);

        $this->assertEquals(['CLASS', 'METHOD'], array_map(static function ($value) {
            return trim($value, ' "');
        }, $targets));
    }

    public function testValueAttribute(): void
    {
        $this->assertObjectHasAttribute('value', $this->middleware);
        $this->assertEmpty($this->middleware->value);

        $this->middleware->value = 'value';

        $this->assertEquals('value', $this->middleware->value);
    }
}
