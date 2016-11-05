<?php

namespace Support;

class Singleton{

    protected static $instance;

    protected function __construct(){

    }

    private function __clone(){

    }

    public static function getInstance(...$params){

        if(static::$instance === null)
            static::$instance = new static(...$params);

        return static::$instance;
    }

}