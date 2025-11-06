<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use HoltBosse\Form\Form;
Use HoltBosse\Form\Fields\{Antispam, Checkbox, Honeypot, Html, Input as Text, Select, Textarea};
Use \stdClass;
Use \Exception;

final class CMS {
	public $domain;
	public $user;
	public $request;
	public $uri_segments; // remaining segments of uri after controller found
	public $uri_path_segments; // uri path of found controller/page
	public $full_path_segments; // full path segments of request
	public $markup; // TODO: rendered html for current content item/page
	public $messages ;
	public $page_contents;
	public $page;
	public $page_id;
	public $protocol;
	public $cache;
	public $enabled_plugins;
	private static $instance = null;
	private $core_controller = false;
	private $need_session = true;
	public $hooks = [];
	public $head_entries = []; // array of string to be output during CMSHEAD replacement
	public $version = "2.5.5";
	public $edit_page_id; // used in admin to store page being edited

	private static $coreControllersRegistry = [
		"image"=>__DIR__ . "/../corecontrollers/image",
		"api"=>__DIR__ . "/../corecontrollers/api",
		"js"=>__DIR__ . "/../corecontrollers/js",
	];

	private static $adminControllersRegistry = [
		//loaded in __construct()
	];

	private static $fakeFilesRegistry = [
		"sitemap.xml"=>__DIR__ . "/../fakefiles/sitemap.xml.php",
		"robots.txt"=>__DIR__ . "/../fakefiles/robots.txt.php",
	];

	public final static function Instance(){
		if (self::$instance === null) {
			// check for singleton instancing errors caused by circular class references inside CMS construction
			/* echo "<pre>"; print_r(debug_backtrace()); echo "</pre>"; 
			echo "<pre>"; print_r('CMS created'); echo "</pre>"; echo "<hr>"; */
			self::$instance = new CMS();
		}
		return self::$instance;
	}

	public static function registerCoreController(string $controllerName, string $controllerPath): bool {
		if (!isset(self::$coreControllersRegistry[$controllerName])) {
			self::$coreControllersRegistry[$controllerName] = $controllerPath;

			return true;
		}
		return false;
	}

	public static function registerCoreControllerDir(string $coreControllerDirPath): void {
		foreach(glob($coreControllerDirPath . '/*') as $file) {
			CMS::registerCoreController(
				basename($file, '.php'),
				$file
			);
		}
	}

	public static function getCoreControllerPath(string $controllerName): ?string {
		if (isset(self::$coreControllersRegistry[$controllerName])) {
			return self::$coreControllersRegistry[$controllerName];
		}
		return null;
	}

	public static function registerAdminController(string $controllerName, string $controllerPath): bool {
		if (!isset(self::$adminControllersRegistry[$controllerName])) {
			self::$adminControllersRegistry[$controllerName] = $controllerPath;

			return true;
		}
		return false;
	}

	public static function registerAdminControllerOverride(string $controllerName, string $controllerPath): void {
		self::$adminControllersRegistry[$controllerName] = $controllerPath;
	}

	public static function registerAdminControllerDir(string $adminControllerDirPath): void {
		foreach(glob($adminControllerDirPath . '/*') as $file) {
			CMS::registerAdminController(
				basename($file, '.php'),
				$file
			);
		}
	}

	public static function isAdminController(string $controllerName): bool {
		return isset(self::$adminControllersRegistry[$controllerName]);
	}

	public static function registerFakeFile(string $fileName, string $filePath): bool {
		if (!isset(self::$fakeFilesRegistry[$fileName])) {
			self::$fakeFilesRegistry[$fileName] = $filePath;

			return true;
		}
		return false;
	}

	public static function configureDebug() {
		if ($_ENV["debug"]==="true") {
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);

			if($_ENV["debugwarnings"]==="true") {
				error_reporting(E_ALL);
			} else {
				error_reporting(E_ERROR);
			}
		}

