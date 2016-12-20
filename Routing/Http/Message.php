<?php
namespace Routing\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

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

        if( ! isset(static::$validProtocolVersion[$version]))
            throw new InvalidArgumentException("Invalid version of http protocol.");

        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function hasHeader($name) {
        return isset($this->headers[$name]);
    }

    public function getHeader($name) {
        return $this->headers[$name];
    }

    public function getHeaderLine($name) {
        // TODO: Implement getHeaderLine() method.
    }

    public function withHeader($name, $value) {

        $clone = clone $this;

        $clone->headers[$name] = $value;

        return $clone;
    }

    public function withAddedHeader($name, $value) {

        $clone = clone $this;

        $clone->headers[$name] = $value;

        return $clone;
    }

    public function withoutHeader($name)  {

        $clone = clone $this;

        unset($clone->headers[$name]);

        return $clone;
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