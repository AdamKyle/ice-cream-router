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

    private $namespace = null;

    public function __construct(string $namespace) {
        $this->collection = new RouteCollection();
        $this->context    = new RequestContext();
        $this->namespace  = $namespace;


        $this->defaultRoutes = [
            '404' => [
                'route' => '/404',
                'action' => function($request) {
                    return new Response('oops! could not find what you were looking for.');
                },
                'method' => 'GET'
            ],
            '500' => [
                'route' => '/500',
                'action' => function($request, $response) {
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
     * Simple get route. Returns the result of the action.
     *
     * @param string $routeName  - eg: '/foo'
     * @param string $name       - eg: 'foo'
     * @param mixed  $action     - action thats executed when route is found.
     */
    public function get(string $routeName, string $name, $action) {
        $this->createRoute($routeName, $name, 'GET', $action);
    }

    /**
     * POST Route.
     *
     * Simple post route. Returns the result of the action.
     *
     * @param string $routeName  - eg: '/foo'
     * @param string $name       - eg: 'foo'
     * @param mixed $action      - action thats executed when route is found.
     */
    public function post(string $routeName, string $name, $action) {
        $this->createRoute($routeName, $name, 'POST', $action);
    }

    /**
     * PUT Route.
     *
     * Simple put route. Returns the result of the action.
     *
     * @param string $routeName  - eg: '/foo'
     * @param string $name       - eg: 'foo'
     * @param mixed $action      - action thats executed when route is found.
     */
    public function put(string $routeName, string $name, $action) {
        $this->createRoute($routeName, $name, 'PUT', $action);
    }

    /**
     * DELETE Route.
     *
     * Simple delete route. Returns the result of the action.
     *
     * @param string $routeName  - eg: '/foo'
     * @param string $name       - eg: 'foo'
     * @param mixed $action      - action thats executed when route is found.
     */
    public function delete(string $routeName, string $name, $action) {
        $this->createRoute($routeName, $name, 'DELETE', $action);
    }

    /**
     * Create the route.
     *
     * If the action is not an instance of Closure then attempt to set up the class and method,
     * by exploding on the delimeter and appending the namespace.
     *
     * The action must conform to 'action:method' if the action is not a closure.
     *
     * @param string $route  - eg: '/foo'
     * @param string $name   - eg: 'foo'
     * @param string $method - eg: GET
     * @param mixed $action  - action thats executed when route is found.
     * @throws \Exception
     */
    public function createRoute(string $route, string $name, string $method, $action) {
        $route = new Route($route, strtoupper($method), $action);

        // Convert the action to class:method
        if (!$action instanceof \Closure) {
            if (is_string($action) && !strpos($action, ':')) {
                throw new \Exception($action . ' is an unacceptable action. Action must be a string seperating class and method with :');
            }

            $routeAction = [];

            $routeAction['class']  = $this->namespace . '\\' . explode(':', $action)[0];
            $routeAction['method'] = explode(':', $action)[1];

            // Overide the string action with the defined action.
            $route->setAction($routeAction);
        }


        $this->collection->add($name, $route->getroute());
    }

    /**
     * Processes the routes as the request comes in.
     *
     * Taken almost directly from the symfony docs, we have a simple method
     * that attempts to find the route in the collection of routes, and call the
     * action, passing in the request object and the response object.
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
     * - name: 404, routeName: /404, method: 'GET', action
     * - name: 500, routeName: /500, method: 'GET', action
     *
     * @param string $name       - name of route.
     * @param string $routeName  - eg: /404
     * @param string $method     = eg: 'GET'
     * @param mixed $action      - action thats executed when route is found.
     */
    public function overrideDefaulRoute(string $name, $action) {
        if (!isset($this->defaultRoutes[$name])) {
            throw new \InvalidArgumentException($name . ' does not exist in the default routes container.');
        }

        $this->defaultRoutes[$name]['action'] = $action;

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

            $route              = new Route($route['route'], $method, $route['action']);
            $routeForCollection = $route->getRoute();

            $this->collection->add($name, $routeForCollection);
        }
    }
}
