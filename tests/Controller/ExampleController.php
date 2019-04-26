<?php declare(strict_types=1);

namespace Slim\AnnotationRouter\Tests\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ExampleController
 *
 * @since 26.04.2019
 * @author Daniel Tęcza
 * @package Slim\AnnotationRouter\Tests\Controller
 *
 * @RoutePrefix("/example")
 */
class ExampleController
{

    /**
     * @Route("/test", methods={"GET"}, name="example.test")
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function testAction(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()->write('Test case');

        return $response;
    }

    /**
     * @Route("/test/{name}", methods={"GET"}, name="example.test")
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function testHelloAction(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $output = \sprintf('Hello %s!', $args['name']);

        $response->getBody()->write($output);

        return $response;
    }
}
