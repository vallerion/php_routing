<?php
namespace Framework\Http;

use Framework\Psr\Http\Message\MessageInterface;
use Framework\Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface {

    protected $protocolVersion = '1.1';


    protected static $validProtocolVersion = [
        '1.0',
        '1.1',
        '2.0'
    ];

    protected $headers;

    protected $body;




    public function getProtocolVersion() {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version) {

        if ( ! isset(self::$validProtocolVersion[$version])){
            throw new \InvalidArgumentException('Invalid HTTP version. 
            Must be one of ' . implode(',', array_values(self::$validProtocolVersion)));
        }

        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function hasHeader($name) {
        return key_exists($name, $this->headers);
    }

    public function getHeader($name) {
        return $this->headers[$name];
    }

    public function getHeaderLine($name) {
        return implode(',', $this->headers[$name]);
    }

    public function withHeader($name, $value) {

//        $clone = clone $this;

        if($this->hasHeader($name)) {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    public function withAddedHeader($name, $value) {

//        $clone = clone $this;
        $this->headers[$name] = $value;

        return $this;
    }

    public function withoutHeader($name) {
//        $clone = clone $this;
        unset($this->headers[$name]);

        return $this;
    }

    public function getBody() {
        return $this->body;
    }

    public function withBody(StreamInterface $body) {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

}