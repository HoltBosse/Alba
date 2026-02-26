<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \stdClass;
Use \Exception;
Use HoltBosse\Form\{Form, Input};

class Plugin {
	public int $id;
	public string $title;
	public int $state;
	// @phpstan-ignore missingType.iterableValue
    public ?array $options;
    public string $location;
    public string $description;
	public ?Form $form;

	// @phpstan-ignore missingType.iterableValue
	private static array $pluginRegistry = [
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

    public function __construct(stdClass $plugin_info) {
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
		foreach(File::glob($pluginDirPath . '/*') as $file) {
			Plugin::registerPlugin(
				basename($file, '.php'),
				$file,
				$pluginClassBase . basename($file, '.php') . "\\" . basename($file, '.php')
			);
		}
	}

	public static function getPluginPath(string $pluginName): ?string {
		if (isset(self::$pluginRegistry[$pluginName])) {
			return File::realpath(self::$pluginRegistry[$pluginName]['path']);
		}

		return null;
	}

	public static function getPluginClass(string $pluginName): ?string {
		if (isset(self::$pluginRegistry[$pluginName])) {
			return self::$pluginRegistry[$pluginName]['class'];
		}

		return null;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function getPluginNames(): array {
		return array_keys(self::$pluginRegistry);
	}
    
	// @phpstan-ignore missingType.iterableValue
    public static function get_all_plugins(): array {
		return DB::fetchAll('select * from plugins where state > -1');
    }


	public function get_option(string $option_name): mixed {
		foreach ($this->options as $option) {
            if ($option->name==$option_name) {
				return $option->value;
			}
		}
		return false;
    } 
    
    public function init(): void {
        throw new Exception('Default plugin init called - should never happen');
    }

    public function execute_action(mixed ...$args): void {
        throw new Exception('Default plugin execute_action called - should never happen');
    }

    public function execute_filter(mixed $data, mixed ...$args): void {
        throw new Exception('Default plugin execute_filter called - should never happen');
    }

	public static function get_plugin_title(int $id): string {
		return DB::fetch("SELECT title from plugins where id=?", [$id])->title;
	}

	public function load(int $id): void {
		$info = DB::fetch('SELECT * FROM plugins WHERE id=?', [$id]);
		$this->id = $info->id;
		$this->title = $info->title;
		$this->state = $info->state;
        $this->description = $info->description;
        $this->location = $info->location;
		$this->options = json_decode($info->options);
	}


	public function save(Form $plugin_options_form): void {
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