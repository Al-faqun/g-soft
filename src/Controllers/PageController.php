<?php
namespace gsoft\Controllers;



class PageController
{
    //checked (!) variables from input
    protected $checked = array();
    public $requests = array();
    protected $messages = array();
    
    protected function __construct()
    {
    }
    
    function get($key, callable $call)
    {
        $this->requests[] = new Request($_GET, $key, $call, $this);
    }
    
    function post($key, callable $call)
    {
        $this->requests[] = new Request($_POST, $key, $call, $this);
    }
    
    function cookie($key, callable $call)
    {
        $this->requests[] = new Request($_COOKIE, $key, $call, $this);
    }
    
    function noGet($key, callable $call)
    {
        $this->requests[] = new Request($_GET, $key, $call, $this, true);
    }
    
    function noPost($key, callable $call)
    {
        $this->requests[] = new Request($_POST, $key, $call, $this, true);
    }
    
    function noCookie($key, callable $call)
    {
        $this->requests[] = new Request($_COOKIE, $key, $call, $this, true);
    }
    
    /**
     * It works even if you modify requests field inside some request callable.
     */
    function execute()
    {
        if (!empty($this->requests) AND is_array($this->requests))
            foreach ($this->requests as &$request) {
                $request->call();
            }
    }
    
    function redirect($address)
    {
        header('Location: ' . $address, true, 303);
        exit();
    }
    
    public function addMessage(String $message)
    {
        $this->messages[] = $message;
    }
    
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
     * Checked are safe values, saved for controller use.
     * @param $key
     * @param $value
     * @throws \Exception
     */
    public function addChecked($key, $value)
    {
        if (!is_string($key) AND !is_integer($key)) {
            throw new \Exception('Attempt to add checked value with incorrect key!');
        }
        $this->checked[$key] = $value;
    }
    
    /**
     * Checked are safe values, saved for controller use.
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function getChecked($key)
    {
        if (isset($this->checked[$key])) {
            return $this->checked[$key];
        } else {
            throw new \Exception('Attempt to fetch checked value from incorrect key!');
        }
    }
}
