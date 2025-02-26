<?php
// guest, registered, author, editor, admin
defined('CMSPATH') or die; // prevent unauthorized access

class User {
	public $id;
	public $groups;
	public $username;
	public $password;
	public $email;
	public $tags;
	public $state;
	public $registered;
	public $created;

	public function __construct() {
		$this->email = false;
		$this->password = false;
		$this->groups = [];
		$this->username = 'guest';
		$this->registered = date('Y-m-d H:i:s');
		$this->id = false;
		$this->tags = [];
		$this->state = 1;
	}

	public static function create_new ($username, $password, $email, $groups=[], $state=0) {
		if ($username && $email && $password) {
			$hash = password_hash ($password, PASSWORD_DEFAULT);
			$query = "INSERT INTO users (username, email, password, state) VALUES (?,?,?,?)";
			//CMS::Instance()->$pdo->prepare($query)->execute([$username,$email,$hash,$state]);
			DB::exec($query, [$username,$email,$hash,$state]);
			$id = CMS::Instance()->pdo->lastInsertId();
			foreach ($groups as $group) {
				if (is_int($group)) {
					$query = "INSERT INTO user_groups (user_id, group_id) values (?,?)";
					DB::exec($query, [$id,$group]);
				}
			}
			return $id;
		}
		else {
			if (Config::debug()) {
				CMS::pprint_r('Unable to create new user');
				CMS::pprint_r(debug_backtrace());
				die();
			} else {
				CMS::Instance()->show_error('Unable to create new user');
			}
		}
	}
	
	// $pdo->prepare($sql)->execute([$name, $id]);
	public static function get_all_users() {
		//echo "<p>Getting all users...</p>";
		//$db = new db();
		//$db = CMS::$pdo;
		//$result = $db->pdo->query("select * from users")->fetchAll();
		//$result = CMS::Instance()->pdo->query("select * from users")->fetchAll(); 
		$query = "Select u.*, group_concat(DISTINCT g.display) as `groups`, group_concat(DISTINCT t.title) as tags 
					from users u 
					Left Join user_groups ug on ug.user_id = u.id 
					Left Join `groups` g on ug.group_id = g.id 
					Left Join tagged tt on tt.content_id = u.id AND content_type_id=-2 
					Left Join tags t on t.id = tt.tag_id AND t.state > 0 
					group by u.id";
		/* $result = CMS::Instance()->pdo->query($query)->fetchAll();
		return $result; */
		return DB::fetchall($query);
	}

	public function get_all_users_in_group($group_id) {
		$query = "Select u.*, group_concat(g.display) as `groups` from users u 
					Left Join user_groups ug on ug.user_id = u.id  
					Left Join `groups` g on ug.group_id = g.id 
					WHERE g.id=? 
					group by u.id";
		return DB::fetchall($query, [$group_id]);
	}

	public static function get_all_groups_for_user($user_id) {
		$query = "SELECT * from groups where id in (select group_id from user_groups where user_id=?) ORDER BY display ASC";
		return DB::fetchAll($query, $user_id);
	}

	public static function get_group_name ($group_id) {
		$query = "SELECT display FROM groups WHERE id=?";
		$result = DB::fetch($query, [$group_id]);
		return $result->display;
	}


	public function update_password ($new_password) {
		$hash = password_hash ($new_password, PASSWORD_DEFAULT);
		$query = "update users set password=? where id=?";
		DB::exec($query, [$hash, $this->id]);
		return true;
	}

	public function is_member_of ($group_value) {
		if (is_numeric($group_value)) {
			$query = "SELECT * FROM user_groups WHERE group_id=? AND user_id=?";
		}
		else {
			// by group name value
			$query = "SELECT id FROM groups WHERE value=? AND id IN (SELECT group_id FROM user_groups WHERE user_id=?)";
		}
		$result = DB::fetchAll($query, [$group_value, $this->id]);
		if ($result) {
			return true;
		}
		return false;
	}

	public function load_from_post() {
		$this->username = Input::getvar('username','RAW');
		if (isset($_POST['password'])) {
			if ($_POST['password']) {
				$this->password = password_hash ($_POST['password'], PASSWORD_DEFAULT); 
			}
			else {
				$this->password = null;
			}
		}
		else {
			$this->password = null;
		}
		$this->email = Input::getvar('email','EMAIL');
		if (!$this->email) {
			CMS::Instance()->queue_message('Invalid email','warning');
			return false;
		}
		$this->registered = date('Y-m-d H:i:s');
		$this->id = Input::getvar('id','INT');
		$this->groups = Input::getvar('groups','ARRAYOFINT');
		$this->tags = Input::getvar('tags','ARRAYOFINT');
		return true;
	}

	public function load_from_id($id) {
		$query = "select * from users where id=?";
		$result = DB::fetch($query, [$id]);
		if ($result) {
			$this->username = $result->username;
			$this->password = $result->password;
			$this->created = $result->created;
			$this->email = $result->email;
			$this->id = $result->id;
			$this->state = $result->state;
			// get groups
			$query = "select * from `groups` where id in (select group_id from user_groups where user_id=?)";
			$this->groups = DB::fetchall($query, [$id]);

			// get tags
			$query = "select tag_id from tagged where content_type_id=-2 and content_id=?";
			$tag_obj_array = DB::fetchall($query, [$id]);
			foreach ($tag_obj_array as $tag) {
				$this->tags[] = $tag->tag_id;
			}

			return true;
		}
		else {
			return false;
		}
	}

	
	public static function get_username_by_id($id) {
		$result = DB::fetch("select username from users where id=?", [$id]);
		return $result->username;
	}

