<?php
defined('CMSPATH') or die; // prevent unauthorized access



// load config
require_once (CMSPATH . "/config.php");
require_once (CMSPATH . "/admin/admin_config.php");

if (Config::$debug) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}




final class CMS {
	public $domain;
	public $pdo;
	public $user;
	public $uri_segments; // remaining segments of uri after controller found
	public $uri_path_segments; // uri path of found controller/page
	public $markup; // TODO: rendered html for current content item/page
	public $messages ;
	public $page_contents;
	public $page_id;
	private static $instance = null;
	private $core_controller = false;
	private $need_session = true;
	public $hooks = [];
	public $head_entries = []; // array of string to be output during CMSHEAD replacement
	public $version = "2.4.79";

	/* protected function __construct() {}
    protected function __clone() {}
    protected function __wakeup() {} */

	// singleton generator

	

	public final static function Instance(){
		if (self::$instance === null) {
			// check for singleton instancing errors caused by circular class references inside CMS construction
			/* echo "<pre>"; print_r(debug_backtrace()); echo "</pre>"; 
			echo "<pre>"; print_r('CMS created'); echo "</pre>"; echo "<hr>"; */
			self::$instance = new CMS();
		}
		return self::$instance;
	}

	public static function raise_404() {
		ob_end_clean ();
		http_response_code(404);
		if (file_exists(CMSPATH . "/my_404.php")) {
			include('my_404.php'); // provide your own HTML for the error page
		}
		else {
			CMS::show_error("Oops, something went wrong &#129300", "404");
		}
		exit(0);
	}

	
	public static function add_action ($hook_label, $plugin_object, $function_name, $priority=10) {
		// shamelessly borrowed idea from wordpress API
		// adds an action/filter to a hook - if hook doesn't exist, it's registered in CMS
		if (!isset($GLOBALS['hooks'][$hook_label])) {
			// hook not already registered, make new hook
			$GLOBALS['hooks'][$hook_label] = new Hook ($hook_label);
		}
		// add action to hook
		$action = new stdClass();
		$action->priority = $priority;
		$action->plugin_object = $plugin_object;
		$action->function_name = $function_name;
		$GLOBALS['hooks'][$hook_label]->actions[] = $action;
	}
	

	public static function get_admin_template() {
		$template="clean";
		if (property_exists('Config','admintemplate') && Config::$admintemplate) {
			if (file_exists(CURPATH . '/templates/' . Config::$admintemplate . "/index.php")) {
				$template = Config::$admintemplate;
			}
		}
		return $template;
	}

	public static function log($msg) {
		file_put_contents(CMSPATH . '/cmslog.txt', "\r\n" . date('Y-m-d H:i:s') . " - " . $msg, FILE_APPEND | LOCK_EX);
	}

