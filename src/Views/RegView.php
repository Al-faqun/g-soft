<?php
namespace gsoft\Views;


use gsoft\FileSystem;

class RegView  extends CommonView
{
    function __construct($templatesDir)
    {
        parent::__construct($templatesDir);
        $loader = new \Twig_Loader_Filesystem([FileSystem::append([$templatesDir, 'Registration']), $templatesDir]);
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => FileSystem::append([$templatesDir, 'cache']),
            'auto_reload' => true,
            'autoescape' => 'html',
            'strict_variables' => true
        ));
    }
    
    /**
     * Loads all values and preferences for a template, then loads the template into string.
     * @var $params array Link to the params array, from which are retrieved all the data.
     * @return string html page
     * @throws \Exception
     */
    public function output($params)
    {
        ob_start();
        $messages = $params['messages'];
        $errors   = $params['errors'];
        $dataBack = $params['databack'];
        //параметры для навбара-логина
        $authorized = $params['authorized'];
        $usernameDisplayed = $params['username'];
        $usergroup = $params['usergroup'];
        
        //загружаем шаблон, который использует вышеописанные переменные
        $template = $this->twig->load('reg.html.twig');
        echo $template->render(array(
            'errors'   => $errors,
            'messages' => $messages,
            'databack' => $dataBack,
            'authorized' => $authorized,
            'username'   => $usernameDisplayed,
            'usergroup'  => $usergroup
        ));
        return ob_get_clean();
    }
}