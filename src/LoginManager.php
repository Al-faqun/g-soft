<?php
namespace gsoft;


use gsoft\Database\ClientMapper;
use gsoft\Database\LoginMapper;
use gsoft\Database\ManagerMapper;
use gsoft\Database\PasswordMapper;
use gsoft\Database\UserMapper;
use gsoft\Entities\Client;

class LoginManager
{

    private $pdo;
    private $userMapper;
    private $passwordMapper;
    /**
     * @var bool VERY important variable, which tells outside world about,
     * whether 'user' is logged OR it's some stranger.
     */
    private $islogged = false;
    /**
     * @var int Holder for id of user, if exists.
     */
    private $id = 0;
    
    /**
     * LoginManager constructor.
     * @param UserMapper $userMapper
     * @param PasswordMapper $passwordMapper
     * @param $pdo
     *
     */
    function __construct(UserMapper $userMapper, PasswordMapper $passwordMapper, $pdo)
    {
        $this->userMapper = $userMapper;
        $this->passwordMapper = $passwordMapper;
        $this->pdo = $pdo;
    }
    
    /**
     * Registers client into database.
     * @param $client
     * @param $password
     * @param ClientMapper $clientMapper
     * @return bool|Client
     * @throws \Exception
     */
    function registerClient($client, $password, ClientMapper $clientMapper)
    {
        //we need to save client to Clients, also to Users table, and save password hash to Passwords
        $hash = password_hash($password, PASSWORD_DEFAULT);
        //Clients and Users tables
        $client = $clientMapper->addClient($this->userMapper, $client);
        //Password table
        if ($client !== false) {
            $success = $this->passwordMapper->addHash($client->getId(), $hash);
            
        } else $success = false;
        //if all done successfully
        if ($success) {
            return $client;
        } else {
            throw new \Exception('Cannot register the client: error during registering');
        }
    }
    
    /**
     * Checks depending on provided input, whether user has valid credentials
     * and therefore may be provided with access to system.
     *
     * It also sets internal state of LoginManager to 'logged' or 'not logged'.
     * @param array $input Input array, containing 'password' and 'user data' of current user of site.
     * @return int|bool ID of user if he has valid credentials, or FALSE if not.
     */
    function checkLoginForm($input)
    {
        //проверяем наличие данных для логина
        if  (array_key_exists('login_form_sent', $input)
            AND
             array_key_exists('navbar_username', $input)
            AND
             array_key_exists('navbar_pwd', $input)
        ) {
            //фильтруем их
            $password = (string)$input['navbar_pwd'];
            $username = (string)$input['navbar_username'];
            $id = $this->userMapper->getIdByName($username);
            if ($id !== false) {
                $hash = $this->passwordMapper->getHash($id);
                //проверяем полученный хеш (если ошибка, вместо него false)
                if ($hash !== false) {
                    //проверка совпадения
                    if (password_verify($password, $hash)) {
                        //если пользователь дал корректные данные, возвращаем его ID
                        $result = $id;
                    } else {
                        $result = false;
                    }
                    //проверка, не обновился ли стандартный способ хэширования в php
                    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $this->passwordMapper->updateHash($id, $hash);
                    }
                } else {
                    //если хеш не найден
                    $result = false;
                }
            } else {
                //если айди не найден
                $result = false;
            }
        } else {
            //если нет нужного ключа в инпуте
            $result = false;
        }
        
