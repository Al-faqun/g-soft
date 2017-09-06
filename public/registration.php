<?php

use gsoft\Controllers\RegController;
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
    $controller = new RegController($root, Loader::getPDO());
    $controller->get('registered', function ($key, $value, RegController $c) {
        $c->addMessage('Вы успешно зарегистрированы! Теперь можете войти.');
    });

    //запуск нужной страницы
    $controller->start();
    
} catch (\Throwable $e) {
    //Catched error -> deal with it
    $errorHelper->dispatch($e, Loader::getStatus());
}