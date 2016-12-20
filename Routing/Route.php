<?php

namespace Framework\Http\Routing;


use Framework\Helpers\Helper;
use Framework\Http\Request;

/**
 * Class Route
 * @package Routing
 */
class Route{

    /**
     * @var string http request method (GET, POST etc)
     */
    protected $method;

    /**
     * @var array many http request method
     */
    protected $methods = [];

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var string route pattern ('/test/')
     */
    protected $pattern;

    /**
     * @var array ($name => $value) parameters route ('test/{id}')
     */
    protected $parameters = [];

    /**
     * @var array parameters name route ('test/{id}')
     */
    protected $paramNames = [];

    /**
     * Route constructor.
     * @param array $methods
     * @param string $pattern
     * @param callable $callback
     */
    public function __construct(array $methods, string $pattern, callable $callback) {

        $this->setMethods($methods);

        $this->setPattern($pattern);

        $this->setCallback($callback);


//        $this->pattern = '/test';
    }

    /**
     * Callback function for preg_replace_callback
     *
     * @param array $match
     * @return string
     */
    protected function urlToParameters(array $match) : string{

        $this->paramNames[] = $match[1];

        return '(?P<' . $match[1] . '>[^/]+)';
    }

    /**
     * Compare urls
     * $uri - current url
     *
     * @param string $uri
     * @return bool
     */
    public function comparePattern(string $uri) : bool {

        $patternToRegexp = preg_replace_callback(
            '|{([\w]+)}|',
            [ $this, 'urlToParameters' ],
            str_replace(')', ')?', (string)$this->pattern)
        );

//        $patternToRegexp = str_replace('/', '/?', $patternToRegexp);
        $regexp = "|^$patternToRegexp$|i";
        $regexp = str_replace('|^/', '|^', $regexp);

        $uri = implode('/', array_filter(explode('/', $uri), 'mb_strlen'));

        if( ! preg_match($regexp, $uri, $paramValues))
            return false;


        foreach ($this->paramNames as $name) {
            if(isset($paramValues[$name]))
                $this->parameters[$name] = $paramValues[$name];
        }

        return true;
    }

    /**
     * Invoke callback
     *
     * @return bool
     */
    public function dispatch() : bool {

        $result = call_user_func_array($this->getCallback(), array_values($this->parameters));

        return $result === false ? false : true;
    }

    /**
     * @return array
     */
    public function getParameters() : array {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getMethods() {
        return $this->methods;
    }

    /**
     * @param array $methods
     */
    public function setMethods(array $methods) {
        $this->methods = count($methods) ? $methods : [ Request::METHOD_GET ];
    }

    /**
     * @return callable
     */
    public function getCallback() : callable {
        return $this->callback;
    }

    /**
     * @param callable $callback
     */
    public function setCallback(callable $callback) {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getPattern() : string {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern(string $pattern) {
        $this->pattern = $pattern;
    }

}