        return $result;
        
    }
    
    /**
     * @param $userid
     */
    function persistLogin($userid)
    {
        $loginMapper = new LoginMapper($this->pdo);
        //случайный токен для хранения в куках
        $token = self::genRandString(24);
        //его хеш для хранения в бд
        $tokenHash = hash('sha256', $token);
        //идентификатором пользователя будет ID записи логина в бд
        $id = $loginMapper->addLogin($tokenHash, $userid);
        //если запись в бд прошла успешно, записываем данные о логине в куки
        if ($id != false) {
            setcookie('login_id', $id, time()+60*60*24, null, null, null, true);
            setcookie('token', $token,   time()+60*60*24, null, null, null, true);
        }
    }
    
    function logout()
    {
        setcookie('login_id', 0, time()-60*60*24, null, null, null, true);
        setcookie('token', 0,   time()-60*60*24, null, null, null, true);
    }
    
    /**
     * By default it returns FALSE.
     * @return bool TRUE if user is valid and logged, otherwise FALSE.
     */
    function isLogged()
    {
        //проверяем наличие в куки данных
        if  (array_key_exists('login_id', $_COOKIE) AND is_string($_COOKIE['login_id']) //проверка на массив
            AND
            array_key_exists('token', $_COOKIE) AND is_string($_COOKIE['token'])
        ) {
            $loginID = (int)$_COOKIE['login_id'];
            $token = (string)$_COOKIE['token'];
            $loginMapper = new LoginMapper($this->pdo);
            //проверяем наличие id (серии) токена в бд
            $hash = $loginMapper->getHash($loginID);
            //делаем что-то только если хэш  найден в базе
            if ($hash != false) {
                //если хэши из бд и куки совпали
                if (hash_equals($hash, hash('sha256', $token))) {
                    //пользователь обладает нужными данными - он залогинен
                    $this->islogged = true;
                } else {
                    //пользователь дал нужный айди, но провалил проверку пароля => воровство
                    $this->islogged = false;
                }
                
            } else $this->islogged = false;
            
        } else $this->islogged = false;
        
        return $this->islogged;
    }
    
    /**
     * Returns if user is logged in and it's group is 'client', else FALSE.
     * @return bool
     */
    function isClient()
    {
        $result = false;
        if ($this->isLogged()) {
            $usergroup = $this->userMapper->getUser($this->getLoggedID())->getUsergroup();
            if ($usergroup === 'client') {
                $result = true;
            } else {
                $result = false;
            }
        }
        return $result;
    }
    
    /**
     * Returns if user is logged in and it's group is 'manager', else FALSE.
     * @return bool
     */
    function isManager()
    {
        $result = false;
        if ($this->isLogged()) {
            $usergroup = $this->userMapper->getUser($this->getLoggedID())->getUsergroup();
            if ($usergroup === 'manager') {
                $result = true;
            } else {
                $result = false;
            }
        }
        return $result;
    }
    
    /**
     * @return int ID of user, if it's credentials were checked, otherwise false.
     */
    function getLoggedID()
    {
        $userid = false;
        if ( $this->isLogged() ) {
            //если залогинены, то в куки есть айди
            $loginID = $_COOKIE['login_id'];
            //вызываем мапперы для доступа к бд
            $loginMapper = new LoginMapper($this->pdo);
            //получем из записи о логине айди пользователя
            $userid = $loginMapper->getUserID($loginID);
        }
        return $userid;
    }
    
    /**
     * False, if user is not logged in.
     * @return bool|string
     */
    function getLoggedName()
    {
        $username = false;
        if ($this->isClient()) {
            //если залогинены, то в куки есть айди
            $loginID = $_COOKIE['login_id'];
            //вызываем мапперы для доступа к бд
            $loginMapper = new LoginMapper($this->pdo);
            $clientMapper = new ClientMapper($this->pdo);
            //получем из записи о логине айди пользователя
            $userid = $loginMapper->getUserID($loginID);
            //получем его имя
            $username = $clientMapper->getClient($userid)->getCompanyName();
        } elseif ($this->isManager()) {
            //если залогинены, то в куки есть айди
            $loginID = $_COOKIE['login_id'];
            //вызываем мапперы для доступа к бд
            $loginMapper = new LoginMapper($this->pdo);
            $managerMapper = new ManagerMapper($this->pdo);
            //получем из записи о логине айди пользователя
            $userid = $loginMapper->getUserID($loginID);
            //получем его имя
            $username = $managerMapper->getManager($userid)->getSurname()
                . ' '
                . $managerMapper->getManager($userid)->getName();
        }
        return $username;
    }
    
    /**
     * Generates !cryptographically secure! string of given length.
     * @param int $length Length of desired random string.
     * @param string $chars Only these characters may be included into string.
     * @return string
     * @throws \Exception
     */
    private static function genRandString($length, $chars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/')
    {
        if (!is_string($chars) || strlen($chars) == 0) {
            throw new \Exception('Parameter is not string or is empty');
        }
        
        $str = '';
        $keysize = strlen($chars) -1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $chars[random_int(0, $keysize)];
        }
        return $str;
    }
}