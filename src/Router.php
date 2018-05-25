<?php

namespace IceCreamRouter;

use \IceCreamRouter\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Router {

    private $collection;

    private $context;

    private $defaultRoutes = [];

    public function __construct() {
        $this->collection = new RouteCollection();
        $this->context    = new RequestContext();


        $this->defaultRoutes = [
            '404' => [
                'route' => '/404',
                'callable' => function($request) {
                    return new Response('oops! could not find what you were looking for.');
                },
                'method' => 'GET'
            ],
            '500' => [
                'route' => '/500',
                'callable' => function($request, $response) {
                    return new Response ($request->get('error_details'));
                },
                'method' => 'GET'
            ],
        ];

        $this->createDefaultRoutes();
    }

    /**
     * GET Route.
     *
     * Simple get route. Returns the result of the callable.
     *
     * @param string $routeName  - eg: '/foo'
     * @param string $name       - eg: 'foo',
     * @param callable $callable - closure function thats executed when route is found.
     */
    public function get(string $routeName, string $name, callable $callable) {
        $route = new Route($routeName, 'GET', $callable);

        $this->collection->add($name, $route->getRoute());
    }

    /**
     * POST Route.
     *
     * Simple post route. Returns the result of the callable.
     *
     * @param string $routeName  - eg: '/foo'
     * @param string $name       - eg: 'foo',
     * @param callable $callable - closure function thats executed when route is found.
     */
    public function post(string $routeName, string $name, callable $callable) {
        $route = new Route($routeName, 'POST', $callable);

        $this->collection->add($name, $route->getRoute());
    }

    /**
     * PUT Route.
     *
     * Simple put route. Returns the result of the callable.
     *
     * @param string $routeName  - eg: '/foo'
     * @param string $name       - eg: 'foo',
     * @param callable $callable - closure function thats executed when route is found.
     */
    public function put(string $routeName, string $name, callable $callable) {
        $route = new Route($routeName, 'PUT', $callable);

        $this->collection->add($name, $route->getRoute());
    }

    /**
     * DELETE Route.
     *
     * Simple delete route. Returns the result of the callable.
     *
     * @param string $routeName  - eg: '/foo'
     * @param string $name       - eg: 'foo',
     * @param callable $callable - closure function thats executed when route is found.
     */
    public function delete(string $routeName, string $name, callable $callable) {
        $route = new Route($routeName, 'DELETE', $callable);

        $this->collection->add($name, $route->getRoute());
    }

    /**
     * Processes the routes as the request comes in.
     *
     * Taken almost directly from the symfony docs, we have a simple method
     * that attempts to find the route in the collection of routes, and call the
     * closure function, passing in the request object and the response object.
     *
     * The response object is created via creating an empty response object and preparing the
     * the request object.
     *
     * Should we fail we default to redirecting to a default route thats registered
     * upon instantiation of this routing class.
     */
    public function processRoutes(Request $request) {
        $matcher = new UrlMatcher($this->collection, $this->getContext($request));

        try {
            return (new RouteHandler($request, $matcher))->handle();
        } catch (ResourceNotFoundException $e) {
            return new RedirectResponse('/404', 302);
        } catch (\Exception $e) {
            $generator = new UrlGenerator($this->collection, $this->getContext($request));
            $url = $generator->generate('500', ['error_details' => $e]);
            return new RedirectResponse($url, 302);
        }
    }

    /**
     * Allows you to register or overide default routes.
     *
     *
     * Current default routes are:
     *
     * - name: 404, routeName: /404, method: 'GET', callable
     * - name: 500, routeName: /500, method: 'GET', callable
     *
     * @param string $name       - name of route.
     * @param string $routeName  - eg: /404
     * @param string $method     = eg: 'GET'
     * @param callable $callable - closure function.
     */
    public function overrideDefaulRoute(string $name, callable $callable) {
        if (!isset($this->defaultRoutes[$name])) {
            throw new \InvalidArgumentException($name . ' does not exist in the default routes container.');
        }

        $this->defaultRoutes[$name]['callable'] = $callable;

        $this->createDefaultRoutes();
    }

    /**
     * Gets the context for the request.
     *
     * @param  Symfony\Component\HttpFoundation\Request $request - request object.
     * @return Symfony\Component\Routing\RequestContext          - context for the request.
     */
    public function getContext(Request $request) {
        return $this->context->fromRequest($request);
    }

    /**
     * Returns route collection.
     *
     * @return Symfony\Component\Routing\RouteCollection
     */
    public function getCollection() {
        return $this->collection;
    }

    private function createDefaultRoutes() {
        foreach($this->defaultRoutes as $name => $route) {
            $method = $route['method'];

            $route              = new Route($route['route'], $method, $route['callable']);
            $routeForCollection = $route->getRoute();

            $this->collection->add($name, $routeForCollection);
        }
    }
}
