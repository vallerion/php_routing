<?php

namespace Framework\Http;

use Framework\Helpers\Helper;
use Framework\Psr\Http\Message\ServerRequestInterface;
use Framework\Psr\Http\Message\UriInterface;
use Framework\Traits\Singleton;

use InvalidArgumentException;

class Request extends Message implements ServerRequestInterface {

    use Singleton;

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    protected $validMethods = [
        'HEAD',
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE'
    ];

//    protected static $headers = [];

    protected $method;

    protected $code;

    protected $fields = [];

//    protected $body;

    protected $cookie;

    protected $session;

    protected $uploadFiles;

    protected $queryParams;


    protected function __construct(array $userSettings = []) {


        $this->headers = $this->makeDefaultHeaders();
        $this->setCurrentHeaders();

        $this->setQueryData();

//        Helper::dumper($this->uploadFiles);
//        Helper::dumperDie($this->headers);

        if( ! empty($userSettings))
            $this->headers = array_merge($this->headers, $userSettings);

    }

    protected function setCurrentHeaders() {

        $this->headers['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];

        $this->headers['QUERY_STRING'] = urldecode($_SERVER['QUERY_STRING']); // example.com/page/?a=b&c=d -> a=b&c=d

        $this->headers['REQUEST_URI_FULL'] = urldecode($_SERVER['REQUEST_URI']);

        $this->headers['REQUEST_URI'] = str_replace([$this->headers['QUERY_STRING'], '?'], '', $this->headers['REQUEST_URI_FULL']); // example.com/page/?a=b&c=d -> /page/

        $this->headers['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;

        $this->headers['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];

        $this->headers['SERVER_SOFTWARE'] = $_SERVER['SERVER_SOFTWARE'] ?? '';

        $this->headers['CONTENT_LENGTH'] = $_SERVER['CONTENT_LENGTH'];

        $this->headers['CONTENT_TYPE'] = $_SERVER['CONTENT_TYPE'];

        $this->headers['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];

        $this->headers['ACCEPT'] = $_SERVER['HTTP_ACCEPT'];

        $this->headers['PATH_INFO'] = $_SERVER['PATH_INFO'];

//        $this->headers['COOKIE'] = $_SERVER['HTTP_COOKIE'];

        $this->headers['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
    }

    protected function makeDefaultHeaders($usersData = []) {

        return array_merge([
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '',
            'PATH_INFO' => '',
            'QUERY_STRING' => '',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'USER_AGENT' => 'localhost',
            'REMOTE_ADDR' => '127.0.0.1',
            'CONTENT_TYPE' => ''
        ], $usersData);

    }

    protected function setQueryData(){

        $input = file_get_contents('php://input');

        if($input === false)
            throw new \Exception('Can`t get data by ' . $this->method);

        $this->queryParams = $this->headers['QUERY_STRING'];

        $this->body = $this->setBody($input);

        $this->fields = array_diff($_REQUEST, $_COOKIE); // all but cookie

        $this->cookie = $_COOKIE;

        $this->uploadFiles = $_FILES; // todo: create FILE class

        if(session_status() != PHP_SESSION_NONE)
            $this->session = $_SESSION;

    }

    protected function setBody($input) {

        if($this->isJson())
            return json_decode($input, true);
        else if($this->isXml()) {
            $backup = libxml_disable_entity_loader(true);
            $result = simplexml_load_string($input);
            libxml_disable_entity_loader($backup);
            return $result;
        }
        else if ($this->isMedia()){
            parse_str($input, $data);
            return $data;
        }

        return $input;
    }

    public function headers() {
        
        if(empty($this->headers)){
            $this->headers = $this->makeDefaultHeaders();
            $this->setCurrentHeaders();
        }

        return $this->headers;
    }

    public function getUri() {
        return $this->headers['REQUEST_URI'];
    }

    public function getUriFull() : string {
        return self::$headers['REQUEST_URI_FULL'];
    }

    public function getMethod() : string {
        return $this->headers['REQUEST_METHOD'];
    }

    public function isJson() {
        return $this->headers['CONTENT_TYPE'] === 'application/json';
    }

    public function isXml() {
        return  $this->headers['CONTENT_TYPE'] === 'application/xml' ||
                $this->headers['CONTENT_TYPE'] === 'text/xml';
    }

    public function isXhr() {
        return $this->hasHeader('X-Requested-With') &&
            $this->headers['X-Requested-With'] === 'XMLHttpRequest';
    }

    public function isMedia() {
        return stristr($this->headers['CONTENT_TYPE'], 'multipart/form-data');
    }


    public function __get($name) {
        return isset($this->fields[$name]) ?
            $this->fields[$name] : null;
    }


    public function getRequestTarget() {
        return $this->getUri();
    }

    public function withRequestTarget($requestTarget) {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; must be a string and cannot contain whitespace'
            );
        }
//        $clone = clone $this;
        $this->requestTarget = $requestTarget;

        return $this;
    }

    public function withMethod($method) {

        $method = $this->normalizeMethod($method);
        $this->method = $method;

        return $this;
    }

    protected function normalizeMethod($method) {

        if ($method === null) {
            return $method;
        }

        if ( ! is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }

        $method = strtoupper($method);
        if ( ! in_array($method, $this->validMethods)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }

        return $method;
    }

    public function withUri(UriInterface $uri, $preserveHost = false) {
        // TODO: Implement withUri() method.
    }

    public function getUploadedFiles() {
        return $this->uploadFiles;
    }

    public function getCookieParams() {
        return $this->cookie;
    }
    
    public function cookie() {
        // TODO: must return Cookie object
    }

    public function session() {
        // todo: must return Session object
    }

    public function getQueryParams() {
        return $this->queryParams;
    }





    public function getServerParams() {
        // TODO: Implement getServerParams() method.
    }

    public function withCookieParams(array $cookies) {
        // TODO: Implement withCookieParams() method.
    }



    public function withQueryParams(array $query) {
        // TODO: Implement withQueryParams() method.
    }

    public function withUploadedFiles(array $uploadedFiles) {
        // TODO: Implement withUploadedFiles() method.
    }

    public function getParsedBody() {
        // TODO: Implement getParsedBody() method.
    }

    public function withParsedBody($data) {
        // TODO: Implement withParsedBody() method.
    }

    public function getAttributes() {
        // TODO: Implement getAttributes() method.
    }

    public function getAttribute($name, $default = null) {
        // TODO: Implement getAttribute() method.
    }

    public function withAttribute($name, $value) {
        // TODO: Implement withAttribute() method.
    }

    public function withoutAttribute($name) {
        // TODO: Implement withoutAttribute() method.
    }


}