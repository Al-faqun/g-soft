<?php
use gsoft\Controllers\CargoController;
use gsoft\Exceptions\ErrorHelper;
use gsoft\FileSystem;
use gsoft\Input\SearchQueryValidator;
use gsoft\Loader;

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
    });
    
    //if certain get parameter does not exist
    $controller->noGet('page', function ($key, $value, CargoController $c) {
        //default values
        $limit = 5; $offset = 0;
        $c->addChecked('limit', $limit);
        $c->addChecked('offset', $offset);
    });
    //process above lambdas
    $controller->start();
    //request page: list of cargos
    $controller->list();
    
} catch (\Throwable $e) {
    //Catched error -> deal with it
    $errorHelper->dispatch($e, Loader::getStatus());
}