<?php

namespace IceCreamRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class RouteHandler {

    private $_request;

    private $_matcher;

    private $_paramsForCallback = [];

    /**
     * Constructor
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Symfony\Component\Routing\Matcher\UrlMatcher $response
     */
    public function __construct(Request $request, UrlMatcher $matcher) {
        $this->_request = $request;
        $this->_matcher = $matcher;
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
            $this->_paramsForCallback[] = $this->createPreparedResponse();

        }

        return call_user_func_array($callback, $this->_paramsForCallback);
    }

    /**
     * Get's the callable closure.
     *
     * Can return null if you haven't called getMatched first.
     *
     * @return Closure or Null
     */
    public function getCallback() {
        return $this->_request->get('callback');
    }

    /**
     * Add the matched route information to the request attributes.
     */
    public function getMatched() {
        $this->_request->attributes->add($this->_matcher->match($this->_request->getPathInfo()));
    }

    /**
     * Removes callback and _route from the param bag.
     */
    public function cleanParamBag() {
        $this->_request->attributes->remove('callback');
        $this->_request->attributes->remove('_route');
    }

    /**
     * Creates, from the request attributes, an array of params.
     *
     * The array is used for the callback closure.
     */
    public function setParamsForCallBack() {
        foreach ($this->_request->attributes as $attributesKey => $attributesValue) {
            $this->_paramsForCallback[] = $attributesValue;
        }

        $this->_paramsForCallback[] = $this->_request;
    }

    /**
     * Gets an array of params for the closure.
     *
     * @return array
     */
    public function getParamsForCallback(): array {
        return $this->_paramsForCallback;
    }

    /**
     * Checks if the request method is a get or not.
     *
     * @return bool
     */
    public function isGet() {
        return $this->_request->isMethod('GET');
    }

    /**
     * Prepares a response based on the request.
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function createPreparedResponse() {
        return Response::create()->prepare($this->_request);
    }
}
