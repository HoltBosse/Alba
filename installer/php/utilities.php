<?php
defined('CMSPATH') or die; // prevent unauthorized access 

function stylePrefix($input) {
	if($input=="NOTICE") {
		return "\033[0;37;43m[" . $input . "]\033[0m";
	} elseif($input=="ERROR") {
		return "\033[0;37;41m[" . $input . "]\033[0m";
	} elseif($input=="SUCCESS") {
		return "\033[0;37;42m[" . $input . "]\033[0m";
	} else {
		return "[" . $input . "]";
	}
}

function outputLine($input, $prefix="NOTICE") {
    echo stylePrefix($prefix) . ": " . $input . "\n";
}

function outputError($input, $prefix="ERROR") {
    echo outputLine($input, $prefix);
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

	HoltBosse\DB\DB::createInstance(
		"mysql:host=" . Config::dbhost() .";dbname=" . Config::dbname() .";charset=" . Config::dbchar(),
		Config::dbuser(),
		Config::dbpass()
	);
}

function fillInFields($fields) {
	foreach($fields as $field=>$value) {
        while (sizeof($fields[$field])<2) {
            if(sizeof($fields[$field])==0) {
                $input = readline("Enter $field: ");
                $fields[$field][0] = $input;
            } elseif(sizeof($fields[$field])==1) {
                $input = readline("Confirm $field: ");
                if($input == $fields[$field][0]) {
                    $fields[$field][1] = $input;
                } else {
                    $fields[$field] = [];
                    outputLine("Fields dont match!!!", "ERROR");
                }
            }
        }
    }

	return $fields;
}

function updateConfigFile($param, $value) {
	$lines = file(CMSPATH . "/config.php", FILE_IGNORE_NEW_LINES);

	foreach ($lines as &$line) {
        if (strpos($line, "$" . $param . " ") !== false) {
            $line = "    static \$$param = '$value';";
        }
    }

	file_put_contents(CMSPATH . "/config.php", implode(PHP_EOL, $lines));
}