<?php

namespace Http;

require __DIR__ . '/../Traits/Codes.php';

use Helpers\Helper;
use Support\Singleton;
use Traits\Codes;

class Response extends Singleton {
    
    use Codes;

    protected $status;

    protected $headers;

    protected $cookies;

    protected $body;

    protected $length;
    
    protected function __construct() {
        
    }

}