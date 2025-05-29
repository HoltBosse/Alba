<?php
defined('CMSPATH') or die; // prevent unauthorized access

class DB {
	public static function exec($query, $paramsarray=[]) {
		if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}
		$stmt = CMS::Instance()->pdo->prepare($query);
		return $stmt->execute($paramsarray);
	}

	public static function get_last_insert_id() {
		return CMS::Instance()->pdo->lastInsertId();
	}

	public static function fetchall($query, $paramsarray=[], $options=[]) {
		if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute($paramsarray);
		return $stmt->fetchAll($options["mode"] ?? PDO::FETCH_OBJ);
	}

	public static function fetchallcolumn($query, $paramsarray=[]) {
		return DB::fetchall($query, $paramsarray, ["mode"=>PDO::FETCH_COLUMN]);
	}

	public static function fetch($query, $paramsarray=[], $options=[]) {
		if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute($paramsarray);
		return $stmt->fetch($options["mode"] ?? PDO::FETCH_OBJ);
	}
}
