<?php
namespace gsoft\Input;



class CargoValidator
{
    /*
     * Whitelist to check against.
     */
    private $statusInputWhiteList = ['on_board', 'finished'];
    private $statusDatabaseWhiteList = ['on board', 'finished'];
    
    /**
     * On success array with int 'id', DateTime 'date_arrival' and string 'status'
     * @param $input
     * @return array|bool
     */
    public function checkCargoIdAndStatusAndDateArrival($input)
    {
        //check id
        $id = $this->checkCargoID($input);
        if ($id !== false) {
            //check date
            $date = $this->checkDateArrival($input);
            if ($date !== false) {
                //check status
                $status = $this->checkStatus($input);
                if ($status !== false) {
                    //if all is right
                    $result['id'] = $id;
                    $result['date_arrival'] = $date;
                    $result['status'] = $status;
                } else $result = false;
                
            } else $result = false;
            
        } else $result = false;
        
        return $result;
    }

    /**
     * @param array $input
     * @return bool|string
     */
    public function checkContainer($input)
    {
        $result = false;
        $key = 'container';
        if (array_key_exists($key, $input)) {
            $result = self::checkString($input[$key], 1, 50, true, true);
        } else $result = false;
        return $result;
    }
    
    public function checkCargoID($input)
    {
        $result = false;
        $key = 'cargo_id';
        if (array_key_exists($key, $input)
            AND is_string($input[$key])
            AND (int)$input[$key] > 0
        ) {
            $result = (int)$input[$key];
        } else {
            //no valid data supplied  from user
            $result = false;
        }
    
        return $result;
    }
    /**
     * @param array $input
     * @return bool|string
     */
    public function checkStatus($input)
    {
        $result = false;
        $fieldname = 'cargo_status';
        if (array_key_exists($fieldname, $input)
            &&
            ( ($key = array_search($input[$fieldname], $this->statusInputWhiteList, false)) !== false )
        ) {
            $result = $this->statusDatabaseWhiteList[$key];
        } else {
            //no valid data supplied  from user
            $result = false;
        }
    
        return $result;
    }
    
    /**
     * @param array $input
     * @return bool|string
     */
    public function checkDateArrival($input)
    {
        if (array_key_exists('date_arrival', $input) AND is_string($input['date_arrival'])) {
            $date = date('Y-m-d', strtotime($input['date_arrival']));
            if ($date !== false) {
                $datetime = \DateTime::createFromFormat('Y-m-d', $date, new \DateTimeZone('Europe/Moscow'));
                $result = $datetime;
            } else {
                $result = false;
            }
        } else $result = false;
        return $result;
    }

    
    /**
     * Checks input to be string, optionally to consist of letters, numbers and '_' sign.
     * @param string $string String to check
     * @param int $minlen Minimal permitted length of string to pass check.
     * @param int $maxlen Maximal permitted length of string to pass check.
     * @param bool $trimWhiteSpaces
     * @param bool $onlyLetters
     * @param bool $startsWithLetter Optional parameter is used,
     * when first meaningful symbol of string (except any white character) must be letter.
     * @return bool|string Returns string if it passes test, else FALSE
     * (be careful, any whitespace character in the begginning and the end are deleted).
     */
    private static function checkString(
        $string,
        $minlen,
        $maxlen,
        $trimWhiteSpaces = false,
        $onlyLetters = false,
        $startsWithLetter = false
    )
    {
        if (is_string($string)) {
            //убираем белые символы, если включена опция
            if ($trimWhiteSpaces === true) {
                $string = trim($string);
            }
            //проверяем входные числа
            if (!is_int($minlen) || !is_int($maxlen)) {
                throw new \UnexpectedValueException('Length of string must be integer');
            }
            //проверяем длину строки
            if ( (mb_strlen($string) >= $minlen
                &&
                mb_strlen($string) <= $maxlen)
            ) {
                $result = $string;
            } else $result = false;
            
            //дополнительные условия
            if ($onlyLetters === true )
            {
                if (!preg_match('/^\w+$/iu', $string) > 0) {
                    $result = false;
                }
            }
            if ($startsWithLetter === true && !self::startsWithLetter($string)) {
                $result = false;
            }
        } else $result = false;
        
        return $result;
    }
    
    /**
     * Checks whether text variable starts with unicode Letter.
     * @param string $var Variable to test.
     * @return bool TRUE if var starts with letter (case insensitive), else FALSE.
     */
    private static function startsWithLetter($var)
    {
        if ( preg_match('/^\p{L}/iu', $var) ) {
            return true;
        } else return false;
    }
}