	public function check_password($password) {
		$query = "select password from users where id=?";
		$hash = DB::fetch($query, [$this->id]);
		return password_verify($password, $hash->password);
	}

	public function load_from_email($email) {
		//echo "<h5>Loading user object from db with email {$email}</h5>";
		$query = "select * from users where email=?";
		$result = DB::fetch($query, [$email]);
		if ($result) {
			$this->username = $result->username;
			$this->password = $result->password;
			$this->created = $result->created;
			$this->groups = false; // TODO: get groups
			$this->email = $result->email;
			$this->id = $result->id;
			$this->state = $result->state;
			return true;
		}
		else {
			return false;
		}
	}

	public function generate_reset_key() {
		$key = md5((2418*2) . $this->email);
   		$addKey = substr(md5(uniqid(rand(),1)),3,10);
		$key = $key . $addKey;
		$query = "update users set reset_key=?, reset_key_expires=NOW() + INTERVAL 1 DAY where id=?";
		$ok = DB::exec($query, [$key, $this->id]);
		if (!$ok) {
			CMS::Instance()->queue_message('Error creating password reset key for ' . Input::stringHtmlSafe($this->username), 'error', Config::uripath()."/admin");
			// should not get here
		}
		return $key;
	}

	public function remove_reset_key() {
		$query = "update users set reset_key=null, reset_key_expires=null where id=?";
		$ok = DB::exec($query, [$this->id]); 
		if (!$ok) {
			return false;
		}
		else {
			return true;
		}
	}

	public function get_user_by_reset_key ($key) {
		// get used for reset key - only returns anything if reset key has not expired
		$query = "select * from users where reset_key=? and reset_key_expires>NOW() LIMIT 1";
		$result = DB::fetch($query, [$key]);
		if ($result) {
			return $this->load_from_id($result->id);
		}
		else {
			return false;
		}
	}

	public function save() {
		if ($this->id) {
			Actions::add_action("userupdate", (object) [
				"affected_user"=>$this->id,
			]);

			// update
			$this->registered = DB::fetch("SELECT created FROM users WHERE id=?", $this->id)->created;
			if ($this->password==null) {
				// no password change
				$query = "update users set username=?, email=? where id=?";
				try {
					$result = CMS::Instance()->pdo->prepare($query)->execute([$this->username, $this->email, $this->id]);
				}
				catch (PDOException $e) {
					CMS::Instance()->queue_message('Username and/or email already exists','danger',Config::uripath().'/admin/users/');
					if (Config::debug()) {
						echo "<code>" . $e->getMessage() . "</code>";
					}
					$result = false;
				}
			}
			else {
				// new password
				$query = "update users set username=?, password=?, email=? where id=?";
				$result = CMS::Instance()->pdo->prepare($query)->execute([$this->username, $this->password, $this->email, $this->id]);
			}
			if ($result) {
				// user tags
				DB::exec("delete from tagged where content_id=?", [$this->id]);
				foreach ($this->tags as $tag) {
					DB::exec("insert into tagged (content_id, tag_id, content_type_id) values(?,?,-2)", [$this->id, $tag]);
				}
			}
			if ($result) {
				// saved ok
				// UDPATE GROUPS
				// delete existing
				// todo: sanity checks, don't trust post data etc...
				DB::exec("delete from user_groups where user_id=?", [$this->id]);
				// re-add new config
				foreach ($this->groups as $group) {
					// todo: sanity check - make sure each group exists before insertion
					// don't trust post data
					DB::exec("insert into user_groups (user_id, group_id) values (?,?)", [$this->id, $group]);
				}
				return true;
			}
			else {
				if (Config::debug()) {
					echo "<code>" . $e->getMessage() . "</code>";
					exit(0);
				}
				return false;
			}
		}
		else {
			// insert new
			$query = "insert into users (username,email,password) values(?,?,?)";
			try {
				$result = CMS::Instance()->pdo->prepare($query)->execute([$this->username, $this->email, $this->password]);	
			}
			catch (PDOException $e) {
				CMS::Instance()->queue_message('Username and/or email already exists','danger',Config::uripath().'/admin/users/');
				if (Config::debug()) {
					echo "<code>" . $e->getMessage() . "</code>";
				}
				$result = false;
			}
			if ($result) {
				$new_user_id = CMS::Instance()->pdo->lastInsertId();
				$this->id = $new_user_id;

				Actions::add_action("usercreate", (object) [
					"affected_user"=>$this->id,
				]);

				// user tags
				foreach ($this->tags as $tag) {
					DB::exec("insert into tagged (content_id, tag_id, content_type_id) values(?,?,-2)", [$new_user_id, $tag]);
				}
			}
			if ($result) {
				// user groups
				
				foreach ($this->groups as $group) {
					// todo: sanity check - make sure each group exists before insertion
					// don't trust post data
					DB::exec("insert into user_groups (user_id, group_id) values (?,?)", [$new_user_id, $group]);
				}
				return true;
			}
			else {
				// todo - check for username/email already existing and clarify
				// todo: remove queue message? this function could be called from frontend too one day...
				CMS::Instance()->queue_message('Unable to create user','danger',Config::uripath().'/admin/users');
				return false;
			}
		}
	}

	public static function get_all_groups() {
		//echo "<p>Getting all users...</p>";
		$result = CMS::Instance()->pdo->query("SELECT * FROM groups ORDER BY display ASC")->fetchAll();
		return $result;
	}
}