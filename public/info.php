<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 03.09.2017
 * Time: 0:12
 */

use gsoft\Controllers\InfoController;
use gsoft\Exceptions\ErrorHelper;
use gsoft\FileSystem;
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
    $controller = new InfoController($root, Loader::getPDO());
    
    //processing get paremeters
    //if we define new input request callable inside callable, it will be executed at the end of prev requests
    //if user asks about client with id
    $controller->get('client', function ($key, $value, InfoController $c) {
        $c->get('id', function ($key, $value, InfoController $c) {
            $id = (int)$value;
            //controller would take care about request and echo everything needed
            //false or client
           $c->aboutClient($id);
        });
    });
    //if user asks about manager with id
    $controller->get('manager', function ($key, $value, InfoController $c) {
        $c->get('id', function ($key, $value, InfoController $c) {
            $id = (int)$value;
            //controller would take care about request and echo everything needed
            //false or manager
            $c->aboutManager($id);
        });
    });
    $controller->get('xls', function ($key, $value, InfoController $c) {
        //if user is logged, prompt to save its cargo to .xls
        $c->loggedCargoToExcel();
    });
    $controller->get('xlsToMail', function ($key, $value, InfoController $c) {
        //if user is logged, prompt to save its cargo to .xls
        $c->loggedCargoMail('test@localhost.com');
    });
    $controller->start();
    
} catch (\Throwable $e) {
    //Catched error -> deal with it
    $errorHelper->dispatch($e, Loader::getStatus());
}