	public function render_head() {
		// called by template
		// injects page title, opengraph, analytics js etc...
		ob_start();
		?>
		<title><?php echo $this->page->title;?> | <?php echo Config::$sitename; ?></title>
		<?php if (Configuration::get_configuration_value ('general_options', 'og_enabled')):?>
			<?php 
			$og_title = $this->page->get_page_option_value("og_title") ? $this->page->get_page_option_value("og_title") : $this->page->title; 
			$og_image = $this->page->get_page_option_value("og_image") ? $this->page->get_page_option_value("og_image") : null; 
			$og_keywords = $this->page->get_page_option_value("og_keywords") ? $this->page->get_page_option_value("og_keywords") : null; 
			$og_description = $this->page->get_page_option_value("og_description") ? $this->page->get_page_option_value("og_description") : null; 
			?>
			<meta property="og:title" content="<?php echo $og_title; ?>" />
			<meta property="og:keywords" content="<?php echo $og_keywords; ?>" />
			<meta property="og:description" content="<?php echo $og_description; ?>" />
			<meta property="og:type" content="website" />
			<meta property="og:url" content="<?php echo  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" />
			<meta name="description" content="<?php echo $og_description; ?>">
			<?php if ($og_image):?>
				<?php $og_image_dimensions = $this->pdo->query('select width,height from media where id=' . $og_image)->fetch();?>
				<meta property="og:image" content="<?php echo $this->protocol . $this->domain . Config::$uripath . "/image/" . $og_image ; ?>/web" />
				<meta property="og:image:width" content="<?php echo $og_image_dimensions->width ; ?>" />
				<meta property="og:image:height" content="<?php echo $og_image_dimensions->height ; ?>" />
			<?php endif; ?>
		<?php endif; ?>

		<?php echo "<script>window.uripath='" . Config::$uripath . "';</script>" ?>

		<?php 
		$cms_head = ob_get_contents();
		ob_end_clean();
		return $cms_head;
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

	public static function show_error($text, $http_code="") {
		if (ob_get_length()) {
			ob_end_clean();
		}
		?>
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
			<div style="display:flex; justify-content:center; align-items:center; height: 100%;">
				<div style="max-width: 50%;">
					<div style="display: flex; gap: 1rem; align-items:center; justify-content:center;">
						<?php 
							$logo_image_id = Configuration::get_configuration_value('general_options','admin_logo');
							$logo_src = $logo_image_id ? Config::$uripath . "/image/" . $logo_image_id : Config::$uripath . "/admin/templates/clean/alba_logo.webp";
						?>
						<img src="<?php echo $logo_src;?>" >
						<?php echo $http_code!="" ? '<h1 class="title" style="font-size: 6rem; width: 6rem;">' . $http_code . '</h1>' : ""; ?>
					</div>
					<br><br>
					<div>
						<h1 class="title is-3" style="text-align:center;"><?php echo $text;?></h1>
						<p style="text-align:center;"><a href="/" style="color: black; font-size: 1.5rem; text-decoration: underline;">Visit Home</a></p>
					</div>
				</div>
			</div>
		<?php
		die();
	}

	private function __construct() {

		// setup domain
		if (Config::$domain=='auto') {
			$this->domain = $_SERVER['HTTP_HOST'];
		}
		else {
			$this->domain = Config::$domain;
		}
		// protocol
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$this->protocol = 'https://';
		}
		else {
			$this->protocol = 'http://';
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
		$this->uri_segments = preg_split('@/@', parse_url($request, PHP_URL_PATH), -1, PREG_SPLIT_NO_EMPTY);

		
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

		
		// Load plugins
		$GLOBALS['hooks'] = []; // reset hooks array
		$this->enabled_plugins = $this->pdo->query('select * from plugins where state>0')->fetchAll();
		foreach ($this->enabled_plugins as $plugin_info) {
			$plugin_class_name = "Plugin_" . $plugin_info->location;
			$a_plugin = new $plugin_class_name($plugin_info);
		}
		// all hooks available in $GLOBALS['hooks']

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
				//$this->user->load_from_id($session_user_id); // cant use user class as it requires CMS - will call constructor twice!
				// code below is almost same as 'load_from_id' in user class
				$query = "select * from users where id=?";
				$stmt = $this->pdo->prepare($query);
				$stmt->execute(array($session_user_id));
				$result = $stmt->fetch();
				if ($result) {
					$this->user->username = $result->username;
					$this->user->password = $result->password;
					$this->user->created = $result->created;
					$this->user->email = $result->email;
					$this->user->id = $result->id;
					// get groups
					$query = "select * from `groups` where id in (select group_id from user_groups where user_id=?)";
					$stmt = $this->pdo->prepare($query);
					$stmt->execute(array($session_user_id));
					$this->user->groups = $stmt->fetchAll();
				}
			}
			// check if session too old
			$now = time();
			if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
				session_unset();
				session_destroy();
				session_start();
				
				if ($session_user_id) {
					// needs to be instance as messages not invoked yet
					if (ADMINPATH) {
						$_SESSION['redirect_url'] = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
						CMS::Instance()->queue_message('You were logged out due to inactivity.','danger',Config::$uripath . '/admin');
					}
					elseif (Config::$frontendlogin) {
						CMS::Instance()->queue_message('You were logged out due to inactivity.','danger',Config::$uripath . '/');
					}
					else {
						// user login timed out, but we're on front-end so let CMS continue to bootstrap
					}
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
		// returns name/location of controller (if any)
		// if controller found, it is set in $this->page->controller object
		// called by render_controller function
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

			if (property_exists ($this->page,'controller')) {
				// already have controller - should never happen? // todo- check
				return $this->page->controller;
			}
			else {
				// get controller for current page
				$query = "select controller_location from content_types where id=?";
				$stmt = $this->pdo->prepare($query);
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
		// called by template index.php to display main content

		// determine controller (if any)
		$controller = $this->get_controller();
		if ($controller) {
			include_once (CURPATH . "/controllers/" . $controller . "/controller.php");
		}
		else {
			// no controller - pages don't require one, just means that
			// only widgets will be rendered on page
			if (Config::$debug) {
				echo "<h5>No controller found for URL. (normal!)</h5>";
			}
		}
	}

	public function render_dev_banner() {
		ob_start();
		?> 
			<div class='dev_banner' style='background-color:red; display:flex; justify-content:center; align-items:center; z-index:99999999; width:100%; height: 10rem; position:fixed; bottom:0;'>
				<h1 style="font-weight: bold; font-size: 5rem; color: black;">DEV SITE</h1>
			</div>
		<?php
		ob_start();
		$dev_banner = ob_get_contents();
		ob_end_clean();
		return $dev_banner;
	}


	public function render() {
		// main entry point for CMS after object is instantiated

		// first check for core controllers
		// these are special controllers that bypass template rendering
		// used for image API, but user core controllers can also
		// be created to serve up other headless data
		if ($this->uri_segments && $this->uri_segments[0]=='image') {
			include_once (CMSPATH .  "/core/controllers/image/controller.php");
			exit(); // shouldn't be needed, controller should exit
		}
		// check for user core controllers
		foreach(scandir(CMSPATH . "/core/controllers") as $folder) {
			if($this->uri_segments[0] == $folder) {
				include_once (CMSPATH . "/core/controllers/" . $this->uri_segments[0] . "/controller.php");
				exit(); // shouldn't be needed, controller should exit
			}
		}

		// override debug if chosen
		if (Configuration::get_configuration_value('general_options','debug', $this->pdo)) {
			Config::$debug = true;
		} 

		// determine front-end template
		$default_template = Template::get_default_template();
		$template = $default_template->folder;

		//$this->content = include_once(CMSPATH . DS . 'templates' . DS . $template . DS . 'index.php');

		//$this->include_once_content (CMSPATH .'/templates/' . $template . '/index.php');
		// if ADMIN but guest, show login
	
		if ( (ADMINPATH && $this->user->username=="guest") || ($this->user->username=="guest" && Config::$frontendlogin) ) {
			// check for login attempt
			$email = Input::getvar('email','EMAIL'); // note: php email filter is a bit more picky than html input type email
			$password = Input::getvar('password','RAW');
			$login_user = new User();
			$redirect_path = Config::$uripath . "/";
			if (ADMINPATH) {
				$redirect_path = Config::$uripath . '/admin';
			}

			// authenticate plugins hook

			$this->user = Hook::execute_hook_filters('authenticate_user', $this->user); 
			
			if ($this->user->id!==false) {
				// an authenticate plugin logged the user in!
				$_SESSION['user_id'] = $this->user->id;
				if (isset($_SESSION['redirect_url'])) {
					$redirect_path = $_SESSION['redirect_url'];
					unset($_SESSION['redirect_url']);
				}
				Hook::execute_hook_actions('user_logged_in'); 
				$this->queue_message('Welcome ' . $this->user->username, 'success', $redirect_path);
			}

			// continue with core login attempt

			if ($password && (!$email)) {
				// badly formatted email submitted and discarded by php filter
				$this->queue_message('Invalid email','danger', $redirect_path);
			}
			if ($email && $password) {
				if ($login_user->load_from_email($email)) {
					if ($login_user->state<1) {
						$this->queue_message('Incorrect email or password','danger', $redirect_path);
					}
					// user exists, check password
					if ($login_user->check_password($password)) {
						// logged in!
						$_SESSION['user_id'] = $login_user->id;
						if (isset($_SESSION['redirect_url'])) {
							$redirect_path = $_SESSION['redirect_url'];
							unset($_SESSION['redirect_url']);
						}
						Hook::execute_hook_actions('user_logged_in'); 
						$this->queue_message('Welcome ' . $login_user->username, 'success', $redirect_path);
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
				$template = $this->get_admin_template();
			}
			include_once (CURPATH . '/templates/' . $template . "/login.php");
			if(Config::$dev_banner ?? null) {
				echo $this->render_dev_banner();
			}
		}

		else {
			if (ADMINPATH) {
				//check the users access rights
				if (sizeof($this->uri_segments) >= 1 && !Access::can_access(Admin_Config::$access[$this->uri_segments[0]])) {
					$this->queue_message('You do not have access to this page','danger', Config::$uripath . "/admin");
				}

				ob_start();
				$template = $this->get_admin_template();
				include_once (CURPATH . '/templates/' . $template . "/index.php");
				
				// save page contents to CMS
				$this->page_contents = ob_get_contents();
				ob_end_clean(); // clear and stop buffering
				// perform content filtering / plugins on CMS::page_contents;
				$this->page_contents = Hook::execute_hook_filters('content_ready_admin', $this->page_contents);
				if(Config::$dev_banner ?? null) {
					$this->page_contents .= $this->render_dev_banner();
				}
				echo $this->page_contents; // output
			}
			else {
				// recurse through page tree from root matching segment by segment
				// at first segment not matching alias, use last page found as controller
				// passing remaining unmatched segments as segments array
				$alias = false; // default 
				$page = false;
				
				if (sizeof($this->uri_segments)==0) {
					// TODO: work with user selected HOME page
					$alias = 'home';
					$query = "select * from pages where parent=-1 and alias='home' and state>0";
					$page = $this->pdo->query($query)->fetch();
				}
				else {
					$parent = -1; // start with root
					$this->uri_path_segments = [];
					while ($this->uri_segments) {
						$query = "select * from pages where parent=? and alias=? and state > 0";
						$stmt = $this->pdo->prepare($query);
						$stmt->execute(array($parent, $this->uri_segments[0]));
						$result = $stmt->fetch();
						if ($result) {
							// found possible alias, will check for deeper match on next loop - if any
							$alias = $result->alias;
							// remove from segments and push onto uri_path_segments
							$this->uri_path_segments[] = array_shift ($this->uri_segments);
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
					$this->raise_404();
				}
				if (Config::$debug) {
					echo "<h1>GOT ALIAS: {$alias}</h1>";
					echo "<h5>Segments passed to controller:</h5>";
					$this->pprint_r ($this->uri_segments);
				}

				$this->page = new Page();
				$this->page->load_from_id($page->id); // also loads correct template obj into page obj

				// check user has access
				if (!Access::can_access(json_decode($this->page->get_page_option_value("access")))) {
					// send to front-end homepage for now - TODO: make back-end config option
					$redirect_uri = Configuration::get_configuration_value ('general_options', 'signin_redirect');
					// make requested page available via session for login redirect if needed
					$smart_redirect = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
					$_SESSION['smart_redirect'] = $smart_redirect;
					$this->queue_message('You do not have access to this page','danger', Config::$uripath . $redirect_uri);
				}

				// front end buffering for plugin functionality
				ob_start();
				include_once (CURPATH . '/templates/' . $this->page->template->folder . "/index.php");
				// save page contents to CMS
				$this->page_contents = ob_get_contents();
				ob_end_clean();
				// perform content filtering / plugins on CMS::page_contents;
				$this->page_contents = Hook::execute_hook_filters('content_ready_frontend', $this->page_contents);
				// render CMS header - can incorporate changes to page title/og/metatags from content controllers
				$cms_head = $this->render_head();
				// add additional head_entries that may have been created
				foreach ($this->head_entries as $he) {
					$cms_head .= $he;
				}
				$this->page_contents = str_replace("<!--CMSHEAD-->", $cms_head, $this->page_contents);
				if(Config::$dev_banner ?? null) {
					$this->page_contents .= $this->render_dev_banner();
				}
				// output final content
				echo $this->page_contents;
			}	
			
		}
	}
}

// CLASS AUTOLOADER

spl_autoload_register(function($class_name) 
{
	// get path to potential class file
	$is_field_class = strpos($class_name, "Field_");
	$is_widget_class = strpos($class_name, "Widget_");
	$is_user_class = strpos($class_name, "User_");
	$is_plugin_class = strpos($class_name, "Plugin_");

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
	elseif ($is_plugin_class===0) {
		$plugin_class_location = str_replace('Plugin_','',$class_name);
		$path = CMSPATH . "/plugins/" . $plugin_class_location . "/plugin_class.php";
	}
	else {
		$path = CMSPATH . "/core/" . strtolower($class_name) . ".php";
	}
	if (!file_exists($path)) {
		// last ditch check if class in user_classes
		$path = CMSPATH . "/user_classes/" . $class_name . ".php";
		if (!file_exists($path)) {
			CMS::Instance()->show_error('Failed to autoload class: ' . $class_name);
		}
	}
    require_once $path;
});



CMS::Instance()->render();


