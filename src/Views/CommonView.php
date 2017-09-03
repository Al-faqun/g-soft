<?php
namespace gsoft\Views;
/**
 * Abstract Class CommonView
 * @package BeeJee\Views
 */
abstract class CommonView
{
    protected $twig;
    
    /**
     * @var array Array of strings.
     */
    protected $requiredFields;
    
    
    /**
     * @var string Full path to the folder with templates
     */
    protected $templatesDir = '';
    
    /**
     * CommonView constructor.
     * @param string $templatesDir
     */
    function __construct($templatesDir)
    {
        $this->templatesDir = $templatesDir;
    }
    
    /**
     * Throws exception if at least one parameter is not defined.
     * Exception text contains name of the missing key.
     * @param $params
     * @throws \Exception
     */
    protected function ensureParams($params)
    {
        if ( $this->checkParams($params) !== true ) {
            $missing = $this->checkParams($params);
            throw new \Exception("Missing key: $missing");
        }
    }
    
    /**
     * Checks whether provided array has all the required fields to run the View.
     * Values of the $params array are not checked.
     * Returns bool any way.
     *
     * @param $params
     * @return bool|string|int TRUE if array has all the required keys; first missing key otherwise.
     */
    protected function checkParams($params)
    {
        $result = false;
        if (is_array($params)) {
            foreach ($this->requiredFields as $required) {
                if ( !array_key_exists($required, $params) ) {
                    $result = $required;
                    break;
                } else $result = true;
            }
        } else $result = false;
        return $result;
    }
    
    /**
     * Outputs page to user.
     * @var $params array Link to the params array, from which are retrieved all the data.
     */
    function render($params)
    {
        //посылаем нужный заголовок
        header('Content-type: text/html; charset=utf-8');
        //отсылаем страницу пользователю
        $contents = $this->output($params);
        echo $contents;
        exit;
    }
    
    
    
    
}