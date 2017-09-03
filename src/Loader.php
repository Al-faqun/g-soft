<?php
namespace gsoft;



use gsoft\Exceptions\LoaderException;

class Loader
{
	//единственный экземпляр класса
	private static $root;
	private static $status;
	private static $config;
	private static $dsn;
	private static $pdo;
	
	/**
	 * Unreachable constructor.
	 */
	private function __construct()
	{
	}
    
    /**
     * Set root directory of project.
     * @param $root
     */
	public static function setRoot($root)
	{
		self::$root = $root;
	}
    
    /**
     * Get root directory of project.
     * @return string
     * @throws LoaderException
     */
	public static function getRoot()
	{
		if (isset(self::$root)) {
			return self::$root;
		} else throw new LoaderException('Cannot get root: root is not defined.');
	}
    
    /**
     * Return configuration from xml document (syntax: doc->tag1->tag2 ...)
     * @return \SimpleXMLElement
     * @throws LoaderException
     */
	public static function getConfig()
	{
		$configFactory = function ($root) {
			if (file_exists(FileSystem::append([$root, 'ini', 'config.xml']))) {
				$configPath = FileSystem::append([$root, 'ini', 'config.xml']);
			} elseif (file_exists(FileSystem::append([$root, 'ini', 'config_test.xml']))) {
				$configPath = FileSystem::append([$root, 'ini', 'config_test.xml']);
			} else {
				throw new LoaderException('Cannot load config!');
			}
			return simplexml_load_file($configPath);
			
		};
		
		if ( !isset(self::$config) ) {
			$root = self::getRoot();
			if ( isset($root) ) {
				self::$config = $configFactory(self::$root);
			} else throw new LoaderException('Failed to create config: root dir is not defined.');
		}
		return self::$config;
	}
    
    /**
     * Set app status manually (only if you know what you doing!)
     * @param int $status
     */
	public static function setStatus($status)
	{
		self::$status = $status;
	}
    
    /**
     * Get current app status.
     * @return int
     */
	public static function getStatus()
	{
		if (!isset(self::$status)) {
			$config = self::getConfig();
			$status = StatusSelector::getDefaultCode($config->app->status);
		} else {
			$status = self::$status;
		}
		return $status;
	}
    
    /**
     * Build DSN for PDO from config.
     * @return string
     * @throws LoaderException
     */
	public static function getDSN()
	{
		$dsnFactory = function ($config) {
			if (empty($config)) {
				throw new LoaderException('Configuration is not properly loaded');
			}
			$dsn = "mysql:host=localhost;dbname={$config->database->dbname};charset=utf8";
			return $dsn;
		};
		
		if ( !isset(self::$dsn) ) {
			$config = self::getConfig();
			if ( isset($config) ) {
				self::$dsn = $dsnFactory($config);
			} else throw new LoaderException('Failed to create dsn: config is not defined.');
		}
		return self::$dsn;
	}
    
    /**
     * Get PDO object (one per script).
     * @return \PDO
     * @throws LoaderException
     */
	public static function getPDO()
	{
		$pdoFactory = function ($config, $dsn) {
			//загружаем данные для соединения
			if (!isset($config->database->username)
				||
				!isset($config->database->password)
			) {
				throw new LoaderException('Config  not loaded or empty');
			} else {
				$username = $config->database->username;
				$password = $config->database->password;
			}
			//пробуем соединиться
			try {
				$opt = array(
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
					\PDO::ATTR_EMULATE_PREPARES => false,
					\PDO::MYSQL_ATTR_FOUND_ROWS => true
				);
				$pdo = new \PDO($dsn, $username, $password, $opt);
			} catch (\PDOException $e) {
				throw new LoaderException('Ошибка при подключении к базе данных');
			}
			return $pdo;
		};
		
		if ( !isset(self::$pdo)) {
			$config = self::getConfig();
			$dsn = self::getDSN();
			if ( isset($config) && isset($dsn)) {
				self::$pdo = $pdoFactory($config, $dsn);
			} else throw new LoaderException('Failed to create dsn: root is not defined.');
		}
		return self::$pdo;
	}
}
