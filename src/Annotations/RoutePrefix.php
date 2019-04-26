<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Annotations;

/**
 * Class RoutePrefix
 *
 * @Annotation
 *
 * @since 21.04.2019
 * @author Daniel Tęcza
 * @package
 *
 * @Target({"CLASS"})
 */
class RoutePrefix
{
    /** @var string */
    public $value = '';
}
