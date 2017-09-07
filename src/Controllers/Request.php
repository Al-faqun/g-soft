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
    
    /**
     * Returns result of called callable, if all needed keys exists, or false.
     * @return bool|mixed
     */
    function call()
    {
        if (!$this->invert) {
            //if several keys
            if (is_array($this->key)) {
                //check that every key is present
                $previousResult = true;
                foreach ($this->key as $key) {
                    //if every key exists -> proceed, else - do nothing
                    if ( array_key_exists($key, $this->input) AND $previousResult === true) {
                        $values[$key] = $this->input[$key];
                        $previousResult = true;
                    } else {
                        $previousResult = false;
                    }
                }
                //if every key exists
                if ($previousResult === true AND !empty($values)) {
                    $return = ($this->callable)($this->key, $values, $this->controller);
                } else $return = false;
                
            //if single value key
            } elseif (array_key_exists($this->key, $this->input)) {
                    $return = ($this->callable)($this->key, $this->input[$this->key], $this->controller);
            } else {
                $return = false;
            }
        //if we need to invert
        } else {
            //if several keys
            if (is_array($this->key)) {
                //check that every key is present
                $previousResult = true;
                foreach ($this->key as $key) {
                    //if every key exists -> proceed, else - do nothing
                    if (!array_key_exists($key, $this->input) AND $previousResult === true) {
                        $previousResult = true;
                    } else {
                        $previousResult = false;
                    }
                }
                //if every key exists
                if ($previousResult === true) {
                    $return = ($this->callable)($this->key, null, $this->controller);
                } else $return = false;
                
            //if single value key
            } elseif (!array_key_exists($this->key, $this->input)) {
                    $return = ($this->callable)($this->key, null, $this->controller);
                } else $return = false;
        }
        
        return $return;
    }
}