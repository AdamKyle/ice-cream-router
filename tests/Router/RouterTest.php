<?php

use IceCreamRouter\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase {

    public function testSimpleGETRoute() {
        $router = new Router('App\\Sample\\Namespace');

        $router->get('/foo', 'foo', function($request, $response){
            return $response->setContent('hello world');
        });

        $response = $router->processRoutes(Request::create('/foo', 'GET'));
        $this->assertEquals('hello world', $response->getContent());
    }

    public function testSimpleGETRouteStatus200() {
        $router = new Router('App\\Sample\\Namespace');

        $router->get('/foo', 'foo', function($request, $response){
            return $response->getStatusCode();
        });

        $response = $router->processRoutes(Request::create('/foo', 'GET'));
        $this->assertEquals(200, $response);
    }

    public function testFoundRouteWithParam() {
        $router = new Router('App\\Sample\\Namespace');

        $router->get('/foo/{bar}', 'foo', function($bar, $request, $response){
            return 'hello ' . $request->attributes->get('bar');
        });

        $response = $router->processRoutes(Request::create('/foo/bar', 'GET'));

        $this->assertEquals('hello bar', $response);
    }

    public function testValidateActionWhenActionIsClassMethod() {
        $router = new Router('App\\Sample\\Namespace');
        $router->get('/foo/{bar}', 'foo', 'Action:sampleAction');

        $action = $router->getCollection()->get('foo')->getDefaults()['action'];

        $this->assertEquals($action['class'], 'App\\Sample\\Namespace\\Action');
        $this->assertEquals($action['method'], 'sampleAction');
    }

    /**
     * @expectedException \Exception
     */
    public function testFailRouteActionValidation() {
        $router = new Router('App\\Sample\\Namespace');
        $router->get('/foo/{bar}', 'foo', 'Action_sampleAction');
    }

    public function testFourOhFourRoute() {
        $router = new Router('App\\Sample\\Namespace');

        $response = $router->processRoutes(Request::create('/foo', 'GET'));

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testFiveHundred() {
        $router = new Router('App\\Sample\\Namespace');

        $router->get('/foo/{bar}', 'foo', function($bar, $request, $response){
            throw new \Exception('error');
        });

        $response = $router->processRoutes(Request::create('/foo/bar', 'GET'));

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testBasicPost() {
        $router = new Router('App\\Sample\\Namespace');

        $router->post('/foo', 'foo', function($request){
            return $request->get('message');
        });

        $response = $router->processRoutes(Request::create('/foo', 'POST', ['message' => 'hello world']));

        $this->assertEquals('hello world', $response);
    }

    public function testPostWithResponse() {
        $router = new Router('App\\Sample\\Namespace');

        $router->post('/foo', 'foo', function($request){
            $message = $request->get('message');

            return new Response($message);
        });

        $response = $router->processRoutes(Request::create('/foo', 'POST', ['message' => 'hello world']));
        $this->assertEquals('hello world', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostWithParamAndGetResponse() {
        $router = new Router('App\\Sample\\Namespace');

        $router->post('/foo/{id}', 'foo', function($id, $request){
            return new Response('Request Message: ' . $request->get('message') . ' Id Passed in: ' . $id);
        });

        $response = $router->processRoutes(Request::create('/foo/6', 'POST', ['message' => 'hello world']));
        $this->assertEquals('Request Message: hello world Id Passed in: 6', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPutWithParamAndGetResponse() {
        $router = new Router('App\\Sample\\Namespace');

        $router->put('/foo/{id}', 'foo', function($id, $request){
            return new Response('Request Message: ' . $request->get('message') . ' Id Passed in: ' . $id);
        });

        $response = $router->processRoutes(Request::create('/foo/6', 'PUT', ['message' => 'hello world']));
        $this->assertEquals('Request Message: hello world Id Passed in: 6', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteWithParamAndGetResponse() {
        $router = new Router('App\\Sample\\Namespace');

        $router->delete('/foo/{id}', 'foo', function($id, $request){
            return new Response('id: ' . $id . ' was deleted');
        });

        $response = $router->processRoutes(Request::create('/foo/6', 'DELETE'));
        $this->assertEquals('id: 6 was deleted', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testTwoRoutesWithDifferentMethods() {
        $router = new Router('App\\Sample\\Namespace');

        $router->get('/foo/{id}', 'foo', function($id, $request, $response){
            return $response->setContent('ID: ' . $id);
        });

        $router->post('/foo/{id}', 'foo_bar', function($id, $request){
            return new Response('Request Message: ' . $request->get('message') . ' Id Passed in: ' . $id);
        });

        $response = $router->processRoutes(Request::create('/foo/6', 'POST', ['message' => 'hello world']));
        $this->assertEquals('Request Message: hello world Id Passed in: 6', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());

        $response = $router->processRoutes(Request::create('/foo/6', 'GET'));
        $this->assertEquals('ID: 6', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testExsitingDefaultRouteFourOhFour() {
        $router = new Router('App\\Sample\\Namespace');

        $response = $router->processRoutes(Request::create('/404', 'GET'));
        $this->assertEquals('oops! could not find what you were looking for.', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testExsitingDefaultRouteFiveHundred() {
        $router = new Router('App\\Sample\\Namespace');

        $response = $router->processRoutes(Request::create('/500', 'GET', ['error_details' => 'oops! Something went wrong.']));
        $this->assertEquals('oops! Something went wrong.', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testOverRideExsitingDefaultRouteCantFindRoute() {
        $router = new Router('App\\Sample\\Namespace');

        $router->overrideDefaulRoute('xxx', function(){ return ''; });
    }

    public function testOverRideExsitingDefaultRoute() {
        $router = new Router('App\\Sample\\Namespace');

        $router->overrideDefaulRoute('404', function(){ return new Response('hello world.'); });

        $response = $router->processRoutes(Request::create('/404', 'GET'));
        $this->assertEquals('hello world.', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetContextFromRequest() {
        $router = new Router('App\\Sample\\Namespace');

        $this->assertInstanceOf(Symfony\Component\Routing\RequestContext::class, $router->getContext(Request::create('/')));
    }

    public function testGetCollection() {
        $router = new Router('App\\Sample\\Namespace');
        $this->assertInstanceOf(Symfony\Component\Routing\RouteCollection::class, $router->getCollection());
    }
}
