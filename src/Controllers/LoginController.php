<?php
namespace gsoft\Controllers;


use gsoft\Database\PasswordMapper;
use gsoft\Database\UserMapper;
use gsoft\LoginManager;

class LoginController extends PageController
{
    private $root;
    private $pdo;
    private $is_logged;
    function __construct($root, $pdo)
    {
        parent::__construct();
        $this->root = $root;
        $this->pdo = $pdo;
    }
    
    function start()
    {
        $userMapper     = new UserMapper($this->pdo);
        $passwordMapper = new PasswordMapper($this->pdo);
        $loginMan  = new LoginManager($userMapper, $passwordMapper, $this->pdo);
        $userID = $loginMan->checkLoginForm($_POST);
        if ($userID !== false ) {
            $loginMan->persistLogin($userID);
        }
        //в конце всех действий - редирект на главную страницу
        $this->redirect('list.php');
    }
    
    function logout()
    {
        $userMapper     = new UserMapper($this->pdo);
        $passwordMapper = new PasswordMapper($this->pdo);
        $loginMan  = new LoginManager($userMapper, $passwordMapper, $this->pdo);
        $loginMan->logout();
    }
}