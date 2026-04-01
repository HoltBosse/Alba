<?php
namespace HoltBosse\Alba\Core;

Use \stdClass;
Use \Exception;
Use HoltBosse\Form\{Form, Input};

/**
 * Plugin - WordPress plugin API wrapper
 * Provides wrapper methods for WordPress plugin functions
 */
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
        // $plugin_info should be object containing plugin info
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
    
	/**
	 * Get all plugins
	 * Wrapper for WordPress get_plugins()
	 */
	// @phpstan-ignore missingType.iterableValue
    public static function get_all_plugins(): array {
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return get_plugins();
    }

	/**
	 * Check if a plugin is active
	 * Wrapper for WordPress is_plugin_active()
	 */
	public static function is_plugin_active(string $plugin_file): bool {
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active($plugin_file);
	}

	/**
	 * Activate a plugin
	 * Wrapper for WordPress activate_plugin()
	 */
	public static function activate_plugin(string $plugin_file): void {
		if (!function_exists('activate_plugin')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		activate_plugin($plugin_file);
	}

	/**
	 * Deactivate a plugin
	 * Wrapper for WordPress deactivate_plugins()
	 */
	public static function deactivate_plugin(string $plugin_file): void {
		if (!function_exists('deactivate_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		deactivate_plugins($plugin_file);
	}

	/**
	 * Get plugin data
	 * Wrapper for WordPress get_plugin_data()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_plugin_data(string $plugin_file): array {
		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return get_plugin_data($plugin_file);
	}

	public function get_option(string $option_name): mixed {
		// Use WordPress get_option for plugin specific options
		$option_key = 'alba_plugin_' . $this->id . '_' . $option_name;
		return get_option($option_key, false);
    } 

	public function set_option(string $option_name, mixed $value): bool {
		// Use WordPress update_option for plugin specific options
		$option_key = 'alba_plugin_' . $this->id . '_' . $option_name;
		return update_option($option_key, $value);
	}

	public function delete_option(string $option_name): bool {
		// Use WordPress delete_option for plugin specific options
		$option_key = 'alba_plugin_' . $this->id . '_' . $option_name;
		return delete_option($option_key);
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
		// Get plugin title from WordPress option
		return get_option('alba_plugin_' . $id . '_title', 'Unknown Plugin');
	}

	public function load(int $id): void {
		// Load plugin data from WordPress options
		$this->id = $id;
		$this->title = get_option('alba_plugin_' . $id . '_title', '');
		$this->state = (int) get_option('alba_plugin_' . $id . '_state', 0);
        $this->description = get_option('alba_plugin_' . $id . '_description', '');
        $this->location = get_option('alba_plugin_' . $id . '_location', '');
		$options_json = get_option('alba_plugin_' . $id . '_options', '[]');
		$this->options = json_decode($options_json);
	}


	public function save(Form $plugin_options_form): void {
		// Update plugin options using WordPress update_option
		$this->options = [];
		foreach ($plugin_options_form->fields as $option) {
			$obj = new stdClass();
			$obj->name = $option->name;
			$obj->value = $option->default;
			$this->options[] = $obj;
			
			// Save each option individually for easier retrieval
			$this->set_option($option->name, $option->default);
		}
		$options_json = json_encode($this->options);

		if ($this->id) {
			// Save all options as JSON as well
			update_option('alba_plugin_' . $this->id . '_options', $options_json);
			
			$msg = "Plugin <a href='" . esc_url($_ENV["uripath"] . "/admin/plugins/edit/{$this->id}") . "'>" . esc_html($this->title) . "</a> updated";	
			CMS::Instance()->queue_message($msg, 'success', $_ENV["uripath"] . '/admin/plugins/show');
        }
        else {
            throw new Exception('Unknown plugin');
        }
	}
}