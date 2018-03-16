<?php

use IceCreamRouter\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase {

    public function testCreateRoute() {
        $route = new Route('/foo', 'post', function($request){ return $request; });

        $this->assertInstanceOf(Symfony\Component\Routing\Route::class, $route->getRoute());
    }
}
