<?php

namespace Framework\Http\Routing;

use Framework\Helpers\Helper;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Traits\Singleton;

class Router {

    use Singleton;

    protected $routes = [];

    protected $request;

    protected $responce;

    public function __construct() {
        $this->request = Request::getInstance();

        $this->responce = Response::getInstance();
    }

    public function get(string $pattern, callable $callback) {
        $this->pushRoute(
            [ Request::METHOD_HEAD, Request::METHOD_GET ],
            $pattern,
            $callback
        );
    }

    public function post(string $pattern, callable $callback) {
        $this->pushRoute(
            [ Request::METHOD_POST ],
            $pattern,
            $callback
        );
    }

    public function put(string $pattern, callable $callback) {
        $this->pushRoute(
            [ Request::METHOD_PUT ],
            $pattern,
            $callback
        );
    }

    public function patch(string $pattern, callable $callback) {
        $this->pushRoute(
            [ Request::METHOD_PATCH ],
            $pattern,
            $callback
        );
    }

    public function delete(string $pattern, callable $callback) {
        $this->pushRoute(
            [ Request::METHOD_DELETE ],
            $pattern,
            $callback
        );
    }

    public function any(string $pattern, callable $callback) {
        $this->pushRoute(
            [ Request::METHOD_DELETE, Request::METHOD_PATCH, Request::METHOD_PUT, Request::METHOD_POST, Request::METHOD_GET, Request::METHOD_HEAD ],
            $pattern,
            $callback
        );
    }

    protected function pushRoute(array $methods, string $pattern, callable $callback) {

        $route = new Route($methods, $pattern, $callback);

        $this->routes[] = $route;
    }

    public function run() {
        
        foreach ($this->routes as $key => $route){

            if(in_array($this->request->getMethod(), $route->getMethods()) && $route->comparePattern($this->request->getUri())) {
                $dispatch = $route->dispatch();

                if($dispatch)
                    break;
            }
            
        }

        // TODO: return $responce->error(404);

    }

    /**
     * @return array
     */
    public function getRoutes() : array {
        return $this->routes;
    }

}