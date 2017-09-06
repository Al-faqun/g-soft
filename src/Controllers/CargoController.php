<?php
namespace gsoft\Controllers;

use gsoft\Database\CargoMapper;
use gsoft\Database\PasswordMapper;
use gsoft\Database\UserMapper;
use gsoft\FileSystem;
use gsoft\LoginManager;
use gsoft\Pager;
use gsoft\Views\ListView;

class CargoController extends PageController
{
    private $root;
    private $pdo;
    
    /**
     * CargoController constructor.
     * @param string $root Root dir of project.
     * @param \PDO $pdo
     */
    public function __construct($root, $pdo)
    {
        parent::__construct();
        $this->root = $root;
        $this->pdo = $pdo;
    }
    
    /**
     * Process prepended actions with input variables (get, post, cookie)
     */
    public function start()
    {
        $this->execute();
    }
    
    /**
     * Display page with list of cargo
     */
    public function list()
    {
        //mappers that fetches objects from DB
        $cargoMapper    = new CargoMapper($this->pdo);
        $userMapper     = new UserMapper($this->pdo);
        $passwordMapper = new PasswordMapper($this->pdo);
        $view = new ListView( FileSystem::append([$this->root, 'templates']) );
        
        //manager of logins and registration
        $loginMan  = new LoginManager($userMapper, $passwordMapper, $this->pdo);
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
        
        //values for db search query, from previously executed requests
        $limit = $this->getChecked('limit');
        $offset = $this->getChecked('offset');
        
        //check whether user is authorized to get anything from this page
        if ($loginMan->isClient()) {
            //save fact that user is authorized by loginManager
            $authorized = 'client';
            //client watches list page, he must see list of his own cargo
            $clientID = $loginMan->getLoggedID();
            $cargo = $cargoMapper->getForClient($clientID, $limit, $offset);
            if ($cargo === false) {
                $this->addMessage('Не найдено ни одного груза для вас');
            }
            //total number of how many entries were found in last DB query
            $entriesCount = $cargoMapper->getEntriesCount();
            //query parts of URL for pagination
            $queries = Pager::getQueries($_GET, $entriesCount);
    
            $view->render([
                'authorized' => $authorized,
                'username'   => $usernameDisplayed,
                'usergroup' => $usergroup,
                'cargo' => $cargo,
                'queries' => $queries,
                'messages' => $this->getMessages()
            ]);
            
        } elseif ($loginMan->isManager()){
            $authorized = 'manager';
            //manager watches list page, he must see list of cargo under his supervise
            $managerID = $loginMan->getLoggedID();
            $cargo = $cargoMapper->getForManager($managerID, $limit, $offset);
            //total number of how many entries were found in last DB query
            $entriesCount = $cargoMapper->getEntriesCount();
            //query parts of URL for pagination
            $queries = Pager::getQueries($_GET, $entriesCount);
            
            $view->render([
                'authorized' => $authorized,
                'username'   => $usernameDisplayed,
                'usergroup' => $usergroup,
                'cargo' => $cargo,
                'queries' => $queries,
                'messages' => $this->getMessages()
            ]);
            
        } else {
            //NON-authed user must see only message
            $message = 'Судя по всему, вы не залогинены на нашем сайте. '
            . 'Только зарегистрированные пользователи с правами могут зайти на эту страницу!';

            $this->addMessage($message);
            $view->render([
                'authorized' => false,
                'username'   => $usernameDisplayed,
                'usergroup'  => '',
                'messages' => $this->getMessages()
            ]);
        }
    }
    
    public function add()
    {
    
    }
    
    /**
     *
     */
    public function edit()
    {
    
    }
    
    
}