<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Annotations;

/**
 * Class Route
 *
 * @Annotation
 *
 * @since 21.04.2019
 * @author Daniel Tęcza
 *
 * @Target({"CLASS", "METHOD"})
 */
class Route
{
    /** @var string */
    public $value = '';

    /** @var string[] */
    public $methods = [];

    /** @var string */
    public $name = '';

    /** @var string */
    public $description = '';

	/** @var string[] */
	public $arguments = [];

}
