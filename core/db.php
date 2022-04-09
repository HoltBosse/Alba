<?php
defined('CMSPATH') or die; // prevent unauthorized access

class db {
	public $pdo;

	public function __construct() {
		$dsn = "mysql:host=" . Config::$dbhost . ";dbname=" . Config::$dbname . ";charset=" . Config::$dbchar;
		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
		try {
			$this->pdo = new PDO($dsn, Config::$dbuser, Config::$dbpass, $options);
		} catch (\PDOException $e) {
			if (Config::$debug) {
				throw new \PDOException($e->getMessage(), (int)$e->getCode());
			}
			else {
				CMS::show_error("Failed to connect to database: " . Config::$dbname);
			}
		}
	}	

	public static function exec($query, $paramsarray=[]) {
		if (!is_array($paramsarray)) {
			$paramsarray = array($paramsarray);
		}
		try {
			$stmt = CMS::Instance()->pdo->prepare($query);
			$success = $stmt->execute($paramsarray);
		}
		catch (\PDOException $e) {
			if (Config::$debug) {
				CMS::show_error("Failed to create PDO query statement: " . $e->getMessage());
			}
			else {
				CMS::show_error("Database query error - turn on debug for more information.");
			}
		}
		return $success;
	}

	public static function get_last_insert_id() {
		return CMS::Instance()->pdo->lastInsertId();
	}

	public static function fetchall($query, $paramsarray=[]) {
		if (!is_array($paramsarray)) {
			$paramsarray = array($paramsarray);
		}
		try {
			$stmt = CMS::Instance()->pdo->prepare($query);
			$stmt->execute($paramsarray);
			$result = $stmt->fetchAll();
		}
		catch (\PDOException $e) {
			if (Config::$debug) {
				//print_r (debug_backtrace()); exit(0);
				CMS::show_error("Error performing query: " . $e->getMessage());
			}
			else {
				CMS::show_error("Database query error - turn on debug for more information.");
			}
		}
		return $result;
	}

	public static function fetchallcolumn($query, $paramsarray=[]) {
		// fetch single col as array
		if (!is_array($paramsarray)) {
			$paramsarray = array($paramsarray);
		}
		try {
			$stmt = CMS::Instance()->pdo->prepare($query);
			$stmt->execute($paramsarray);
			$result = $stmt->fetchAll(PDO::FETCH_COLUMN);
		}
		catch (\PDOException $e) {
			if (Config::$debug) {
				//print_r (debug_backtrace()); exit(0);
				CMS::show_error("Error performing query: " . $e->getMessage());
			}
			else {
				CMS::show_error("Database query error - turn on debug for more information.");
			}
		}
		return $result;
	}

	public static function fetch($query, $paramsarray=[]) {
		if (!is_array($paramsarray)) {
			$paramsarray = array($paramsarray);
		}
		try {
			$stmt = CMS::Instance()->pdo->prepare($query);
			$stmt->execute($paramsarray);
			$result = $stmt->fetch();
		}
		catch (\PDOException $e) {
			if (Config::$debug) {
				CMS::show_error("Error performing query: " . $e->getMessage());
			}
			else {
				CMS::show_error("Database query error - turn on debug for more information.");
			}
		}
		return $result;
	}
}
