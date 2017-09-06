<?php
namespace gsoft\Input;

use gsoft\Database\ClientMapper;
use gsoft\Database\UserMapper;

class RegFormValidator
{
    private $userMapper;
    private $clientMapper;
    
    public function __construct(UserMapper $userMapper, ClientMapper $clientMapper)
    {
        $this->userMapper = $userMapper;
        $this->clientMapper = $clientMapper;
    }
    
    /**
     * Отослана ли форма
     * @param $input
     * @return bool
     */
    public function dataSent($input)
    {
        $result = false;
        $fieldname = 'reg_form_sent';
        //если есть нужный hidden input
        
        if (array_key_exists($fieldname, $input)
             &&
            ($input[$fieldname] == 1)
        ) {
            $result = true;
        } else $result = false;
        
        return $result;
    }
    
    /**
     * @param $input
     * @return array|bool
     */
    public function checkInput($input, &$errors)
    {
        $result = true;
        $password    = $this->checkPassword($input);
        $userName    = $this->checkUsername($input);
        $existsName  = $this->doesExistName($userName);
        $email       = $this->checkEmail($input);
        $existsEmail = $this->doesExistEmail($email);
        $companyName = $this->checkCompanyName($input);
        $inn         = $this->checkInn($input);
        $address     = $this->checkAddress($input);
        $tel         = $this->checkTel($input);
        if ($password === false) {
            $errors[] = 'Введен некорректный пароль. Исправьте!';
            $result = false;
        }
        
        if ($userName === false) {
            $errors[] = 'Введенный логин недопустим. Исправьте!';
            $result = false;
        }
        if ($existsName === true) {
            $errors[] = 'Пользователь с таким именем уже существует.';
            $result = false;
        }
        
        if ($email === false) {
            $errors[] = 'Введенный адрес электронной почты недопустим. Исправьте!';
            $result = false;
        }
        if ($existsEmail === true) {
            $errors[] = 'Пользователь с таким email\'ом уже существует.';
            $result = false;
        }
        
        if ($companyName === false) {
            $errors[] = 'Введенное название компании недопустимо. Исправьте!';
            $result = false;
        }
        
        if ($inn === false) {
            $errors[] = 'Введенный ИНН недопустим. Исправьте!';
            $result = false;
        }
        
        if ($address === false) {
            $errors[] = 'Введенный адрес недопустим. Исправьте!';
            $result = false;
        }
        
        if ($tel === false) {
            $errors[] = 'Введенный телефон недопустим. Исправьте!';
            $result = false;
        }
        
        if ($result !== false) {
            $result = [
                'username'     => $userName,
                'password'     => $password,
                'email'        => $email,
                'company_name' => $companyName,
                'inn'          => $inn,
                'address'      => $address,
                'tel'          => $tel
            ];
        }
        
        return $result;
    }
    
    private function checkPassword($input)
    {
        $result = false;
        $fieldname = 'password';
        if (array_key_exists($fieldname, $input)
        ) {
            $result = self::checkString($input[$fieldname], 3, 300);
        } else $result = false;
    
        return $result;
    }
    
    private function checkUsername($input)
    {
        $result = false;
        $fieldname = 'username';
        if (array_key_exists($fieldname, $input)
        ) {
            $result = self::checkString($input[$fieldname], 3, 100, true, true, true);
        } else $result = false;
    
        return $result;
    }
    
    private function checkEmail($input)
    {
        $result = false;
        $fieldname = 'email';
        if (array_key_exists($fieldname, $input)
        ) {
            $email = trim($input[$fieldname]);
            if ( filter_var($email, FILTER_VALIDATE_EMAIL) ) {
                $result = $email;
            } else {
                $result = false;
            }
        } else $result = false;
      
        return $result;
    }
    
    private function checkCompanyName($input)
    {
        $result = false;
        $fieldname = 'company_name';
        if (array_key_exists($fieldname, $input)
        ) {
            $result = self::checkString($input[$fieldname], 3, 255, true);
        } else $result = false;
    
        return $result;
    }
    
    private function checkInn($input)
    {
        $result = false;
        $fieldname = 'inn';
        if (array_key_exists($fieldname, $input)
        ) {
            if (
                self::checkString($input[$fieldname], 5, 20, true, true)
                    AND
                self::onlyNumbers($input[$fieldname]) ){
                $result = self::checkString($input[$fieldname], 5, 20, true, true);
            } else {
                $result = false;
            }
            
        } else $result = false;
        
        return $result;
    }
    
    private function checkAddress($input)
    {
        $result = false;
        $fieldname = 'address';
        if (array_key_exists($fieldname, $input)
        ) {
            $result = self::checkString($input[$fieldname], 3, 2000, true);
        } else $result = false;
        
        return $result;
    }
    
    private function checkTel($input)
    {
        $result = false;
        $fieldname = 'tel';
        if (array_key_exists($fieldname, $input)
        ) {
            $result = self::checkString($input[$fieldname], 3, 20, true);
            if ( !(preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/i', $result) > 0) ) {
                $result = false;
            }
        } else $result = false;
        
        return $result;
    }
    
    /**
     * @param string $userName
     * @return bool
     */
    private function doesExistName($userName)
    {
        return $this->userMapper->doesExist($userName);
    }
    
    /**
     * @param $email
     * @return bool
     */
    private function doesExistEmail($email)
    {
        return $this->clientMapper->doesExistEmail($email);
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
     * Copies pure specific variables from input for later use as databack to user
     * @param $input
     * @return array
     */
    public static function pureInputFrom($input)
    {
        $dataBack = [
            'username'     => $input['username'],
            'password'     => $input['password'],
            'email'        => $input['email'],
            'company_name' => $input['company_name'],
            'inn'          => $input['inn'],
            'address'      => $input['address'],
            'tel'          => $input['tel']
        ];
        return $dataBack;
    }
    
    private static function onlyNumbers($string)
    {
        if (preg_match('/^\d+$/iu', $string) > 0) {
            $result = true;
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