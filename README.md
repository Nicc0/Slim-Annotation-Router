# Slim-Annotation-Router

<p align="center">
 <a href="https://packagist.org/packages/nicc0/slim-annotation-router">
  <img alt="Latest Version on Packagist" src="https://img.shields.io/packagist/v/Nicc0/slim-annotation-router.svg?style=flat-square">
 </a>
 <a href="#">
  <img alt="Latest PHP Version" src="https://img.shields.io/packagist/php-v/nicc0/slim-annotation-router.svg?style=flat-square">
 </a>
 <a href="https://github.com/Nicc0/Slim-Annotation-Router/blob/master/LICENSE">
  <img alt="License" src="https://img.shields.io/github/license/Nicc0/Slim-Annotation-Router.svg?style=flat-square">
 </a>
 <a href="https://travis-ci.org/Nicc0/slim-annotation-router">
  <img alt="Build Status" src="https://img.shields.io/travis/Nicc0/Slim-Annotation-Router.svg?style=flat-square">
 </a>
 <a href="https://codecov.io/gh/Nicc0/slim-annotation-router">
  <img alt="Coverages" src="https://img.shields.io/codecov/c/github/nicc0/slim-annotation-router.svg?style=flat-square">
 </a>
</p>

Annotation Router for [Slim 4.x](https://github.com/slimphp/Slim/tree/4.x)

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Slimw Annotation Router.

```bash
$ composer require "nicc0/slim-annotation-router"
```

This will install Slim Annotation Router and all required dependencies. Remember Slim 4.x requires PHP 7.1 or newer.

## Usage

```php
$factory = new DecoratedResponseFactory( new ResponseFactory(), new StreamFactory() );
$resolver = new CallableResolver();

$controllerPath = './app/controllers/';

$collector = new AnnotationRouteCollector( $factory, $resolver );
$collector->setDefaultControllersPath( $controllersPath );
$collector->collectRoutes();

$app = new App( $factory, $container, $resolver, $collector );
```

## Creating Routes by Annotation

```php
/**
 * Class ExampleController
 *
 * @RoutePrefix("/example")
 */
class ExampleController
{
    /**
     * @Route("/hello", methods={"GET"}, name="example.hello")
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function hello(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('Hello world!');

        return $response;
    }
}
```

By opening the url http://your_site_url/example/hello, you should see "Hello world!".

## Tests

To execute the test suite, you'll need to install all development dependencies.

```bash
git clone https://github.com/Nicc0/Slim-Annotation-Router
composer install
composer test
```

## License

The Slim Annotation Router is licensed under the MIT license. See [License File](LICENSE.md) for more information.
