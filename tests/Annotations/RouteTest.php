<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests\Annotations;

use PHPUnit\Framework\TestCase;
use Slim\AnnotationRouter\Annotations\Route;

/**
 * Class RouteTest
 *
 * @since   08.07.2019
 * @author  Daniel Tęcza
 * @package Slim\AnnotationRouter\Tests\Annotations
 */
class RouteTest extends TestCase
{
    /** @var Route */
    private $route;

    public function setUp(): void
    {
        $this->route = new Route();
    }

    public function testCanGetAnnotationFromDocComment(): void
    {
        $reflection = new \ReflectionClass(Route::class);

        $comment = $reflection->getDocComment();

        $result = preg_match('/\*( *)@Annotation( *)\n/', $comment);

        $this->assertNotFalse($result);
    }

    public function testCanGetTargetsFromDocComment(): void
    {
        $reflection = new \ReflectionClass(Route::class);

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
        $this->assertObjectHasAttribute('value', $this->route);
        $this->assertEmpty($this->route->value);

        $this->route->value = 'value';

        $this->assertEquals('value', $this->route->value);
    }

    public function testDescriptionAttribute(): void
    {
        $this->assertObjectHasAttribute('description', $this->route);
        $this->assertEmpty($this->route->description);

        $this->route->description = 'description';

        $this->assertEquals('description', $this->route->description);

    }

    public function testNameAttribute(): void
    {
        $this->assertObjectHasAttribute('name', $this->route);
        $this->assertEmpty($this->route->name);

        $this->route->name = 'name';
    }

    public function testMethodsAttribute(): void
    {
        $this->assertObjectHasAttribute('methods', $this->route);
        $this->assertEmpty($this->route->methods);

        $this->route->methods = [ 'GET', 'POST' ];

        $this->assertEquals([ 'GET', 'POST' ], $this->route->methods);
    }
}
