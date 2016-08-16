<?php

use IceCreamRouter\RouteHandler;
use IceCreamRouter\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class RouteHandlerTest extends \PHPUnit_Framework_TestCase {
    private $_router;

    public function setup() {
        $this->_router = new Router();

        $this->_router->get('/route', 'route', function($request, $response){
            return new Response('route');
        });

        $this->_router->get('/route/{id}', 'route_id', function($request, $response){
            return new Response('route');
        });

        $this->_router->get('/route/sample/{id}', 'route_sample_id', function($request, $response){
            return $request->get('message');
        });

        $this->_router->post('/route/sample/{id}', 'route_sample_id_post', function($request, $response){
            return $request->get('message');
        });
    }

    public function testHandlerMethod() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);


        $this->assertEquals(200, $routeHandler->handle()->getStatusCode());
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testHandlerMethodNotFound() {
        $request      = Request::create('/xxx', 'GET');
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->handle();
    }

    /**
     * @expectedException \Exception
     */
    public function testHandlerMethodFiveHundred() {
        $this->_router->get('/foo/{bar}', 'foo', function($bar, $request, $response){
            throw new \Exception('error');
        });

        $request      = Request::create('/foo/bar', 'GET');
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->handle();
    }

    public function testGetCallback() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->getMatched();

        $callback = $routeHandler->getCallback();
        $this->assertTrue(is_callable($callback));
    }

    public function testGetMatched() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertFalse(is_callable($routeHandler->getCallback()));

        $routeHandler->getMatched();

        $this->assertTrue(is_callable($routeHandler->getCallback()));
    }

    public function testCleanParamBag() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->getMatched();

        $callback = $routeHandler->getCallback();
        $this->assertTrue(is_callable($callback));

        $routeHandler->cleanParamBag();

        $this->assertFalse(is_callable($routeHandler->getCallback()));
    }

    public function testParamsBagIsEmpty() {
        $request      = Request::create('/route', 'GET');
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertEmpty($routeHandler->getParamsForCallback());
    }

    public function testParamsBagIsNotEmpty() {
        $request      = Request::create('/route/1', 'GET');
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->setParamsForCallBack();

        $this->assertNotEmpty($routeHandler->getParamsForCallback());
    }

    public function testParamsBagIsSizeOne() {
        $request      = Request::create('/route/1', 'GET', ['param' => 'value']);
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->setParamsForCallBack();

        $this->assertTrue(count($routeHandler->getParamsForCallback()) === 1);
    }

    public function testParamsBagIsSizeOneAndMessageExists() {
        $request      = Request::create('/route/sample/1', 'GET', ['message' => 'value']);
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $routeHandler->setParamsForCallBack();

        $this->assertTrue(count($routeHandler->getParamsForCallback()) === 1);

        $this->assertEquals('value', $routeHandler->handle());
    }

    public function testIsGetIsTrue() {
        $request      = Request::create('/route/sample/1', 'GET', ['message' => 'value']);
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertTrue($routeHandler->isGet());
    }

    public function testIsGetIsFalse() {
        $request      = Request::create('/route/sample/1', 'POST', ['message' => 'value']);
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertFalse($routeHandler->isGet());
    }

    public function testCreatePreparedResponse() {
        $request      = Request::create('/route/sample/1', 'POST', ['message' => 'value']);
        $matcher      = new UrlMatcher($this->_router->getCollection(), $this->_router->getContext($request));
        $routeHandler = new RouteHandler($request, $matcher);

        $this->assertInstanceOf(Symfony\Component\HttpFoundation\Response::class, $routeHandler->createPreparedResponse());
    }
}
