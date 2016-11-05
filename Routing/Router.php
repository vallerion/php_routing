<?php

namespace Routing;

//require_once 'RouteCollection.php';
require __DIR__ . '/Helpers/Helper.php';
require __DIR__ . '/Support/Singleton.php';
require __DIR__ . '/Http/Request.php';
require __DIR__ . '/Http/Response.php';
require __DIR__ . '/Route.php';

use Helpers\Helper;
use Http\Request;
use Http\Response;
use Support\Singleton;

class Router extends Singleton{

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

    protected function pushRoute(array $methods, string $pattern, callable $callback) {

        $route = new Route($methods, $pattern, $callback);

        $this->routes[] = $route;
    }

    public function run() {

//        Helper::dumperDie($this->request);
        
        foreach ($this->routes as $key => $route){

            if(in_array($this->request->getMethod(), $route->getMethods()) && $route->comparePattern($this->request->getUriFull())) {
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