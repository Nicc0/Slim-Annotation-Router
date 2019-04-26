<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Annotations;

/**
 * Class Middleware
 *
 * @Annotation
 *
 * @since 21.04.2019
 * @author Daniel Tęcza
 *
 * @Target({"CLASS", "METHOD"})
 */
class Middleware
{
    /** @var string */
    public $value = '';
}
