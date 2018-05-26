<?php

namespace IceCreamRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class RouteHandler {

    private $request;

    private $matcher;

    private $paramsForAction = [];

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
     * Processes the route based on request. Returns the (executed) action
     * of the registered route when found.
     *
     * @throws Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @throws \Exception
     */
    public function handle() {
        $this->getMatched();

        $action = $this->getAction();

        $this->cleanParamBag();
        $this->setParamsForAction();

        if ($this->isGet()) {
            $this->paramsForAction[] = $this->createPreparedResponse();

        }

        return call_user_func_array($action, $this->paramsForAction);
    }

    /**
     * Get's the action for the route.
     *
     * Can return null if you haven't called getMatched first.
     *
     * @return Closure or Null
     */
    public function getAction() {
        return $this->request->get('action');
    }

    /**
     * Add the matched route information to the request attributes.
     */
    public function getMatched() {
        $this->request->attributes->add($this->matcher->match($this->request->getPathInfo()));
    }

    /**
     * Removes action and _route from the param bag.
     */
    public function cleanParamBag() {
        $this->request->attributes->remove('action');
        $this->request->attributes->remove('_route');
    }

    /**
     * Creates, from the request attributes, an array of params.
     *
     * The array is used for the action closure.
     */
    public function setParamsForAction() {
        foreach ($this->request->attributes as $attributesKey => $attributesValue) {
            $this->paramsForAction[] = $attributesValue;
        }

        $this->paramsForAction[] = $this->request;
    }

    /**
     * Gets an array of params for the closure.
     *
     * @return array
     */
    public function getParamsForAction(): array {
        return $this->paramsForAction;
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
