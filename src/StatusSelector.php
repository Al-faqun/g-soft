<?php
namespace gsoft;



use gsoft\Exceptions\LoaderException;

class StatusSelector
{
	const APP_IN_DEVELOPMENT = 0;
	const APP_IN_PRODUCTION  = 1;
	public static $key = 'appStatus';
	private static $const = array('APP_IN_DEVELOPMENT', 'APP_IN_PRODUCTION');
	private static $texts = array('In development', 'In production');
	
	function __construct()
	{
	}
    
    /**
     * Check if value <from input>contains needed code.
     * @param $value
     * @return bool|int Integer code on success, FALSE if no correct data in input value
     */
	function checkCode($value)
	{
		switch ($value) {
			case '0':
				$result = 0;
				break;
			case '1':
				$result = 1;
				break;
			default:
				$result = false;
				break;
		}
		return $result;
	}
    
    /**
     * Check if value from input contains input code, return code's text representation.
     * @param $value
     * @return bool|string user-friendly text about app's status from value or FALSE.
     */
	function checkText($value)
	{
		$code = $this->checkCode($value);
		if ( $code !== false ) {
			$text = self::codeToText($code);
		} else $text = false;
		return $text;
	}
    
    /**
     * Get app status code  from input array.
     * @param array $input
     * @return bool|int Integer on success of FALSE upon failure
     */
	function getCode($input)
	{
		if (array_key_exists(self::$key, $input)) {
			switch ($input[self::$key]) {
				case '0':
					$result = 0;
					break;
				case '1':
					$result = 1;
					break;
				default:
					$result = false;
					break;
			}
		} else $result = false;
		return $result;
	}
    
    /**
     * Get app status text  from input array.
     * @param array $input
     * @return bool|int String  on success of FALSE upon failure
     */
	function getText($input)
	{
		$code = $this->getCode($input);
		if ( $code !== false ) {
			$text = self::codeToText($code);
		} else $text = false;
		return $text;
	}
    
    /**
     * Parse value from config into integer code for use thoughout app.
     * @param string $statusFromConfig
     * @return int
     * @throws LoaderException
     */
	static function getDefaultCode($statusFromConfig)
	{
		if (isset($statusFromConfig)) {
			switch ($statusFromConfig) {
				case self::$const[0]:
					$status = self::APP_IN_DEVELOPMENT;
					break;
				case self::$const[1]:
					$status = self::APP_IN_PRODUCTION;
					break;
				default:
					throw new LoaderException('App status is not properly loaded');
					break;
			}
		} else throw new LoaderException('App status is not properly loaded');
		return $status;
	}
    
    /**
     * Returns user-friendly text about default status of app <from config>
     * @param $statusFromConfig
     * @return bool|string
     */
	function useDefaultText($statusFromConfig)
	{
		return self::codeToText( self::getDefaultCode($statusFromConfig) );
	}
    
    /**
     * User-friendly text to status code transformation.
     * @param $statusText
     * @return bool|int
     */
	public static function textToCode($statusText)
	{
		switch ($statusText) {
			case self::$texts[0]:
				$result = 0;
				break;
			case self::$texts[1]:
				$result = 1;
				break;
			default:
				$result = false;
				break;
		}
		return $result;
	}
    
    /**
     * Code to user-friendly text about status transformation.
     * @param $statusCode
     * @return bool|string
     */
	public static function codeToText($statusCode)
	{
		switch ($statusCode) {
			case 0:
				$result = self::$texts[0];
				break;
			case 1:
				$result = self::$texts[1];
				break;
			default:
				$result = false;
				break;
		}
		return $result;
	}
}