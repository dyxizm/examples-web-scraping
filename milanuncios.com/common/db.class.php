<?php


class db {
	private static $_instance = null;
	
	static public function getInstance() {
		if(is_null(self::$_instance))
		{
			require __DIR__ . '/../config.php';
			self::$_instance = new mysqli(
				$config['db']['host'],
				$config['db']['user'],
				$config['db']['password'],
				$config['db']['database']
			);
			if (self::$_instance->connect_errno) {
				die('Connect Error: ' . $mysqli->connect_error);
			}
			/* изменение набора символов на utf8 */
			if (!self::$_instance->set_charset("utf8")) {
				die('Set charset Error: ' . $mysqli->connect_error); 
			}
		}
		return self::$_instance;
	}

	private function __construct() {
	// приватный конструктор ограничивает реализацию getInstance ()
	}
	protected function __clone() {
	// ограничивает клонирование объекта
	}
}
