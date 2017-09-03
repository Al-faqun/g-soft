<?php

//главная папка проекта
use gsoft\Controllers\LoginController;
use gsoft\ErrorHelper;
use gsoft\FileSystem;
use gsoft\Loader;

$root = dirname(__FILE__, 2);

require_once($root . '/bootstrap.php');
//error handler
$errorHelper = new ErrorHelper(FileSystem::append([$root, 'templates']));
//init base config and error handling
Loader::setRoot($root);
$errorHelper->setLogFilePath(FileSystem::append([$public, 'errors.log']));
$errorHelper->registerFallbacks(Loader::getStatus());

try {
    $controller = new LoginController($root, Loader::getPDO());
    if (array_key_exists('logout', $_POST)) {
        $controller->logout();
    }
    $controller->redirect('list.php');;
    
} catch (\Throwable $e) {
    //if catched error
    $errorHelper->dispatch($e);
}