<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\Form\Input;
Use \Exception;
Use Respect\Validation\Validator as v;

/**
 * User - WordPress user functions wrapper
 * Provides wrapper methods for WordPress user management
 */
class User {
	public ?int $id;
	// @phpstan-ignore missingType.iterableValue
	public array $groups;  // WordPress roles
	public string $username;
	public ?string $password;
	public ?string $email;
	// @phpstan-ignore missingType.iterableValue
	public array $tags;
	public int $state;
	public string $registered;
	public string $created;
	public ?int $domain;

	public function __construct() {
		$this->email = null;
		$this->password = null;
		$this->groups = [];
		$this->username = 'guest';
		$this->registered = date('Y-m-d H:i:s');
		$this->id = null;
		$this->tags = [];
		$this->state = 1;
		$this->domain = null;
	}

	/**
	 * Create a new user
	 * Wrapper for WordPress wp_insert_user()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function create_new (string $username, string $password, string $email, array $groups=[], int $state=0, ?int $domain=null): int {
		if(is_null($domain)) {
			$domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		}

		if ($username && $email && $password) {
			$userdata = [
				'user_login' => $username,
				'user_email' => $email,
				'user_pass' => $password,
				'role' => !empty($groups) ? $groups[0] : 'subscriber'
			];
			
			$user_id = wp_insert_user($userdata);
			
			if (is_wp_error($user_id)) {
				throw new Exception('Unable to create new user: ' . $user_id->get_error_message());
			}
			
			// Add additional roles/groups
			$user = new \WP_User($user_id);
			foreach (array_slice($groups, 1) as $group) {
				$user->add_role($group);
			}
			
			// Store Alba-specific metadata
			update_user_meta($user_id, 'alba_state', $state);
			update_user_meta($user_id, 'alba_domain', $domain);
			
			return $user_id;
		}
		else {
			throw new Exception('Unable to create new user');
		}
	}

	/**
	 * Get all users
	 * Wrapper for WordPress get_users()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_all_users(): array {
		$wp_users = get_users();
		$users = [];
		
		foreach ($wp_users as $wp_user) {
			$user_data = (object) [
				'id' => $wp_user->ID,
				'username' => $wp_user->user_login,
				'email' => $wp_user->user_email,
				'groups' => implode(', ', $wp_user->roles),
				'tags' => '', // TODO: implement with user meta
				'state' => (int) get_user_meta($wp_user->ID, 'alba_state', true) ?: 1,
				'created' => $wp_user->user_registered,
				'domain' => get_user_meta($wp_user->ID, 'alba_domain', true)
			];
			$users[] = $user_data;
		}
		
		return $users;
	}

	/**
	 * Get all users in a specific role (group)
	 * Wrapper for WordPress get_users()
	 */
	// @phpstan-ignore missingType.iterableValue
	public function get_all_users_in_group(int $group_id): array {
		// Map group_id to role name
		$role = $this->get_role_by_group_id($group_id);
		
		$wp_users = get_users(['role' => $role]);
		$users = [];
		
		foreach ($wp_users as $wp_user) {
			$user_data = (object) [
				'id' => $wp_user->ID,
				'username' => $wp_user->user_login,
				'email' => $wp_user->user_email,
				'groups' => implode(', ', $wp_user->roles)
			];
			$users[] = $user_data;
		}
		
		return $users;
	}

