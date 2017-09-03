<?php

namespace gsoft\Controllers;


class Request
{
    private $input;
    private $key;
    private $callable;
    private $controller;
    private $invert;
    
    function __construct($input, $key, callable $call, $controller, $invert = false)
    {
        $this->input = $input;
        $this->key = $key;
        $this->callable = $call;
        $this->controller = $controller;
        $this->invert = $invert;
    }
    
    function call()
    {
        if (!$this->invert) {
            if ( array_key_exists($this->key, $this->input) ) {
                return ($this->callable)($this->key, $this->input[$this->key], $this->controller);
            } else return false;
        } else {
            if ( !array_key_exists($this->key, $this->input) ) {
                return ($this->callable)($this->key, null, $this->controller);
            } else return false;
        }
    }
}