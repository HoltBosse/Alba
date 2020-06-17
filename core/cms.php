<?php
defined('CMSPATH') or die; // prevent unauthorized access



// load config
require_once (CMSPATH . "/config.php");

if (Config::$debug) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}




final class CMS {
	public $domain;
	public $pdo;
	public $user;
	public $uri_segments;
	public $markup; // TODO: rendered html for current content item/page
	public $messages ;
	public $page_contents;
	public $page_id;
	private static $instance = null;
	private $core_controller = false;
	private $need_session = true;
	public $version = "0.17 (beta) - new Page options";

	/* protected function __construct() {}
    protected function __clone() {}
    protected function __wakeup() {} */

	// singleton generator

	

	public final static function Instance(){
		
		if (self::$instance === null) {
			self::$instance = new CMS();
		}
		return self::$instance;
	}


	public static function log($msg) {
		file_put_contents(CMSPATH . '/cmslog.txt', "\r\n" . date('Y-m-d H:i:s') . " - " . $msg, FILE_APPEND | LOCK_EX);
	}

	static public function getvar($val, $filter="RAW") {
		if (isset($_GET[$val])) {
			$foo = $_GET[$val];
		}
		elseif (isset($_POST[$val])) {
			$foo = $_POST[$val];
		}
		else {
			/* echo "<code>Var " . $val . " not found</code>";
			exit(0); */
			return NULL;
		}
		if ($filter=="RAW") {
			return $foo;
		}
		elseif ($filter=="ALIAS") {
			$temp = filter_var($foo, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
			return CMS::stringURLSafe($temp);
		}
		elseif ($filter=="USERNAME"||$filter=="TEXT"||$filter=="STRING") {
			return filter_var($foo, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
		}
		elseif ($filter=="EMAIL") {
			return filter_var($foo, FILTER_VALIDATE_EMAIL);
		}
		elseif ($filter=="ARRAYTOJSON") {
			if (!is_array($foo)) {
				CMS::Instance()->queue_message('Cannot convert non-array to json in ARRAYTOJSON','danger',Config::$uripath . '/admin');
				//echo "<h5>Variable is not array, cannot perform ARRAYTOJSON filter</h5>";
				return false;
			}
			$json = json_encode($foo);
			return $json;
		}
		elseif ($filter=="ARRAYOFINT") {
			if (is_array($foo)) {
				$ok = true;
				foreach ($foo as $bar) {
					if ($bar===0||is_numeric($bar)) {
						// this one is fine
					}
					else {
						$ok = false;
					}
				}
				if ($ok) {
					return $foo;
				}
				else {
					return false;
				}
			}
			else {
				CMS::Instance()->queue_message('Cannot convert non-array to array in ARRAYOFINT','danger',Config::$uripath . '/admin');
				return false;
			}
		}
		elseif ($filter=="ARRAYOFSTRING") {
			if (is_array($foo)) {
				$ok = true;
				foreach ($foo as $bar) {
					if (is_string($bar)) {
						// this one is fine
					}
					else {
						$ok = false;
					}
				}
				if ($ok) {
					return $foo;
				}
				else {
					return false;
				}
			}
			else {
				CMS::Instance()->queue_message('Cannot convert non-array to array in ARRAYOFINT','danger',Config::$uripath . '/admin');
				return false;
			}
		}
		elseif ($filter=="NUM"||$filter=="INT"||$filter=="NUMBER"||$filter=="NUMERIC") {
			if ($foo===0) {
				return 0;
			}
			else {
				return filter_var($foo, FILTER_SANITIZE_NUMBER_INT);
			}
		}
		else {
			//return $foo;
			return false;
		}
	}

	static public function stringURLSafe($string)
    {
        //remove any '-' from the string they will be used as concatonater
        $str = str_replace('-', ' ', $string);
		$str = str_replace('_', ' ', $string);
		
        // remove any duplicate whitespace, and ensure all characters are alphanumeric
        $str = preg_replace(array('/\s+/','/[^A-Za-z0-9\-]/'), array('-',''), $str);

        // lowercase and trim
        $str = trim(strtolower($str));
        return $str;
	}
	
	public function render_widgets($position) {
		//echo "<h5>{$position}</h5>";
		// TODO: fix for different templates
		$widgets = Widget::get_widget_overrides_for_position ($this->page->id, $position);
		
		if (!$widgets) {
			// no overrides, so get widgets based on widget logic
			$widgets = Widget::get_widgets_for_position($this->page->id, $position);
		}
		
		foreach ($widgets as $widget) {
			if ($widget->state>0) {
				$type_info = Widget::get_widget_type($widget->type);
				//CMS::pprint_r ($widget);
				//CMS::pprint_r ($type_info);
				$widget_class_name = "Widget_" . $type_info->location;
				$widget_of_type = new $widget_class_name();
				$widget_of_type->load ($widget->id);
				$widget_of_type->render();
			}
			//CMS::pprint_r ($widget_of_type); 
		}
		//CMS::pprint_r ($widgets);
	}

	public static function show_error($text) {
		echo "<div style='height:100vh; width:100%; display:flex; align-items:center; justify-content:center;'>";
		echo "<h1>{$text}</h1>";
		echo "</div>";
		exit(0);
	}

	private function __construct() {

		// setup domain
		if (Config::$domain=='auto') {
			$this->domain = $_SERVER['HTTP_HOST'];
		}
		else {
			$this->domain = Config::$domain;
		}

		// routing and session checking
		// first strip base uri path (from config) out of path
		$request = $_SERVER['REQUEST_URI'];
		$to_remove = Config::$uripath;
		if (ADMINPATH) {
			$to_remove .= "/admin/";
		}
		$request = str_ireplace($to_remove, "", $request);
		// split into array of segments
		$this->uri_segments = preg_split('@/@', parse_url($request, PHP_URL_PATH), NULL, PREG_SPLIT_NO_EMPTY);

		
		if (@$this->uri_segments[0]=='image') {
			$this->need_session=false; // don't need session for image api
		}

		// db
		// TODO: move all db setup to db.php - make it not a class, just a old fashioned include
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
				$this->show_error("Failed to connect to database: " . Config::$dbname . "<br>Update config file or run installer and try again.");
			}
		}

		// END DB SETUP

		$this->user = new User(); // defaults to guest

		// start session if required
		// need_session defaults to true, but in router setup in construction above
		// can be set to false to aid performance of core routes that do not require
		// authentication eg. image API
		
		

		if( session_status() == PHP_SESSION_NONE && $this->need_session) {
			session_start();
			$session_user_id = null;
			if (isset($_SESSION['user_id'])) {
				$session_user_id = $_SESSION['user_id'];
			}
			//if (s::get('user_id')) {
			if ($session_user_id) {
				//$this->user->load_from_id(s::get('user_id'));
				$this->user->load_from_id($session_user_id);
			}
			// check if session too old
			$now = time();
			if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
				session_unset();
				session_destroy();
				session_start();
				if ($session_user_id) {
					CMS::Instance()->queue_message('You were logged out due to inactivity.','danger',Config::$uripath . '/admin');
				}
			}
			$session_time = Configuration::get_configuration_value('general_options','session_time', $this->pdo);
			if (!$session_time) {
				$session_time = 10; // 10 min if config is missing
			}
			//$_SESSION['discard_after'] = $now + (Config::$session_length * 60); // using configuration option now not config.php file
			$_SESSION['discard_after'] = $now + ($session_time * 60);
		}
		//CMS::pprint_r ($_SESSION);
		/* session_unset();
		session_destroy();
		$_SESSION = array(); */
		//CMS::pprint_r (session_status()); echo "<br>0) not available 1) not started 2) started<br>";

