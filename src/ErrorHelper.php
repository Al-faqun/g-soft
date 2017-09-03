<?php

namespace gsoft;


class ErrorHelper {
    /**
     * @var string Holds path to folder with templates of pages
     */
    private $templateDir = '';
    private $logFile = '';
    private $twig;
    private $fallBackStatus;
    const APP_IN_DEVELOPMENT = 0;
    const APP_IN_PRODUCTION = 0;
    
    /**
     * ErrorHelper constructor.
     * @param $templatesDir
     * @throws \Exception
     */
    public function __construct($templatesDir) {
        if (is_dir($templatesDir)) {
            $this->templateDir = $templatesDir;
        } else throw new \Exception("Error Helper failed to be created");
        
        $loader = new \Twig_Loader_Filesystem(FileSystem::append([$templatesDir]));
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => FileSystem::append([$templatesDir, 'cache']),
            'auto_reload' => true,
            'autoescape' => 'html'
        ));
    }
    
    function setLogFilePath($logFilePath)
    {
        $this->logFile = $logFilePath;
    }
    
    function dispatch(\Throwable $e, $appStatus = self::APP_IN_PRODUCTION, $userID = 0)
    {
        switch ($appStatus) {
            case self::APP_IN_DEVELOPMENT:
                $this->renderThrowableAndExit($e, '/');
                break;
            case self::APP_IN_PRODUCTION:
                $userMes = 'Encountered error, logs are sent to developer. Please, try again later!';
                //форматируем текст для записи в лог-файл
                $text = self::excepTextRecursive($e);
                //добавляем дату в начало
                array_unshift($text, date('d-M-Y H:i:s') . ' ');
                $text[] = 'UserID = ' . $userID;
                $this->addToLog($text, $this->logFile);
                $this->renderErrorPageAndExit($userMes, '/');
                break;
        }
    }
    
    /**
     * Recursively walks provided exception and it's parents,
     * creates text, holding info about all of them,
     * renders error page with that info and exists script.
     *
     * @param \Throwable $e
     * @param string $whereToRedirect Relative URLpath to desired location,
     * to which user would be relocated after viewing info about exceptions.
     */
    function renderThrowableAndExit(\Throwable $e, $whereToRedirect)
    {
        //get text of exception and it's previous exceptions
        $output = self::excepTextRecursive($e);
        
        //show info to the user and end main script
        $this->renderErrorPageAndExit($output, $whereToRedirect);
    }
    
    /**
     * Shows to user page with custom message about error.
     * @param string|array $errorMes
     * @param string $whereToRedirect Relative $whereToRedirect URLpath to desired location,
     * to which user would be relocated after viewing info about exceptions.
     */
    function renderErrorPageAndExit($errorMes, $whereToRedirect)
    {
        header('Content-type: text/html; charset=utf-8');
        $error = $errorMes;
        $url = $whereToRedirect;
        $text = 'Вернуться';
        $template = $this->twig->load(FileSystem::append(['Errors', 'error.html.twig']));
        echo $template->render(array(
                'error' => $error,
                'url' => $url,
                'text' => $text)
        );
        exit();
    }
    
    /**
     * Shows to user page with detailed desctiption of php error.
     *
     * @param array $error PHP error array(old error)
     * @param string $whereToRedirect Relative $whereToRedirect URLpath to desired location,
     * to which user would be relocated after viewing info about exceptions.
     */
    function renderFatalErrorAndExit($error, $whereToRedirect)
    {
        $text = array("Произошла фатальная ошибка, выполнение приложения прекращается: ");
        $text = array_merge($text, self::errorToArray($error));
        $this->renderErrorPageAndExit($text, $whereToRedirect);
    }
    
    /**
     * @param array $message Array of strings, each string would be added to log as new line.
     * @param string $logpath Full path to log file.
     * @throws \Exception
     */
    function addToLog($message, $logpath)
    {
        if (is_writeable($logpath)) {
            $text = self::arrayToString($message, PHP_EOL, PHP_EOL);
            error_log($text, 3, $logpath);
        } else throw new \Exception('Log file location is not writeable');
    }
    
    function registerFallbacks($appStatus = self::APP_IN_PRODUCTION)
    {
        $this->fallBackStatus = $appStatus;
        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this,'errorHandler'), E_ALL);
        register_shutdown_function(array($this, 'shutDown'));
    }
    
    public function exceptionHandler(\Throwable $e)
    {
        $this->dispatch($e, $this->fallBackStatus);
    }
    
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $text = array();
        $text[] = "Возникла ошибка, выполнение приложения могло бы быть продолжено:";
        $text[] = 'текст: ';
        $text[] = ErrorHelper::splitErrMes($errstr);
        $text[] = 'файл: ' . $errfile . ',';
        $text[] = 'строка:' . $errline . '.';
        switch ($this->fallBackStatus) {
            case self::APP_IN_DEVELOPMENT:
                //отображаем ошибку пользователю
                $this->renderErrorPageAndExit($text, '');
                break;
            case self::APP_IN_PRODUCTION:
                //пишем ошибку в лог
                $this->addToLog($text, $this->logFile);
        }
    }
    
    public function shutDown()
    {
        $error = error_get_last();
        if (isset($error['type']) && $error['type'] === E_ERROR) {
            switch ($this->fallBackStatus) {
                case self::APP_IN_DEVELOPMENT:
                    //отображаем ошибку пользователю
                    $this->renderFatalErrorAndExit($error, '');
                    break;
                case self::APP_IN_PRODUCTION:
                    //пишем ошибку в лог
                    $text = array("Возникла ошибка, выполнение приложения прекращено:");
                    $text = array_merge($text, self::errorToArray($error));
                    $this->addToLog($text, $this->logFile);
            }
        }
    }
    
    public static function errorToArray($error)
    {
        $text = array();
        $text[] = 'текст: ';
        $text[] = self::splitErrMes($error['message']);
        $text[] = 'файл: ' . $error['file'] . ',';
        $text[] = 'строка:' . $error['line'] . '.';
        
        return $text;
    }
    
    /**
     * Parses Throwable (and all of it's previous if exist) into array of strings, each presenting one line.
     * @param \Throwable $e
     * @return array array of strings
     */
    public static function excepTextRecursive(\Throwable $e)
    {
        //recursively get info about exception and it's possible parents
        $i = 1;
        $previous = $e->getPrevious();
        if ($previous !== null)
        {
            $i++;
            $previous = $previous->getPrevious();
        }
        
        $output = array();
        do {
            $text = array();
            $text[] = "Возникло исключение #{$i} класса " . get_class($e) . ':';
            $text[] = 'текст: ' . "'" . $e->getMessage() . "'" . ',';
            $text[] = 'файл: ' . $e->getFile() . ',';
            $text[] = 'строка:' . $e->getLine() . '.';
            $text[] = 'traceback: ';
            $trace = self::splitErrMes($e->getTraceAsString());
            $text = array_merge($text, $trace);
            $output[] = $text;
            $i--;
            $previous = $e->getPrevious();
        }
        while ($e = $previous);
        return $output;
    }
    
    /**
     * Splits error message into array of strings by delimiter (# as default).
     * Useful to create pretty text for developer or to log.
     *
     * @param $message
     * @return array
     */
    public static function splitErrMes($message)
    {
        $lines = [];
        $array = explode('#', $message);
        foreach ($array as $line) {
            if (!empty($line)) {
                $line = trim(preg_replace('/\s+/', ' ', $line));
                $lines[] = '#' . $line;
            }
        }
        return $lines;
    }
    
    /**
     * Converts array into string using new-line separators like '<br>' or PHP_EOL.
     * Optionally you can use 'block separator', which is added after the string to indicate additional empty line.
     *
     * @param array $array Array of strings to convert.
     * @param string $separator Indicates new line.
     * @param string $blockSeparator Adds to the end of string.
     * @return string
     */
    public static function arrayToString($array, $separator, $blockSeparator = '')
    {
        $string = '';
        foreach ($array as $line) {
            if (is_array($line)) {
                $string .= self::arrayToString($line, $separator);
            } else {
                $string .= $line . $separator;
            }
        }
        if (!empty($blockSeparator)) {
            $string .= $blockSeparator;
        }
        return $string;
    }
}