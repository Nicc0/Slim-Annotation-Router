<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests\Annotations;

use PHPUnit\Framework\TestCase;
use Slim\AnnotationRouter\Annotations\RoutePrefix;

/**
 * Class RoutePrefixTest
 *
 * @since   08.07.2019
 * @author  Daniel Tęcza
 * @package Slim\AnnotationRouter\Tests\Annotations
 */
class RoutePrefixTest extends TestCase
{
    /** @var RoutePrefix */
    private $routePrefix;

    public function setUp(): void
    {
        $this->routePrefix = new RoutePrefix();
    }

    public function testCanGetAnnotationFromDocComment(): void
    {
        $reflection = new \ReflectionClass(RoutePrefix::class);

        $comment = $reflection->getDocComment();

        $result = preg_match('/\*( *)@Annotation( *)\n/', $comment);

        $this->assertNotFalse($result);
    }

    public function testCanGetTargetsFromDocComment(): void
    {
        $reflection = new \ReflectionClass(RoutePrefix::class);

        $comment = $reflection->getDocComment();

        $result = preg_match('/*(?: *)@Target\({(?<=\{)(.*?)(?=\})}\)(?: *)\n/', $comment, $matches);

        $this->assertNotFalse($result);

        $targets = \explode(',', $matches);

        $this->assertEquals(['CLASS'], array_map(static function ($value) {
            return trim($value, ' "');
        }, $targets));
    }

    public function testValueAttribute(): void
    {
        $this->assertObjectHasAttribute('value', $this->routePrefix);
        $this->assertEmpty($this->routePrefix->value);

        $this->routePrefix->value = 'value';

        $this->assertEquals('value', $this->routePrefix->value);
    }
}
