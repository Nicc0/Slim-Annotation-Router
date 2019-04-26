<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Strategies;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Class RequestArgs
 *
 * @since 21.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter\Strategies
 */
class RequestArgs implements InvocationStrategyInterface
{
    /**
     * @param callable $callable
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $routeArguments
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(callable $callable, ServerRequestInterface $request, ResponseInterface $response, array $routeArguments): ResponseInterface
    {
        return $callable(...array_values($routeArguments));
    }
}