		// EXCEPTION HANDLER
		set_exception_handler(function($e) {
			if($_ENV["debug"]==="true") {
				ErrorManager::exceptionHandler($e);
			} else {
				CMS::show_error("Internal Server Error", 500, ErrorManager::generateNiceException($e));
			}
		});
	}

	public function isAdmin(): bool {
		return (isset($this->full_path_segments[0]) && $this->full_path_segments[0]=='admin');
	}

	public static function raise_404() {
		ob_end_clean ();ob_end_clean ();
		// check if we need to redirect this page
		$relative_url = rtrim(CMS::Instance()->request, '/');
		if(CMS::Instance()->isAdmin()) {
			$relative_url = "/admin" . $relative_url;
		}

		$domainIndex = CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		$valid_redirect = DB::fetch("SELECT * FROM redirects WHERE `state`=1 AND old_url=? AND domain=?", [$relative_url, $domainIndex]);
		if ($valid_redirect) {
			header('Location: '.$valid_redirect->new_url, true, $valid_redirect->header);
		} else {
			// handle redirect/404 capturing
			if ($_ENV["capture_404s"]!=="false") {
				$existing_redirect_id = DB::fetch('SELECT id FROM redirects WHERE old_url=? and domain=?', [$relative_url, $domainIndex])->id ?? false;
				if ($existing_redirect_id) {
					// increment hit for 404
					DB::exec('UPDATE redirects SET hits=hits+1 WHERE id=?', $existing_redirect_id);
				}
				else {
					// check if URL ends in file suffix for certain file types and ignore for redirect storage
					$ignore_suffixes = ['.zip', '.gz', '.tag', '.bz', '.sh', '.tar', '.gzip','.7z','.tgz','.exe','.bak','.iso'];
					$pattern = '/(' . implode('|', array_map('preg_quote', $ignore_suffixes)) . ')$/i';
					$ignore_file = preg_match($pattern, $relative_url);
					// check if url contains, at any position, any of the following strings and ignore for redirect storage
					$ignore_contains = ['wp-admin','wp-content','wp-includes','wp-login', 'wp-add', '.well-known','adminer','phpmyadmin','.git'];
					$pattern = '/' . implode('|', array_map('preg_quote', $ignore_contains)) . '/i';
					$ignore_request = preg_match($pattern, $relative_url);
					if (!$ignore_file && !$ignore_request) {
						// create new redirect
						$user_id_int = CMS::Instance()->user->id ? CMS::Instance()->user->id : 0;
						$params = [$relative_url, $_SERVER['HTTP_REFERER'], $user_id_int , $user_id_int, $domainIndex];
						DB::exec('INSERT INTO redirects (`state`, old_url, referer, created_by, updated_by, note, hits, domain) VALUES(0,?,?,?,?,"auto",1,?)', $params);
					}
				}
			}
			if (isset($_ENV["custom_404_file_path"])) {
				include($_ENV["custom_404_file_path"]); // provide your own HTML for the error page
			} else {
				CMS::show_error("Oops, something went wrong &#129300", "404");
			}
		}
		exit(0);
	}

	public static function add_action ($hook_label, $plugin_object, $function_name, $priority=10) {
		// shamelessly borrowed idea from wordpress API
		// adds an action/filter to a hook - if hook doesn't exist, it's registered in CMS
		if (!isset($GLOBALS['hooks'][$hook_label])) {
			// hook not already registered, make new hook
			$GLOBALS['hooks'][$hook_label] = new Hook ();
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
		if (isset($_ENV["admintemplate"]) && $_ENV["admintemplate"]) {
			if (file_exists(Template::getTemplatePath($_ENV["admintemplate"], true) . "/index.php")) {
				$template = $_ENV["admintemplate"];
			}
		}
		return $template;
	}

	public static function log($msg) {
		file_put_contents($_ENV["cms_log_file_path"], "\r\n" . date('Y-m-d H:i:s') . " - " . $msg, FILE_APPEND | LOCK_EX);
	}

	public function render_head() {
		// called by template
		// injects page title, opengraph, analytics js etc...
		ob_start();
		?>
		<title><?php echo $this->page->title;?> | <?php echo $_ENV["sitename"]; ?></title>
		<?php if (Configuration::get_configuration_value ('general_options', 'og_enabled')):?>
			<?php 
			$og_title = $this->page->get_page_option_value("og_title") ? $this->page->get_page_option_value("og_title") : $this->page->title; 
			$og_image = $this->page->get_page_option_value("og_image") ? $this->page->get_page_option_value("og_image") : null; 
			$og_description = $this->page->get_page_option_value("og_description") ? $this->page->get_page_option_value("og_description") : null; 
			?>
			<meta property="og:title" content="<?php echo Input::stringHtmlSafe($og_title); ?>" />
			<meta property="og:description" content="<?php echo Input::stringHtmlSafe($og_description); ?>" />
			<meta property="og:type" content="website" />
			<meta property="og:url" content="<?php echo  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" />
			<meta name="description" content="<?php echo Input::stringHtmlSafe($og_description); ?>">
			<?php if ($og_image):?>
				<?php $og_image_dimensions = DB::fetch('SELECT width,height FROM media WHERE id=?', $og_image);?>
				<meta property="og:image" content="<?php echo $this->protocol . $this->domain . $_ENV["uripath"] . "/image/" . $og_image ; ?>/web" />
				<meta property="og:image:width" content="<?php echo $og_image_dimensions->width ; ?>" />
				<meta property="og:image:height" content="<?php echo $og_image_dimensions->height ; ?>" />
			<?php endif; ?>
		<?php endif; ?>

		<?php echo "<script>window.uripath='" . $_ENV["uripath"] . "';</script>" ?>

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
				$widget_class_name = Widget::getWidgetClass($type_info->location);
				$widget_of_type = new $widget_class_name();
				$widget_of_type->load ($widget->id);
				$widget_of_type->internal_render();
			}
			//CMS::pprint_r ($widget_of_type); 
		}
		//CMS::pprint_r ($widgets);
	}

	public static function show_error($text, $http_code="500", $qrCodeText=null) {
		ob_end_clean();
		ob_end_clean();
		http_response_code($http_code);
		?>
			<!DOCTYPE html>
			<html style="height: 100%;" lang="en">
				<head>
					<title>Page not Found</title>
					<meta name="viewport" content="width=device-width, initial-scale=1" />
					<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
				</head>
				<body style="display:flex; justify-content:center; align-items:center; height: 100%;">
					<div style="max-width: 50%;">
						<div style="display: flex; gap: 1rem; align-items:center; justify-content:center;">
							<?php 
								$logo_image_id = Configuration::get_configuration_value('general_options','admin_logo');
								$logo_src = $logo_image_id ? $_ENV["uripath"] . "/image/" . $logo_image_id : $_ENV["uripath"] . "/admin/templates/clean/alba_logo.webp";
								$img_meta_string = $_ENV["sitename"] . " site logo";
							?>
							<img src="<?php echo $logo_src;?>" title="<?= $img_meta_string; ?>" alt="<?= $img_meta_string; ?>">
							<?php echo $http_code!="" ? '<h1 class="title" style="font-size: 6rem; width: 6rem;">' . $http_code . '</h1>' : ""; ?>
						</div>
						<br><br>
						<div style="display: flex; flex-direction: column; gap: 1rem; align-items:center; justify-content:center;">
							<h1 class='title is-3' style='text-align:center; margin-bottom: 0;'><?php echo $text; ?></h1>
							<?php
								if($qrCodeText) {
									?>
										<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.js"></script>
										<canvas id="canvas" data-error-code="<?php echo $text; ?>"></canvas>
										<script type="module">
											QRCode.toCanvas(document.getElementById('canvas'), '<?php echo $qrCodeText; ?>');
										</script>
									<?php
								}
							?>
							<p style="text-align:center;"><a href="/" style="color: black; font-size: 1.5rem; text-decoration: underline;" hreflang="en">Visit Home</a></p>
						</div>
					</div>
				</body>
			</html>
		<?php
		die();
	}

	public static function parseUrlToSegments(string $url): array {
		return preg_split('@/@', parse_url($url, PHP_URL_PATH), -1, PREG_SPLIT_NO_EMPTY);
	}

	public static function registerBuiltInFormFields(): void {
		$formPackageFormFields = [
			"Antispam", "Checkbox", "Honeypot", "Html", "Text", "Select", "Textarea"
		];
		foreach($formPackageFormFields as $formField) {
			$formFieldClass = "HoltBosse\\Form\\Fields\\" . $formField . "\\" . $formField;
			if($formField=="Text") {
				$formFieldClass = "HoltBosse\\Form\\Fields\\Input\\Input";
			}

			if (class_exists($formFieldClass)) {
				Form::registerField($formField, $formFieldClass);
			}
		}

		foreach(glob(__DIR__ . "/../Fields/*") as $fieldFile) {
			$fieldName = basename($fieldFile);
			$fieldClass = "HoltBosse\\Alba\\Fields\\" . $fieldName . "\\" . $fieldName;
			if (class_exists($fieldClass)) {
				Form::registerField($fieldName, $fieldClass);
			} else {
				CMS::pprint_r("Field class not found: " . $fieldClass); die;
			}
		}

		Form::registerFieldAlias("Input", "Text");
	}

	private function __construct() {

		// setup domain
		if ($_ENV["domain"]=='auto') {
			$this->domain = $_SERVER['HTTP_HOST'];
		} else {
			$this->domain = $_ENV["domain"];
		}
		// protocol
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$this->protocol = 'https://';
		} else {
			$this->protocol = 'http://';
		}

		$this->full_path_segments = $this->parseUrlToSegments($_SERVER['REQUEST_URI']);

		// routing and session checking
		// first strip base uri path (from config) out of path
		$this->request = $_SERVER['REQUEST_URI'];
		$to_remove = $_ENV["uripath"];
		if($this->isAdmin()) {
			$to_remove .= "/admin";
		}
		$this->request = str_ireplace($to_remove, "", $this->request);
		// split into array of segments
		$this->uri_segments = $this->parseUrlToSegments($this->request);
		
		if (@$this->uri_segments[0]=='image') {
			$this->need_session=false; // don't need session for image api
		}

		//load admin controllers
		CMS::registerAdminControllerDir(__DIR__ . "/../admin/controllers");

		//load Form Fields
		CMS::registerBuiltInFormFields();
		
		// Load plugins
		$GLOBALS['hooks'] = []; // reset hooks array
		$this->enabled_plugins = DB::fetchAll('SELECT * FROM plugins WHERE state>0');
		foreach ($this->enabled_plugins as $plugin_info) {
			$plugin_class_name = Plugin::getPluginClass($plugin_info->location);
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
				$result = DB::fetch("SELECT * FROM users WHERE id=? AND state>0", $session_user_id);
				if ($result) {
					$this->user->username = $result->username;
					$this->user->password = $result->password;
					$this->user->created = $result->created;
					$this->user->email = $result->email;
					$this->user->id = $result->id;
					// get groups
					$this->user->groups = DB::fetchAll(
						"SELECT * FROM `groups` WHERE id IN (SELECT group_id FROM user_groups WHERE user_id=?)",
						$session_user_id
					);
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
					if ($this->isAdmin()) {
						$_SESSION['redirect_url'] = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
						CMS::Instance()->queue_message('You were logged out due to inactivity.','danger',$_ENV["uripath"] . '/admin');
					}
					elseif ($_ENV["frontendlogin"]==="true") {
						CMS::Instance()->queue_message('You were logged out due to inactivity.','danger',$_ENV["uripath"] . '/');
					}
					else {
						// user login timed out, but we're on front-end so let CMS continue to bootstrap
					}
				}
			}
			$session_time = Configuration::get_configuration_value('general_options','session_time');
			if (!$session_time) {
				$session_time = 10; // 10 min if config is missing
			}
			//$_SESSION['discard_after'] = $now + (Config::session_length() * 60); // using configuration option now not config.php file
			$_SESSION['discard_after'] = $now + ($session_time * 60);
		}
		//CMS::pprint_r ($_SESSION);
		/* session_unset();
		session_destroy();
		$_SESSION = []; */
		//CMS::pprint_r (session_status()); echo "<br>0) not available 1) not started 2) started<br>";

		// messages
		if ($this->need_session) {
			$this->messages = new Messages();
		}

		// default page contents
		$this->page_contents = "";
		$this->page_id = false;

		// moved cache serve checking post session + db bootstrap
		// small performance hit, but only way to alleviate potential security issues for following checks

		// check for core controller - save folder name if found for include during rendering
		if(!$this->isAdmin() && sizeof($this->uri_segments)>0 && isset(self::$coreControllersRegistry[$this->uri_segments[0]])) {
			$this->core_controller = $this->uri_segments[0];
		}

		if ( $_ENV["debugwarnings"]!=="true" && $_ENV["debug"]!=="true" && $_ENV["cache_enabled"]==="true" && !$this->isAdmin() && !($_SESSION['flash_messages'] ?? null) && !$this->user->id && !$this->core_controller)  {
			// check if caching is turned on and we are on front-end 
			// admin will never create caches, so no point in even checking
			// also never serve cache if messages waiting to be viewed potentially
			// and never serve if any user is logged in
			// also never serve if this is a core controller
			// and don't serve if debugging / or debugwarnings are turned on 
			$this->cache = new Cache();
			$cached_page_file = $this->cache->is_cached($this->request, 'url');
			if ($cached_page_file) {
				$this->cache->serve_page($cached_page_file);
			}
		}
	}

	public function queue_message($msg, $type='success', $redirect=null) {
		if(gettype($type)=="string") {
			$type = MessageType::from($type);
		}

		$this->messages->add($type, $msg, $redirect);
	}

	public function display_messages() {
		$this->messages->display();
	}

	public static function pprint_r ($o) {
		echo "<pre>";
		print_r ($o);
		echo "</pre>";
	}

	public static function jprint_r($o, $mode = "log") {
    	echo "<script>console.".$mode."(".json_encode($o).");</script>";
	}

	public function get_controller() {
		// returns name/location of controller (if any)
		// if controller found, it is set in $this->page->controller object
		// called by render_controller function
		if ($this->isAdmin()) {
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
				$result = DB::fetch("SELECT controller_location FROM content_types WHERE id=?", $this->page->content_type);
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
		$controllerName = $this->get_controller();
		if ($controllerName) {
			ob_start();
				if($this->isAdmin()) {
					include_once(self::$adminControllersRegistry[$controllerName] . "/controller.php");
				} else {
					include_once(Content::getContentControllerPath($controllerName) . "/controller.php");
				}
			$output = ob_get_clean();
			$output = Hook::execute_hook_filters('on_controller_render', $output, $controllerName);
			echo $output;
		}
		else {
			// no controller - pages don't require one, just means that
			// only widgets will be rendered on page
			if ($_ENV["debugwarnings"]==="true") {
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
		$dev_banner = ob_get_contents();
		ob_end_clean();
		return $dev_banner;
	}

	public static function getDomainIndex(string $domain) {
		$domains = [$_SERVER["HTTP_HOST"]];
		if(isset($_ENV["domains"])) {
			$domains = explode(",", $_ENV["domains"]);
		}

		$index = array_search($domain, $domains);
		if($index===false) {
			throw new Exception("Could not resolve domain name");
		}

		return $index;
	}

	public function render() {
		// main entry point for CMS after object is instantiated

		// first check for core controllers
		// these are special controllers that bypass template rendering
		// used for image API, but user core controllers can also
		// be created to serve up other headless data
		// check for user core controllers
		// already checked for existing folder during cache testing
		if($this->core_controller) {
			include_once(self::$coreControllersRegistry[$this->core_controller] . "/controller.php");
			exit(); // shouldn't be needed, controller should exit
		}

		$script_url = explode("?", $_SERVER["REQUEST_URI"])[0];
		//check if the first char is /, if so remove it
		if (substr($script_url, 0, 1) == "/") {
			$script_url = substr($script_url, 1);
		}
		//check if the last char is /, if so remove it
		if (substr($script_url, -1) == "/") {
			$script_url = substr($script_url, 0, -1);
		}

		if(isset(self::$fakeFilesRegistry[$script_url])) {
			include_once (self::$fakeFilesRegistry[$script_url]);
			exit();
		}

		// determine front-end template
		$default_template = Template::get_default_template();
		$template = $default_template->folder;

		//$this->content = include_once(CMSPATH . DS . 'templates' . DS . $template . DS . 'index.php');

		//$this->include_once_content (CMSPATH .'/templates/' . $template . '/index.php');
		// if ADMIN but guest, show login

		if ( ($this->isAdmin() && $this->user->username=="guest") || ($this->user->username=="guest" && $_ENV["frontendlogin"]==="true") ) {
			$email = Input::getvar('email','EMAIL'); // note: php email filter is a bit more picky than html input type email
        	$password = Input::getvar('password','RAW');
			
			$loginResult = Access::handleLogin($email, $password);

			if($loginResult->hasMessage()) {
				// login failed, show login form
				$this->queue_message(...$loginResult->toQueueMessageArgsArray());
			}

			if ($this->isAdmin()) {
				// force switch to admin template login 
				$template = $this->get_admin_template();
			}
			include_once (Template::getTemplatePath($template, true) . "/login.php");
			if($_ENV["dev_banner"]==="true") {
				echo $this->render_dev_banner();
			}
		} else {
			if ($this->isAdmin()) {
				//check the users access rights
				if (!Access::can_access(Access::getAdminAccessRule($this->uri_segments[0] ?? ""))) {
					if(CMS::Instance()->user && CMS::Instance()->user->groups && (CMS::Instance()->user->is_member_of(1) || CMS::Instance()->user->is_member_of(2))) {
						$this->queue_message('You do not have access to this page','danger', $_ENV["uripath"] . "/admin");
					} else {
						$this->queue_message('You do not have access to this page','danger', $_ENV["uripath"] . "/");
					}
				}

				ob_start();
				$template = $this->get_admin_template();
				include_once(Template::getTemplatePath($template, true) . "/index.php");
				
				// save page contents to CMS
				$this->page_contents = ob_get_contents();
				ob_end_clean(); // clear and stop buffering

				//add head entries
				$cms_head = "";
				foreach ($this->head_entries as $he) {
					$cms_head .= $he;
				}
				if(!str_contains($this->page_contents, "<!--CMSHEAD-->")) {
					throw new Exception("Failed to Load Head");
				}
				$this->page_contents = str_replace("<!--CMSHEAD-->", $cms_head, $this->page_contents);

				// perform content filtering / plugins on CMS::page_contents;
				$this->page_contents = Hook::execute_hook_filters('content_ready_admin', $this->page_contents);
				if($_ENV["dev_banner"]==="true") {
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
				$domainIndex = CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
				
				if (sizeof($this->uri_segments)==0) {
					// TODO: work with user selected HOME page
					$alias = 'home';
					$page = DB::fetch("SELECT * FROM pages WHERE parent=-1 AND alias='home' AND state>0 AND domain=?", $domainIndex);
				}
				else {
					$parent = -1; // start with root
					$this->uri_path_segments = [];
					while ($this->uri_segments) {
						$result = DB::fetch(
							"SELECT * FROM pages WHERE parent=? AND alias=? AND state > 0 AND domain=?",
							[$parent, $this->uri_segments[0], $domainIndex]
						);
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
					// magic alias of 'home' used for now - todo: make configurable via config option in future
					// home page has to have a controller and alias of home and at root of pages and published
					$alias = 'home'; // see magic alias comment above for this and query below
					$page = DB::fetch("SELECT * FROM pages WHERE content_type>0 AND parent=-1 AND alias='home' AND state>0 AND domain=?", $domainIndex);
					if (!$page) {
						// have params, but no page found and/or home has no controller to consume params
						$this->raise_404();
					}
				}
				if ($_ENV["debugwarnings"]==="true") {
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
					$this->queue_message('You do not have access to this page','danger', $_ENV["uripath"] . $redirect_uri);
				}

				// front end buffering for plugin functionality
				ob_start();
				include_once (Template::getTemplatePath($this->page->template->folder) . "/index.php");
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
				if(!str_contains($this->page_contents, "<!--CMSHEAD-->")) {
					throw new Exception("Failed to Load Head");
				}
				$this->page_contents = str_replace("<!--CMSHEAD-->", $cms_head, $this->page_contents);
				if($_ENV["dev_banner"]==="true") {
					$this->page_contents .= $this->render_dev_banner();
				}
				// output final content
				echo $this->page_contents;

				// create full page cache if needed
				// only if no messages in queue and user is not logged in and not a core controller and not debugging currently
				// @phpstan-ignore-next-line
				if ( $_ENV["debugwarnings"]!=="true" && $_ENV["debug"]!=="true" && $_ENV["cache_enabled"]==="true" && !($_SESSION['flash_messages'] ?? null) && !$this->user->id  && !$this->core_controller) {
					$this->cache->create_cache($_SERVER['REQUEST_URI'], 'url', $this->page_contents);
				}
			}	
			
		}
	}
}