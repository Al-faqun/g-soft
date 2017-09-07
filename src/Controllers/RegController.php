<?php

namespace gsoft\Controllers;


use gsoft\Database\ClientMapper;
use gsoft\Database\PasswordMapper;
use gsoft\Database\UserMapper;
use gsoft\Entities\Client;
use gsoft\FileSystem;
use gsoft\Input\RegFormValidator;
use gsoft\LoginManager;
use gsoft\Views\RegView;

class RegController extends PageController
{
    private $root;
    private $pdo;
    private $errors;
    public $userID = 0;
    
    function __construct($root, $pdo)
    {
        parent::__construct();
        $this->root = $root;
        $this->pdo = $pdo;
    }
    
    function start()
    {
        $this->execute();
        $this->regPage($this->root, $this->pdo);
    }
    
    protected function regPage($root, \PDO $pdo)
    {
        $userMapper     = new UserMapper($pdo);
        $clientMapper   = new ClientMapper($pdo);
        $passwordMapper = new PasswordMapper($pdo);
        $validator = new RegFormValidator($userMapper, $clientMapper);
        
        $loginMan  = new LoginManager($userMapper, $passwordMapper, $pdo);
        //проверяем логин пользователя (если есть)
        $authorized = $loginMan->isLogged();
        //если залогинены - запоминаем имя для отображения на странице
        if ($authorized === true) {
            if ($loginMan->isClient()) {
                //save info for template
                $usergroup = 'client';
            } elseif ($loginMan->isManager()) {
                $usergroup = 'manager';
            }
            $usernameDisplayed = $loginMan->getLoggedName();
        } else {
            $usernameDisplayed = '';
            $usergroup = '';
        }
        
        $dataBack  = array();  // значения неправильных входных данных
        //проверяем, были ли посланы данные формы
        if ($validator->dataSent($_POST)) {
            //проверяем, правильно ли они заполнены
            $data = $validator->checkInput($_POST, $this->errors);
            if ($data !== false) {
                $client = Client::fromArray($data);
                $user = $loginMan->registerClient($client, $data['password'], $clientMapper);
                $this->redirect('registration.php?registered');
            } else {
                $dataBack = RegFormValidator::pureInputFrom($_POST);
            }
        }
        $view = new RegView(FileSystem::append([$root, '/templates']));
        $view->render([
            'errors'     => $this->errors,
            'messages'   => $this->messages,
            'databack'   => $dataBack,
            'authorized' => $authorized,
            'username'   => $usernameDisplayed,
            'usergroup'  => $usergroup
        ]);
    }
}