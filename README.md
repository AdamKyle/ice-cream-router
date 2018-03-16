# Ice Cream Router

[![Build Status](https://travis-ci.org/AdamKyle/ice-cream-router.svg?branch=master)](https://travis-ci.org/AdamKyle/ice-cream-router)
[![Packagist](https://img.shields.io/packagist/v/ice-cream/router.svg?style=flat)](https://packagist.org/packages/ice-cream/router)
[![Maintenance](https://img.shields.io/maintenance/yes/2018.svg)]()
[![Made With Love](https://img.shields.io/badge/Made%20With-Love-green.svg)]()

- Requires PHP 7.2
- Is Standalone

This is a very thin and very basic wrapper around the concept of [symfony routing](http://symfony.com/doc/current/routing.html).

It's main goal is to be as simple and easy to use as possible with no bloat or
fluff.

## Documentation

Why is there no generated documentation, like the other Ice Cream packages?

Because PHP Documentor has a symfony config that conflicts with the symfony router.

## How Do I Use It?

The simplest way to use this is:

```php
use IceCreamRouter\Router;

$router = new Router();

$router->get('/foo/{id}', 'foo', function($id, $request, $response){
  return response->setContent('Id is: ' . $id);
});

$response = $router->processRoutes(Request::create('/foo/1', 'GET'));

var_dump($response->getContent()); // => Id is: 1
```

Could not get any simpler then that.

The same thing is done for `POST`, `PUT` and `DELETE` The only difference between whats above and what you would do
for these methods is omitting `$response` as a variable for the closure.

```php
use IceCreamRouter\Router;

$router = new Router();

$router->post('/foo/{id}', 'foo', function($id, $request){
  return new Response('the message passed in was: ' . $request->get('message') . ' and the id is: ' . $id);
});

$response = $router->processRoutes(Request::create('/foo/1', 'POST', ['message' => 'hello world']));

var_dump($response->getContent()); // => the message passed in was: hello world and the id is: 1
```

We are even smart enough, with the help of symfony, to recognize the same route with different methods:


```php
use IceCreamRouter\Router;

$router = new Router();

$router->post('/foo/{id}', 'foo', function($id, $request){
  return new Response('the message passed in was: ' . $request->get('message') . ' and the id is: ' . $id);
});

$router->delete('/foo/{id}', 'foo_del', function($id, $request){
  return new Response('deleted');
});

$router->put('/foo/{id}', 'foo_put', function($id, $request){
  return new Response('the message put was: ' . $request->get('message') . ' and the id is: ' . $id);
});

$response = $router->processRoutes(Request::create('/foo/1', 'POST', ['message' => 'hello world']));

var_dump($response->getContent()); // => the message passed in was: hello world and the id is: 1
```

You can see here that we have the same end point, with different methods, as a result we can create a request
that only does `POST` and it will match the correct endpoint.