	/**
	 * Get all roles (groups) for a user
	 * Wrapper for WordPress WP_User->roles
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_all_groups_for_user(int $user_id): array {
		$wp_user = get_user_by('ID', $user_id);
		if (!$wp_user) {
			return [];
		}
		
		$roles = [];
		foreach ($wp_user->roles as $role) {
			$role_obj = get_role($role);
			if ($role_obj) {
				$roles[] = (object) [
					'id' => $role,
					'display' => ucfirst($role),
					'value' => $role
				];
			}
		}
		
		return $roles;
	}

	/**
	 * Get role name by group ID
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_group_name (int $group_id): array {
		// In WordPress, role names are strings, not IDs
		// This is a compatibility method
		$roles = wp_roles()->roles;
		$role_keys = array_keys($roles);
		
		if (isset($role_keys[$group_id])) {
			return $roles[$role_keys[$group_id]]['name'];
		}
		
		return 'Unknown';
	}

	/**
	 * Update user password
	 * Wrapper for WordPress wp_set_password()
	 */
	public function update_password (string $new_password): bool {
		if (!$this->id) {
			return false;
		}
		wp_set_password($new_password, $this->id);
		return true;
	}

	/**
	 * Check if user is member of a role/group
	 * Wrapper for WordPress user_can()
	 */
	public function is_member_of (mixed $group_value): bool {
		if (!$this->id) {
			return false;
		}
		
		$wp_user = get_user_by('ID', $this->id);
		if (!$wp_user) {
			return false;
		}
		
		if (is_numeric($group_value)) {
			// Convert group ID to role name
			$role = $this->get_role_by_group_id($group_value);
			return in_array($role, $wp_user->roles);
		}
		else {
			// Check by role name
			return in_array($group_value, $wp_user->roles);
		}
	}

	/**
	 * Check if user can access backend
	 * Wrapper for WordPress current_user_can()
	 */
	public function canAccessBackend(): bool {
		if (!$this->id) {
			return false;
		}
		return user_can($this->id, 'edit_posts');
	}

	/**
	 * Helper method to map group ID to role name
	 */
	private function get_role_by_group_id(int $group_id): string {
		$roles = array_keys(wp_roles()->roles);
		return $roles[$group_id] ?? 'subscriber';
	}

