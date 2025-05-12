<?php
defined('CMSPATH') or die; // prevent unauthorized access 

function outputLine($input) {
    echo $input . "\n";
}

function outputError($input) {
    echo $input . "\n";
    die;
}

function checkDbConnection() {
    $dsn = "mysql:host=" . Config::dbhost() . ";dbname=" . Config::dbname() . ";charset=" . Config::dbchar();
	$options = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];
	try {
		$pdo = new PDO($dsn, Config::dbuser(), Config::dbpass(), $options);
	} catch (\PDOException $e) {
		return false;
	}

    return true;
}

//we fake the cms to load the db class
function loadDb() {
	Class CMS {
		public $pdo;
		private static $instance = null;

		private function __construct() {
			$dsn = "mysql:host=" . Config::dbhost() . ";dbname=" . Config::dbname() . ";charset=" . Config::dbchar();
			$options = [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
				PDO::ATTR_EMULATE_PREPARES   => false,
			];
			try {
				$this->pdo = new PDO($dsn, Config::dbuser(), Config::dbpass(), $options);
			} catch (\PDOException $e) {
				if (Config::debug()) {
					throw new \PDOException($e->getMessage(), (int)$e->getCode());
				} else {
					outputError("ERROR: couldnt load db!!!");
				}
			}
		}

		public static function pprint_r ($o) {
			print_r ($o);
		}

		public final static function Instance(){
			if (self::$instance === null) {
				self::$instance = new CMS();
			}
			return self::$instance;
		}
	}

	require_once(CMSPATH . "/core/db.php");
}