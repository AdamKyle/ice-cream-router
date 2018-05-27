<?php

use IceCreamRouter\RouteHandler;
use IceCreamRouter\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use PHPUnit\Framework\TestCase;

class RouteHandlerTest extends TestCase {
    private $router;

    public function setup() {
        $this->router = new Router('App\\Sample\\Namespace');

        $this->router->get('/route', 'route', function($request, $response){
            return new Response('route');
        });

        $this->router->get('/route/{id}', 'route_id', function($request, $response){
            return new Response('route');
        });

        $this->router->get('/route/{id}/sample', 'route_id', 'Action:sampleAction');

        $this->router->get('/route/sample/{id}', 'route_sample_id', function($request, $response){
            return $request->get('message');
        });

        $this->router->post('/route/sample/{id}', 'route_sample_id_post', function($request, $response){
            return $request->get('message');
        });
    }

    public function testHandlerMethod() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);


        $this->assertEquals(200, $routeHandler->handle()->getStatusCode());
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testHandlerMethodNotFound() {
        $request      = Request::create('/xxx', 'GET');
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->handle();
    }

    /**
     * @expectedException \Exception
     */
    public function testHandlerMethodFiveHundred() {
        $this->router->get('/foo/{bar}', 'foo', function($bar, $request, $response){
            throw new \Exception('error');
        });

        $request      = Request::create('/foo/bar', 'GET');
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->handle();
    }

    public function testGetAction() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->getMatched();

        $action = $routeHandler->getAction();
        $this->assertTrue(is_callable($action));
    }

    public function testGetMatched() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertFalse(is_callable($routeHandler->getAction()));

        $routeHandler->getMatched();

        $this->assertTrue(is_callable($routeHandler->getAction()));
    }

    public function testCleanParamBag() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->getMatched();

        $action = $routeHandler->getAction();
        $this->assertTrue(is_callable($action));

        $routeHandler->cleanParamBag();

        $this->assertFalse(is_callable($routeHandler->getAction()));
    }

    public function testParamsBagIsEmpty() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertEmpty($routeHandler->getParamsForAction());
    }

    public function testParamsBagIsNotEmpty() {
        $request      = Request::create('/route/1', 'GET');
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->setParamsForAction();

        $this->assertNotEmpty($routeHandler->getParamsForAction());
    }

    public function testParamsBagIsSizeOne() {
        $request      = Request::create('/route/1', 'GET', ['param' => 'value']);
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->setParamsForAction();

        $this->assertTrue(count($routeHandler->getParamsForAction()) === 1);
    }

    public function testParamsBagIsSizeOneAndMessageExists() {
        $request      = Request::create('/route/sample/1', 'GET', ['message' => 'value']);
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->setParamsForAction();

        $this->assertTrue(count($routeHandler->getParamsForAction()) === 1);

        $this->assertEquals('value', $routeHandler->handle());
    }

    public function testIsGetIsTrue() {
        $request      = Request::create('/route/sample/1', 'GET', ['message' => 'value']);
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertTrue($routeHandler->isGet());
    }

    public function testIsGetIsFalse() {
        $request      = Request::create('/route/sample/1', 'POST', ['message' => 'value']);
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertFalse($routeHandler->isGet());
    }

    public function testCreatePreparedResponse() {
        $request      = Request::create('/route/sample/1', 'POST', ['message' => 'value']);
        $matcher      = new UrlMatcher($this->router->getCollection(), $this->router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertInstanceOf(Symfony\Component\HttpFoundation\Response::class, $routeHandler->createPreparedResponse());
    }
}
