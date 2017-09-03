<?php
namespace gsoft\Views;


use gsoft\Cargo;
use gsoft\FileSystem;

class ListView extends CommonView
{
    /**
     * StudentListView constructor.
     * @param string $templatesDir
     */
    function __construct($templatesDir)
    {
        parent::__construct($templatesDir);
        $loader = new \Twig_Loader_Filesystem([FileSystem::append([$templatesDir, 'List']), $templatesDir]);
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
        $authorized = $params['authorized'];
        $usernameDisplayed = $params['username'];
        $messages = $params['messages'];
        $cargo   = $params['cargo'] ?? null;
        $queries = $params['queries'] ?? null;
        if ($authorized === 'client') {
            $caption = 'Ваши грузы';
        } elseif ($authorized === 'manager') {
            $caption = 'Грузы ваших клиентов';
        } else {
            $caption = '';
        }
       
        //загружаем шаблон, который использует вышеописанные переменные
        $template = $this->twig->load('list.html.twig');
        echo $template->render(array(
            'cargo'    => $cargo,
            'messages' => $messages,
            'queries'  => $queries,
            'authorized' => $authorized,
            'username'   => $usernameDisplayed,
            'caption' => $caption
        ));
        return ob_get_clean();
    }
    
 
}