		// messages
		if ($this->need_session) {
			$this->messages = new Messages();
		}

		// default page contents
		$this->page_contents = "";
		$this->page_id = false;
	}

	private function showinfo() {

		$this->pprint_r($_SESSION);

		echo "<style>#cmsinfo {border:1px solid black;box-shadow:0 0 10px rgba(0,0,0,0.5);margin:1rem;} #cmsinfo p, #cmsinfo pre {font-size:1rem; font-family:sans-serif;}</style>";
		echo "<div id='info'>";
		echo "<p>Domain: {$this->domain}</p>";
		echo "<p>Base Path (subfolder): " . Config::$uripath . "</p>";
		echo "<p>CMSPATH: " . CMSPATH . "</p>";
		echo "<p>ADMINPATH: " . ADMINPATH . "</p>";
		echo "<p>Default template: " . Config::$template . "</p>";
		echo "<p>User:<p>";
		$this->pprint_r($this->user);
		echo "<p>Segments:</p>";
		$this->pprint_r($this->uri_segments);
		echo "<p>Page:</p>";
		$this->pprint_r($this->page);
		echo "</div>";
		echo "<p>DB:</p>";
		$this->pprint_r($this->pdo);
		echo "<h1>Session:</h1>";
		echo "<code>"; $this->pprint_r ($_SESSION); echo "</code>";
		echo "<h1>System Ready</h1>";
	}


	public function queue_message($msg, $type='success', $redirect=null) {
		$this->messages->add($type,$msg, $redirect);
	}

	public function display_messages() {
		$this->messages->display();
	}

	public function has_messages() {
		return false;
		return ($this->messages->hasMessages());
	}

	public static function pprint_r ($o) {
		echo "<pre>";
		print_r ($o);
		echo "</pre>";
	}





	public function get_controller() {
		if (ADMINPATH) {
			// works different here boys and girls
			// controller name is first part of segment
			// todo: lookup in db first? make sure it's installed?
			if ($this->uri_segments) {
				return $this->uri_segments[0];
			}
			else {
				return "home";
			}
		}
		else {
			// front end controllers
			// first determine page
			// look for deepest matching alias - once found, that page is our controller
			// if final matching alias is empty, show home

			// check for core controllers - eg. image
			if (sizeof ($this->uri_segments)>0) {
				if ($this->uri_segments[0]=='image') {
					$this->core_controller = true;
					return 'image';
				}
			}

			/* // FOR NOW JUST DO HOME PAGE
			CMS::Instance()->page = new Page();
			CMS::Instance()->page->load_from_alias('home'); */

			if (property_exists (CMS::Instance()->page,'controller')) {
				// already have controller - should never happen? // todo- check
				return CMS::Instance()->page->controller;
			}
			else {
				$query = "select controller_location from content_types where id=?";
				$stmt = CMS::Instance()->pdo->prepare($query);
				$stmt->execute(array($this->page->content_type));
				$result = $stmt->fetch();
				if ($result) {
					$this->page->controller = $result->controller_location;
					return $result->controller_location;
				}
				else {
					$this->page->controller = null;
					return false;
				}
			}
		}
	}

	public function render_controller() {
		// determine controller (if any)
		$controller = $this->get_controller();
		if ($controller) {
			// adjust controller path if core controller detected in get_controller function
			/* $core_controller_path = "";
			if ($this->core_controller) {
				$core_controller_path = "/core";
			} */
			//include_once (CURPATH . $core_controller_path . "/controllers/" . $controller . "/controller.php");
			include_once (CURPATH . "/controllers/" . $controller . "/controller.php");
		}
		else {
			if (Config::$debug) {
				echo "<h5>No controller found for URL. (normal!)</h5>";
			}
		}
	}


	public function render() {

		$template = "basic";
		// TODO template picking

		// override debug if chosen
		if (Configuration::get_configuration_value('general_options','debug', $this->pdo)) {
			Config::$debug = true;
		} 
		
		//$this->content = include_once(CMSPATH . DS . 'templates' . DS . $template . DS . 'index.php');

		//$this->include_once_content (CMSPATH .'/templates/' . $template . '/index.php');
		// if ADMIN but guest, show login
	
		if ( (ADMINPATH && $this->user->username=="guest") || ($this->user->username=="guest" && Config::$frontendlogin) ) {
			// check for login attempt
			//$username = $this->getvar('username','USERNAME'); // using email, username is now display name
			$email = $this->getvar('email','EMAIL'); // note: php email filter is a bit more picky than html input type email
			$password = $this->getvar('password','RAW');
			$login_user = new User();
			$redirect_path = Config::$uripath;
			if (ADMINPATH) {
				$redirect_path = Config::$uripath . '/admin';
			}
			if ($password && (!$email)) {
				// badly formatted email submitted and discarded by php filter
				$this->queue_message('Invalid email','danger', $redirect_path);
			}
			if ($email && $password) {
				//if ($login_user->load_from_username($username)) {
				if ($login_user->load_from_email($email)) {
					// user exists, check password
					if ($login_user->check_password($password)) {
						// logged in!
						//s::set('user_id',$login_user->id);
						$_SESSION['user_id'] = $login_user->id;
						$this->queue_message('Welcome ' . $login_user->username, 'success', $redirect_path);
						//echo "<p>welcome {$login_user->username}</p>";
					}
					else {
						$this->queue_message('Incorrect email or password','danger', $redirect_path);
					}
				}
				else {
					$this->queue_message('Incorrect email or password','danger', $redirect_path);
				}
			}
			if (ADMINPATH) {
				// force switch to admin template login 
				$template="clean";
			}
			include_once (CURPATH . '/templates/' . $template . "/login.php");
			//$this->pprint_r ($login_user);
			//$this->showinfo();
		}

		else {
			if (ADMINPATH) {
				ob_start();
				$template = "clean";
				include_once (CURPATH . '/templates/' . $template . "/index.php");
				// save page contents to CMS
				$this->page_contents = ob_get_contents();
				ob_end_clean(); // clear and stop buffering
				echo $this->page_contents; // output
			}
			else {
				
				// recurse through page tree from root matching segment by segment
				// at first segment not matching alias, use last page found as controller
				// passing remaining unmatched segments as 
				$alias = false; // default 
				$page = false;
				
				if (sizeof($this->uri_segments)==0) {
					// TODO: work with user selected HOME page
					$alias = 'home';
					$query = "select * from pages where parent=-1 and alias='home' and state>0";
					$page = $this->pdo->query($query)->fetch();
				}
				else {
					// check for core controllers
					if ($this->uri_segments[0]=='image') {
						include_once (CMSPATH .  "/core/controllers/image/controller.php");
						exit(); // shouldn't be needed, controller should exit
					}
					$parent = -1; // start with root
					while ($this->uri_segments) {
						$query = "select * from pages where parent=? and alias=? and state > 0";
						$stmt = $this->pdo->prepare($query);
						$stmt->execute(array($parent, $this->uri_segments[0]));
						$result = $stmt->fetch();
						if ($result) {
							// found possible alias, will check for deeper match on next loop - if any
							$alias = $result->alias;
							// remove from segments
							array_shift ($this->uri_segments);
							// and change parent to search for next possible alias match to page found
							$parent = $result->id;
							// set cms page
							$page = $result;
						}
						else {
							// quit loop leaving remaining segments in place for controller to consume
							break;
						}
					}
				}
				if (!$alias) {
					// 404
					echo "<h1>404!</h1>";
					exit(0);
				}
				if (Config::$debug) {
					echo "<h1>GOT ALIAS: {$alias}</h1>";
					echo "<h5>Segments passed to controller:</h5>";
					$this->pprint_r ($this->uri_segments);
				}

				$this->page = new Page();
				$this->page->load_from_alias($page->alias);
				
				// front end buffering for plugin functionality
				ob_start();
				include_once (CURPATH . '/templates/' . $template . "/index.php");
				// save page contents to CMS
				$this->page_contents = ob_get_contents();
				ob_end_clean();
				// perform content filtering / plugins on CMS::page_contents;
				include_once (CMSPATH .'/plugins/plugins.php');
				// output final content
				echo $this->page_contents;
			}	
		}
	}
}

