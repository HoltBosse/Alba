<?php
defined('CMSPATH') or die; // prevent unauthorized access

class DB extends HoltBosse\DB\DB {
	public static function get_last_insert_id() {
		return DB::getLastInsertedId();
	}

	public static function fetchAllColumn($query, $paramsarray=[]) {
		return DB::fetchAll($query, $paramsarray, ["mode"=>PDO::FETCH_COLUMN]);
	}
}
