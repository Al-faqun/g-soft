<?php

use gsoft\Controllers\CargoController;
use gsoft\Database\CargoMapper;
use gsoft\Database\PasswordMapper;
use gsoft\Database\UserMapper;
use gsoft\Exceptions\ErrorHelper;
use gsoft\FileSystem;
use gsoft\Input\CargoValidator;
use gsoft\Loader;
use gsoft\LoginManager;

$root = dirname(__FILE__, 2);
$public = __DIR__;
//bootstrap file with autoloading, etc
require_once ($root . '/bootstrap.php');
//error handler
$errorHelper = new ErrorHelper(FileSystem::append([$root, 'templates']));
//init base config and error handling
Loader::setRoot($root);
$errorHelper->setLogFilePath(FileSystem::append([$public, 'errors.log']));
$errorHelper->registerFallbacks(Loader::getStatus());

try {
    $controller = new CargoController($root, Loader::getPDO());
    //if get parameter exist
    $controller->post('container', function ($key, $value, CargoController $c) {
        //check whether user, attempting access to script, is valid logged client
        $userMapper     = new UserMapper(Loader::getPDO());
        $passwordMapper = new PasswordMapper(Loader::getPDO());
        $loginMan  = new LoginManager($userMapper, $passwordMapper, Loader::getPDO());
        if ($loginMan->isClient()) {
            // if client - permit other operations
            $clientID = $loginMan->getLoggedID();
            $mapper = new CargoMapper(Loader::getPDO());
            //validate user input
            $validator = new CargoValidator();
            //false or string
            $containerName = $validator->checkContainer($_POST);
            if ($containerName) {
                $added = $mapper->newCargo($containerName, $clientID);
                if ($added !== false) {
                    $result = $added->getId();
                } else {
                    //this will result in json input about error
                    throw new \gsoft\Exceptions\JsonException('Data is valid; cannot add a to database.');
                }
            } else {
                //this will result in json input about error
                throw new \gsoft\Exceptions\JsonException('Data is not valid.');
            }
        } else {
            throw new \gsoft\Exceptions\JsonException('You are not logged in as client.');
        }
        
        //whatever result is - return in json
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    });
    //if get parameter does not exit
    $controller->noPost('container', function ($key, $value, CargoController $c) {
        //if scrupt accessed, but no valid data appended - return false for those who wait json
        echo json_encode(false);
    });
    
    //start lambdas
    $controller->start();
} catch (\Throwable $e) {
    //Catched error -> deal with it
    $errorHelper->dispatch($e, Loader::getStatus());
}