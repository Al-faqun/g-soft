<?php
use gsoft\Controllers\CargoController;
use gsoft\Database\CargoMapper;
use gsoft\Database\PasswordMapper;
use gsoft\Database\UserMapper;
use gsoft\Exceptions\ErrorHelper;
use gsoft\Exceptions\JsonException;
use gsoft\FileSystem;
use gsoft\Input\SearchQueryValidator;
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
    
    //process post data
    $controller->post(['makeExecutor', 'cargoID'], function ($key, $value, CargoController $c) {
        //remember: we response in JSON
        //check that user has authority
        $userMapper     = new UserMapper(Loader::getPDO());
        $passwordMapper = new PasswordMapper(Loader::getPDO());
        $loginMan  = new LoginManager($userMapper, $passwordMapper, Loader::getPDO());
        // if user is logged and authorized manager
        if ($loginMan->isManager()) {
            $manID = $loginMan->getLoggedID();
            //validate - no need for dedicated class for one value
            //validate that cargoID is valid
            if (isset($value['cargoID']) AND is_string($value['cargoID']) AND (int)$value['cargoID'] > 0) {
                //validated clean data
                $cargoID = (int)$value['cargoID'];
                $cargoMapper = new CargoMapper(Loader::getPDO());
                $success = $cargoMapper->changeManager($cargoID, $manID);
                if ($success) {
                    $result = true;
                } else {
                    //if error with db quering
                    throw new JsonException('Failed with update query to Db');
                }
            } else {
                //problem with data
                throw new JsonException('No valid data supplied with request: ID of cargo.');
            }
        } else {
            //no log in
            throw new JsonException('You are not logged in to perform this task.');
        }
        
        //if no exception - echo result
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        //no further action requiered
        exit();
    });
    
    //if exist certain get parameter
    $controller->get('page', function ($key, $value, CargoController $c) {
        $validator = new SearchQueryValidator();
        //init
        $limit = 0; $offset = 0;
        //this method always fills referenced values with valid result (default in worse case)
        $validator->checkPage($_GET, $offset, $limit);
        //save checked values
        $c->addChecked('limit', $limit);
        $c->addChecked('offset', $offset);
        //display list of awaiting cargo
        $c->awaitingList();
    });
    
    //if certain get parameter does not exist
    $controller->noGet('page', function ($key, $value, CargoController $c) {
        //default values
        $limit = 5; $offset = 0;
        $c->addChecked('limit', $limit);
        $c->addChecked('offset', $offset);
        //display list of awaiting cargo
        $c->awaitingList();
    });
    
    $controller->start();
    
} catch (\Throwable $e) {
    //Catched error -> deal with it
    $errorHelper->dispatch($e, Loader::getStatus());
}