// CLASS AUTOLOADER

spl_autoload_register(function($class_name) 
{
	if ($class_name=="CMS") {
		echo "<h1>wtf - cms class should not be required before its loaded itself below!</h1>";
		exit (0);
		//return false;
	}

	// get path to class file
	$is_field_class = strpos($class_name, "Field_");
	$is_widget_class = strpos($class_name, "Widget_");
	$is_user_class = strpos($class_name, "User_");

	if ($is_field_class===0) {
		$path = CMSPATH . "/core/fields/" . $class_name . ".php";
	}
	elseif ($is_widget_class===0) {
		$widget_class_type = str_replace('Widget_','',$class_name);
		$path = CMSPATH . "/widgets/" . $widget_class_type . "/widget_class.php";
	}
	elseif ($is_user_class===0) {
		$path = CMSPATH . "/user_classes/" . $class_name . ".php";
	}
	else {
		$path = CMSPATH . "/core/" . strtolower($class_name) . ".php";
	}
	
	

	
	//echo "<h1>autoload path: " . $path . "</h1>";
	if (!file_exists($path)) {
		CMS::Instance()->queue_message('Failed to autoload class: ' . $class_name , 'danger',Config::$uripath . "/admin");
		exit(0);
	}
    require_once $path;
});

CMS::Instance()->render();

