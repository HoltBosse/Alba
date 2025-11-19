<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use \Exception;
use \PDOException;
Use Respect\Validation\Validator as v;

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
			$id = DB::getLastInsertedId();
			foreach ($groups as $group) {
				if (is_int($group)) {
					$query = "INSERT INTO user_groups (user_id, group_id) values (?,?)";
					DB::exec($query, [$id,$group]);
				}
			}
			return $id;
		}
		else {
			throw new Exception('Unable to create new user');
		}
	}

	public static function get_all_users() {
		return DB::fetchAll(
			"SELECT u.*, group_concat(DISTINCT g.display) AS `groups`, group_concat(DISTINCT t.title) AS tags 
			FROM users u 
			LEFT JOIN user_groups ug ON ug.user_id = u.id 
			LEFT JOIN `groups` g ON ug.group_id = g.id 
			LEFT JOIN tagged tt ON tt.content_id = u.id AND content_type_id=-2 
			LEFT JOIN tags t ON t.id = tt.tag_id AND t.state > 0 
			GROUP BY u.id"
		);
	}

	public function get_all_users_in_group($group_id) {
		$query = "Select u.*, group_concat(g.display) as `groups` from users u 
					Left Join user_groups ug on ug.user_id = u.id  
					Left Join `groups` g on ug.group_id = g.id 
					WHERE g.id=? 
					group by u.id";
		return DB::fetchAll($query, [$group_id]);
	}

	public static function get_all_groups_for_user($user_id) {
		$query = "SELECT * from `groups` where id in (select group_id from user_groups where user_id=?) ORDER BY display ASC";
		return DB::fetchAll($query, $user_id);
	}

	public static function get_group_name ($group_id) {
		$query = "select display from `groups` where id=?";
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
			$query = "select * from user_groups where group_id=? and user_id=?";
		}
		else {
			// by group name value
			$query = "select id from `groups` where value=? and id in (select group_id from user_groups where user_id=?)";
		}
		$result = DB::fetchAll($query, [$group_value, $this->id]);
		if ($result) {
			return true;
		}
		return false;
	}

	public function load_from_post() {
		$this->username = Input::getvar('username','RAW');

		$submittedPassword = Input::getvar('password',v::StringVal(),null);
		if ($submittedPassword) {
			$this->password = password_hash ($submittedPassword, PASSWORD_DEFAULT); 
		}
		else {
			$this->password = null;
		}
		$this->email = Input::getvar('email',v::email());
		if (!$this->email) {
			CMS::Instance()->queue_message('Invalid email','warning');
			return false;
		}
		$this->registered = date('Y-m-d H:i:s');
		$this->id = Input::getvar('id',v::IntVal(),null);
		$this->groups = Input::getvar('groups',v::arrayType()->each(v::intVal()));
		$this->tags = Input::getvar('tags',v::arrayType()->each(v::intVal()));
		$this->state = Input::getvar('userstate',v::IntVal());
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
			$this->groups = DB::fetchAll($query, [$id]);

			// get tags
			$query = "select tag_id from tagged where content_type_id=-2 and content_id=?";
			$tag_obj_array = DB::fetchAll($query, [$id]);
			foreach ($tag_obj_array as $tag) {
				$this->tags[] = $tag->tag_id;
			}

			return true;
		}
		else {
			return false;
		}
	}

	
	public static function get_username_by_id($id): ?string {
		$result = DB::fetch("select username from users where id=?", [$id]);
		return $result ? $result->username : null;
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
   		$addKey = substr(md5(uniqid((string) rand(),true)),3,10);
		$key = $key . $addKey;
		$query = "update users set reset_key=?, reset_key_expires=NOW() + INTERVAL 1 DAY where id=?";
		$ok = DB::exec($query, [$key, $this->id]);
		if (!$ok) {
			CMS::Instance()->queue_message('Error creating password reset key for ' . Input::stringHtmlSafe($this->username), 'warning', $_ENV["uripath"]."/admin");
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

	public function sendResetEmail(?string $baseUrl=null): Message {
		$status = false;

		if(!isset($baseUrl)) {
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			$domain = $_SERVER['HTTP_HOST'];
			$domainUrl = $protocol.$domain;
			$baseUrl = $domainUrl . "/admin";
		}
		
		if ($this->username != 'guest') {
			$key = $this->generate_reset_key();
			$link = $baseUrl . "?resetkey=" . $key;
			$markup = "
			<h5>Hi {$this->username}</h5>
			<p>A password reset has been request on " . $_ENV["sitename"] . "</p>
			<p>Click <a target='_blank' href='{$link}'>here</a> to choose a new password.</p>
			<p>If you did not initiate this request, please ignore this email.</p>
			";
			$mail = new Mail();	
			$mail->addAddress($this->email, $_ENV["sitename"] . " - User");
			$mail->subject = 'Reset Email for ' . $_ENV["sitename"];
			$mail->html = $markup;
			$mail->send();

			$status = true;
		}

		// either sent or not, show same message
		return new Message($status, MessageType::Success, 'If your email was associated with a user, you should receive a message with further instructions shortly.', $baseUrl);
	}

	public function resetPassword($password1, $password2, $resetkey, $baseUrl=null): Message {
		if(!isset($baseUrl)) {
			$baseUrl = "{$_ENV["uripath"]}/admin";
		}

		if ($password1 && $password2) {
			if ($password1 != $password2) {
				return new Message(false, MessageType::Danger, 'Passwords did not match.', $baseUrl . '?resetkey=' . $resetkey);	
			} else {
				// check resetkey matches a valid and current resetkey in user table
				$reset_user = new User();
				$reset_user_exists = $reset_user->get_user_by_reset_key($resetkey);
				if ($reset_user_exists) {
					// remove resetkey from user, update password and redirect to admin login
					if (!$reset_user->remove_reset_key()) {
						return new Message(false, MessageType::Danger, 'Error removing reset key.', $baseUrl);
					}
					if ($reset_user->update_password($password1)) {
						return new Message(true, MessageType::Success, 'Password changed for ' . Input::stringHtmlSafe($reset_user->username), $baseUrl);	
					}
					else {
						return new Message(false, MessageType::Danger, 'Unable to reset password. Please contact the system administrator.', $baseUrl. '?resetkey=' . $resetkey);		
					}
				} else {
					// no matching user for resetkey found or resetkey is outdated
					return new Message(false, MessageType::Danger, 'Invalid reset key or reset key is too old.', $_ENV["uripath"] . '/admin?resetkey=' . $resetkey);	
				}
			}
		} else {
			return new Message(false, MessageType::Danger, "An Error occured, Please contact the system administrator.", $baseUrl);
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
				try {
					$result = DB::exec(
						"UPDATE users SET username=?, email=?, state=? WHERE id=?",
						[$this->username, $this->email, $this->state, $this->id]
					);
				}
				catch (PDOException $e) {
					//CMS::Instance()->queue_message('Username and/or email already exists','danger',$_ENV["uripath"].'/admin/users/');
					if ($_ENV["debug"]==="true") {
						echo "<code>" . $e->getMessage() . "</code>";
					}
					$result = false;
				}
			}
			else {
				// new password
				$result = DB::exec(
					"UPDATE users SET username=?, password=?, email=?, state=? WHERE id=?",
					[$this->username, $this->password, $this->email, $this->state, $this->id]
				);
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
				if ($_ENV["debug"]==="true") {
					echo "<code>" . $e->getMessage() . "</code>";
					exit(0);
				}
				return false;
			}
		}
		else {
			// insert new
			try {
				$result = DB::exec(
					"INSERT INTO users (username,email,password) VALUES (?,?,?)",
					[$this->username, $this->email, $this->password]
				);	
			}
			catch (PDOException $e) {
				CMS::Instance()->queue_message('Username and/or email already exists','danger',$_ENV["uripath"].'/admin/users/');
				if ($_ENV["debug"]==="true") {
					echo "<code>" . $e->getMessage() . "</code>";
				}
				$result = false;
			}
			if ($result) {
				$new_user_id = DB::getLastInsertedId();
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
				CMS::Instance()->queue_message('Unable to create user','danger',$_ENV["uripath"].'/admin/users');
				return false;
			}
		}
	}

	public static function get_all_groups() {
		return DB::fetchAll("SELECT * FROM `groups` ORDER BY display ASC");
	}
}