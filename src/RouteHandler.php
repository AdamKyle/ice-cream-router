<?php

namespace IceCreamRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class RouteHandler {

    private $request;

    private $matcher;

    private $paramsForCallback = [];

    /**
     * Constructor
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Symfony\Component\Routing\Matcher\UrlMatcher $response
     */
    public function __construct(Request $request, UrlMatcher $matcher) {
        $this->request = $request;
        $this->matcher = $matcher;
    }

    /**
     * Handles the route.
     *
     * Processes the route based on request. Returns the (executed) callback
     * of the registered route when found.
     *
     * @throws Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @throws \Exception
     */
    public function handle() {
        $this->getMatched();

        $callback = $this->getCallback();

        $this->cleanParamBag();
        $this->setParamsForCallBack();

        if ($this->isGet()) {
            $this->paramsForCallback[] = $this->createPreparedResponse();

        }

        return call_user_func_array($callback, $this->paramsForCallback);
    }

    /**
     * Get's the callable closure.
     *
     * Can return null if you haven't called getMatched first.
     *
     * @return Closure or Null
     */
    public function getCallback() {
        return $this->request->get('callback');
    }

    /**
     * Add the matched route information to the request attributes.
     */
    public function getMatched() {
        $this->request->attributes->add($this->matcher->match($this->request->getPathInfo()));
    }

    /**
     * Removes callback and _route from the param bag.
     */
    public function cleanParamBag() {
        $this->request->attributes->remove('callback');
        $this->request->attributes->remove('_route');
    }

    /**
     * Creates, from the request attributes, an array of params.
     *
     * The array is used for the callback closure.
     */
    public function setParamsForCallBack() {
        foreach ($this->request->attributes as $attributesKey => $attributesValue) {
            $this->paramsForCallback[] = $attributesValue;
        }

        $this->paramsForCallback[] = $this->request;
    }

    /**
     * Gets an array of params for the closure.
     *
     * @return array
     */
    public function getParamsForCallback(): array {
        return $this->paramsForCallback;
    }

    /**
     * Checks if the request method is a get or not.
     *
     * @return bool
     */
    public function isGet() {
        return $this->request->isMethod('GET');
    }

    /**
     * Prepares a response based on the request.
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function createPreparedResponse() {
        return Response::create()->prepare($this->request);
    }
}