	public function load_from_post(): bool {
		$this->username = Input::getvar('username',v::StringVal());

		$submittedPassword = Input::getvar('password',v::StringVal(),null);
		if ($submittedPassword) {
			$this->password = $submittedPassword; // WordPress will hash it
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
		$this->groups = Input::getvar('groups',v::arrayType()->each(v::intVal()), []);
		$this->tags = Input::getvar('tags',v::arrayType()->each(v::intVal()), []);
		$this->state = Input::getvar('userstate',v::IntVal());
		$this->domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		return true;
	}

	/**
	 * Load user from WordPress by ID
	 * Wrapper for WordPress get_user_by()
	 */
	public function load_from_id(int $id): bool {
		$wp_user = get_user_by('ID', $id);
		
		if ($wp_user) {
			$this->username = $wp_user->user_login;
			$this->password = $wp_user->user_pass;
			$this->created = $wp_user->user_registered;
			$this->email = $wp_user->user_email;
			$this->id = $wp_user->ID;
			$this->state = (int) get_user_meta($wp_user->ID, 'alba_state', true) ?: 1;
			$this->domain = get_user_meta($wp_user->ID, 'alba_domain', true);
			
			// Get roles
			$this->groups = [];
			foreach ($wp_user->roles as $role) {
				$this->groups[] = (object) ['value' => $role, 'display' => ucfirst($role)];
			}
			
			// Get tags
			$this->tags = get_user_meta($wp_user->ID, 'alba_tags', true) ?: [];
			
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Get username by ID
	 * Wrapper for WordPress get_user_by()
	 */
	public static function get_username_by_id(int $id): ?string {
		$wp_user = get_user_by('ID', $id);
		return $wp_user ? $wp_user->user_login : null;
	}

	/**
	 * Check password
	 * Wrapper for WordPress wp_check_password()
	 */
	public function check_password(string $password): bool {
		if (!$this->id) {
			return false;
		}
		
		$wp_user = get_user_by('ID', $this->id);
		if (!$wp_user) {
			return false;
		}
		
		return wp_check_password($password, $wp_user->user_pass, $wp_user->ID);
	}

	/**
	 * Load user from email
	 * Wrapper for WordPress get_user_by()
	 */
	public function load_from_email(string $email, ?int $domain=null): User|bool {
		if($domain==null) {
			$domain = $_SESSION["current_domain"] ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		}

		$wp_user = get_user_by('email', $email);
		if ($wp_user) {
			// Check domain if needed
			$user_domain = get_user_meta($wp_user->ID, 'alba_domain', true);
			if ($user_domain && $user_domain != $domain) {
				return false;
			}
			
			return $this->load_from_id($wp_user->ID);
		} else {
			return false;
		}
	}

	/**
	 * Generate password reset key
	 * Wrapper for WordPress get_password_reset_key()
	 */
	public function generate_reset_key(): string {
		if (!$this->id) {
			throw new Exception('Cannot generate reset key without user ID');
		}
		
		$wp_user = get_user_by('ID', $this->id);
		if (!$wp_user) {
			throw new Exception('User not found');
		}
		
		$key = get_password_reset_key($wp_user);
		
		if (is_wp_error($key)) {
			CMS::Instance()->queue_message('Error creating password reset key for ' . Input::stringHtmlSafe($this->username), 'warning', $_ENV["uripath"]."/admin");
			throw new Exception($key->get_error_message());
		}
		
		return $key;
	}

	/**
	 * Remove reset key
	 * WordPress handles expiration automatically
	 */
	public function remove_reset_key(): bool {
		// WordPress doesn't expose a direct method to remove reset keys
		// They expire automatically, so we'll just return true
		return true;
	}

	/**
	 * Get user by reset key
	 * Wrapper for WordPress check_password_reset_key()
	 */
	public function get_user_by_reset_key (string $key): User|bool {
		// Extract user_login from key format (WordPress stores keys differently)
		// WordPress uses format: user_login:timestamp in the key
		// We need to find the user by checking all users
		
		// This is a simplified version - WordPress's check_password_reset_key requires both login and key
		// You may need to store the user_login separately when generating the key
		
		$users = get_users();
		foreach ($users as $wp_user) {
			$check = check_password_reset_key($key, $wp_user->user_login);
			if (!is_wp_error($check)) {
				return $this->load_from_id($wp_user->ID);
			}
		}
		
		return false;
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
			$link = $baseUrl . "?resetkey=" . $key . "&user=" . urlencode($this->username);
			$markup = "
			<h5>Hi {$this->username}</h5>
			<p>A password reset has been request on " . esc_html($_ENV["sitename"]) . "</p>
			<p>Click <a target='_blank' href='" . esc_url($link) . "'>here</a> to choose a new password.</p>
			<p>If you did not initiate this request, please ignore this email.</p>
			";
			
			// Use WordPress wp_mail instead of custom Mail class
			$subject = 'Reset Email for ' . $_ENV["sitename"];
			$headers = ['Content-Type: text/html; charset=UTF-8'];
			wp_mail($this->email, $subject, $markup, $headers);

			$status = true;
		}

		// either sent or not, show same message
		return new Message($status, MessageType::Success, 'If your email was associated with a user, you should receive a message with further instructions shortly.', $baseUrl);
	}

	public function resetPassword(string $password1, string $password2, string $resetkey, mixed $baseUrl=null): Message {
		if(!isset($baseUrl)) {
			$baseUrl = "{$_ENV["uripath"]}/admin";
		}

		if ($password1 && $password2) {
			if ($password1 != $password2) {
				return new Message(false, MessageType::Danger, 'Passwords did not match.', $baseUrl . '?resetkey=' . $resetkey);	
			} else {
				// Get user_login from request
				$user_login = $_REQUEST['user'] ?? '';
				if (!$user_login) {
					return new Message(false, MessageType::Danger, 'Invalid reset request.', $baseUrl);
				}
				
				// Verify reset key using WordPress
				$user = check_password_reset_key($resetkey, $user_login);
				
				if (is_wp_error($user)) {
					return new Message(false, MessageType::Danger, 'Invalid reset key or reset key is too old.', $baseUrl);
				}
				
				// Reset password using WordPress
				reset_password($user, $password1);
				
				return new Message(true, MessageType::Success, 'Password changed for ' . Input::stringHtmlSafe($user->user_login), $baseUrl);
			}
		} else {
			return new Message(false, MessageType::Danger, "An Error occurred, Please contact the system administrator.", $baseUrl);
		}
	}

	/**
	 * Save user
	 * Wrapper for WordPress wp_update_user() and wp_insert_user()
	 */
	public function save(): bool {
		$domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		
		//run last
		if($this->id && $this->domain!==null && $this->domain!==$domain) {
			//dont change domain if it already has one
			$domain = $this->domain;
		}

		$this->domain = $domain;

		if ($this->id) {
			// Update existing user
			Actions::add_action("userupdate", (object) [
				"affected_user"=>$this->id,
			]);

			$userdata = [
				'ID' => $this->id,
				'user_login' => $this->username,
				'user_email' => $this->email,
			];
			
			// Only update password if provided
			if ($this->password !== null) {
				$userdata['user_pass'] = $this->password;
			}
			
			$result = wp_update_user($userdata);
			
			if (is_wp_error($result)) {
				return false;
			}
			
			// Update Alba-specific metadata
			update_user_meta($this->id, 'alba_state', $this->state);
			update_user_meta($this->id, 'alba_domain', $domain);
			update_user_meta($this->id, 'alba_tags', $this->tags);
			
			// Update roles/groups
			$wp_user = new \WP_User($this->id);
			// Remove all current roles
			foreach ($wp_user->roles as $role) {
				$wp_user->remove_role($role);
			}
			// Add new roles
			foreach ($this->groups as $group) {
				if (is_object($group) && isset($group->value)) {
					$wp_user->add_role($group->value);
				} elseif (is_string($group)) {
					$wp_user->add_role($group);
				}
			}
			
			return true;
		}
		else {
			// Insert new user
			$userdata = [
				'user_login' => $this->username,
				'user_email' => $this->email,
				'user_pass' => $this->password,
				'role' => !empty($this->groups) ? (is_object($this->groups[0]) ? $this->groups[0]->value : $this->groups[0]) : 'subscriber'
			];
			
			$new_user_id = wp_insert_user($userdata);
			
			if (is_wp_error($new_user_id)) {
				CMS::Instance()->queue_message('Unable to create user: ' . $new_user_id->get_error_message(),'danger',$_ENV["uripath"].'/admin/users');
				return false;
			}
			
			$this->id = $new_user_id;
			
			Actions::add_action("usercreate", (object) [
				"affected_user"=>$this->id,
			]);
			
			// Save Alba-specific metadata
			update_user_meta($new_user_id, 'alba_state', $this->state);
			update_user_meta($new_user_id, 'alba_domain', $domain);
			update_user_meta($new_user_id, 'alba_tags', $this->tags);
			
			// Add additional roles (first role already set in user_data)
			if (count($this->groups) > 1) {
				$wp_user = new \WP_User($new_user_id);
				foreach (array_slice($this->groups, 1) as $group) {
					if (is_object($group) && isset($group->value)) {
						$wp_user->add_role($group->value);
					} elseif (is_string($group)) {
						$wp_user->add_role($group);
					}
				}
			}
			
			return true;
		}
	}

	/**
	 * Get all roles (groups)
	 * Wrapper for WordPress wp_roles()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_all_groups(?int $domain=null): array {
		if($domain==null) {
			$domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		}

		$wp_roles = wp_roles();
		$groups = [];
		
		foreach ($wp_roles->roles as $role_key => $role_data) {
			$groups[] = (object) [
				'id' => $role_key,
				'value' => $role_key,
				'display' => $role_data['name'],
				'domain' => null // WordPress roles are global
			];
		}
		
		return $groups;
	}
}