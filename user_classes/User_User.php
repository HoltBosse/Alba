<?php
defined('CMSPATH') or die; // prevent unauthorized access

// user space class for User stuff

class User_User {
	public function get_all_users() {
		return DB::fetchall('select * from users where state=1');
	}
}