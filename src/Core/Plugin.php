<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \stdClass;
Use \Exception;

class Plugin {
	public $id;
	public $title;
	public $state;
    public $options;
    public $location;
    public $description;
	public $form;

	private static $pluginRegistry = [
		"AutoYear" => [
			"path" => __DIR__ . '/../Plugins/AutoYear',
			"class" => "HoltBosse\\Alba\\Plugins\\AutoYear\\AutoYear"
		],
		"FrontEndEditButton" => [
			"path" => __DIR__ . '/../Plugins/FrontEndEditButton',
			"class" => "HoltBosse\\Alba\\Plugins\\FrontEndEditButton\\FrontEndEditButton"
		],
		"GoogleLoginJwt" => [
			"path" => __DIR__ . '/../Plugins/GoogleLoginJwt',
			"class" => "HoltBosse\\Alba\\Plugins\\GoogleLoginJwt\\GoogleLoginJwt"
		],
		"UserVerify"=> [
			"path" => __DIR__ . '/../Plugins/UserVerify',
			"class" => "HoltBosse\\Alba\\Plugins\\UserVerify\\UserVerify"
		],
		"Widget" => [
			"path" => __DIR__ . '/../Plugins/Widget',
			"class" => "HoltBosse\\Alba\\Plugins\\Widget\\Widget"
		],
	];

    public function __construct($plugin_info) {
        // $plugin_info should be object containing select * info from plugins table for this plugin
        $this->state = $plugin_info->state;
        $this->title = $plugin_info->title;
        $this->description = $plugin_info->description;
        $this->location = $plugin_info->location;
        $this->id = $plugin_info->id;
        $this->options = json_decode((string)$plugin_info->options);
        $this->init();
    }

	public static function registerPlugin(string $pluginName, string $pluginPath, string $pluginClass): bool {
		if (!isset(self::$pluginRegistry[$pluginName])) {
			self::$pluginRegistry[$pluginName] = [
				"path" => $pluginPath,
				"class" => $pluginClass
			];

			return true;
		}

		return false;
	}

	public static function registerPluginDir(string $pluginDirPath, string $pluginClassBase): void {
		foreach(glob($pluginDirPath . '/*') as $file) {
			Plugin::registerPlugin(
				basename($file, '.php'),
				$file,
				$pluginClassBase . basename($file, '.php') . "\\" . basename($file, '.php')
			);
		}
	}

	public static function getPluginPath(string $pluginName): ?string {
		if (isset(self::$pluginRegistry[$pluginName])) {
			return realpath(self::$pluginRegistry[$pluginName]['path']);
		}

		return null;
	}

	public static function getPluginClass(string $pluginName): ?string {
		if (isset(self::$pluginRegistry[$pluginName])) {
			return self::$pluginRegistry[$pluginName]['class'];
		}

		return null;
	}

	public static function getPluginNames(): array {
		return array_keys(self::$pluginRegistry);
	}
    
    public static function get_all_plugins() {
		return DB::fetchAll('select * from plugins where state > -1');
    }


	public function get_option($option_name) {
		foreach ($this->options as $option) {
            if ($option->name==$option_name) {
				return $option->value;
			}
		}
		return false;
    } 
    
    public function init() {
        throw new Exception('Default plugin init called - should never happen');
    }

    public function execute_action(...$args) {
        throw new Exception('Default plugin execute_action called - should never happen');
    }

    public function execute_filter($data, ...$args) {
        throw new Exception('Default plugin execute_filter called - should never happen');
    }

	public static function get_plugin_title ($id) {
		return DB::fetch("select title from plugins where id=?", [$id])->title;
	}

	public function load($id) {
		$info = DB::fetch('SELECT * FROM plugins WHERE id=' . $id);
		$this->id = $info->id;
		$this->title = $info->title;
		$this->state = $info->state;
        $this->description = $info->description;
        $this->location = $info->location;
		$this->options = json_decode($info->options);
	}


	public function save($plugin_options_form) {
		// update this object with submitted and validated form info
		$this->options = [];
		foreach ($plugin_options_form->fields as $option) {
			$obj = new stdClass();
			$obj->name = $option->name;
			$obj->value = $option->default;
			//$obj->{$option->name} = $option->default;
			$this->options[] = $obj;
		}
		$options_json = json_encode($this->options);

		if ($this->id) {
			// update
			$result = DB::exec("update plugins set options=? where id=?", [$options_json, $this->id]);
			
			if ($result) {
				$msg = "Plugin <a href='" . $_ENV["uripath"] . "/admin/plugins/edit/{$this->id}'>{$this->title}</a> updated";	
				CMS::Instance()->queue_message($msg, 'success', $_ENV["uripath"] . '/admin/plugins/show');
			}
			else {
				CMS::Instance()->queue_message('Plugin failed to save','danger',$_ENV["uripath"] . $_SERVER['REQUEST_URI']);	
			}
        }
        else {
            throw new Exception('Unknown plugin');
        }
	}
}