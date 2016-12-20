<?php

namespace Framework\Http;

use Framework\Helpers\Helper;
use Framework\Psr\Http\Message\ResponseInterface;
use Framework\Traits\Singleton;
use Framework\Traits\Codes;
use Framework\Psr\Http\Message\UriInterface;

use Framework\View\View;
use InvalidArgumentException;

class Response extends Message implements ResponseInterface {

    use Singleton;
    use Codes; // todo: what for trait Codes?

    protected $status = 200;

    protected $reasonPhrase; // ex. 404 Page non found

//    protected $headers;

    protected $cookies = [];

//    protected $body;

    protected $length;

    protected $view;

    protected function __construct($status = 200, $headers = null, $body = null) {

        $this->status = $this->normalizeStatusCode($status);

        $this->headers = is_null($headers) ? $this->makeDefaultHeaders() : $headers;
        $this->setCurrentHeaders();

        $this->body = $body;

        $this->view = View::getInstance();

//        Helper::dumperDie($this->headers);
    }

    protected function makeDefaultHeaders($usersData = []) {
        return array_merge([
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'REQUEST_METHOD'       => 'GET',
            'SCRIPT_NAME'          => '',
            'REQUEST_URI'          => '',
            'QUERY_STRING'         => '',
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => 80,
            'HTTP_HOST'            => 'localhost',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'HTTP_USER_AGENT'      => 'Slim Framework',
            'REMOTE_ADDR'          => '127.0.0.1',
            'REQUEST_TIME'         => time(),
            'REQUEST_TIME_FLOAT'   => microtime(true),
        ], $usersData);
    }

    protected function setCurrentHeaders() {

        foreach ($_SERVER as $name => $value)
            if($this->hasHeader($name))
                $this->headers[$name] = $value;

    }

    protected function normalizeStatusCode($status) {

        if ( ! is_integer($status) || $status < 100 || $status > 599) {
            throw new InvalidArgumentException('Invalid HTTP status code');
        }

        return $status;
    }

    public function status($newStatus = null) {

        if( ! is_null($newStatus))
            $this->status = $this->normalizeStatusCode($newStatus);

        return $this->status;
    }

    public function reasonPhrase($reasonPhrase = null) {

        if( ! is_null($reasonPhrase) && ! empty($reasonPhrase))
            $this->reasonPhrase = $reasonPhrase;

        return $this->reasonPhrase;
    }

    public function redirect($url, $status = 302) {
        $responce = $this->withAddedHeader('Location', (string)$url);
    }

    public function isEmpty() {
        return in_array($this->getStatusCode(), [204, 205, 304]);
    }

    public function write($data) {
        $this->body .= $data;
    }

    public function view($template, $values = []) {
        $this->view->template($template, $values);
    }


    /**
     *
     * usage:
     *
     * $response->cookie('a', 'b', ['expires' => time()+3600, 'path' => 'home']);
     *
     * @param $name
     * @param $value
     * @param array $property
     */
    public function cookie($name, $value, $property = []) {

        $value = ['value' => (string)$value];
        if( ! empty($property))
            $value = array_merge($value, $property);

//        $this->cookies[$name] = array_replace($this->cookies, $value);
        $this->cookies[$name] = $value;
    }

    protected function cookiesToHeaders() {
        $headers = [];
        foreach ($this->cookies as $name => $properties) {
            $headers[] = $this->cookiesToHeader($name, $properties);
        }

        return $headers;
    }

    protected function cookiesToHeader($name, $properties) {

        $result = urlencode($name) . '=' . urlencode($properties['value']);

        if (isset($properties['domain'])) {
            $result .= '; domain=' . $properties['domain'];
        }

        if (isset($properties['path'])) {
            $result .= '; path=' . $properties['path'];
        }

        if (isset($properties['expires'])) {
            if (is_string($properties['expires'])) {
                $timestamp = strtotime($properties['expires']);
            } else {
                $timestamp = (int)$properties['expires'];
            }
            if ($timestamp !== 0) {
                $result .= '; expires=' . gmdate('D, d-M-Y H:i:s e', $timestamp);
            }
        }

        if (isset($properties['secure']) && $properties['secure']) {
            $result .= '; secure';
        }

        if (isset($properties['hostonly']) && $properties['hostonly']) {
            $result .= '; HostOnly';
        }

        if (isset($properties['httponly']) && $properties['httponly']) {
            $result .= '; HttpOnly';
        }

        return $result;
    }
    
    
    public function getStatusCode() {
        return $this->status();
    }

    public function withStatus($code, $reasonPhrase = '') {
        $this->status($code);
        $this->reasonPhrase($reasonPhrase);
    }

    public function getReasonPhrase() {
        return $this->reasonPhrase();
    }

    public function respond() {

        $cookie = $this->cookiesToHeaders();

        $this->withAddedHeader('Set-Cookie', $cookie);

        if( ! headers_sent()){

            header(sprintf(
                'HTTP/%s %s %s',
                $this->getProtocolVersion(),
                $this->status(),
                $this->reasonPhrase()
            ));

//            $output .= PHP_EOL;
            foreach ($this->getHeaders() as $name => $values) {

                if(is_array($values))
                    foreach ($values as $val) {
                        header(sprintf('%s: %s', $name, $val), false);
                    }
                else
                    header(sprintf('%s: %s', $name, $this->getHeader($name)), false);
            }


        }
    }

    public function __toString() {

        $output = (string)$this->view->render();

        $output .= (string)$this->getBody();

        return $output;
    }

}