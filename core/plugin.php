
<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin {
	public $id;
	public $title;
	public $state;
    public $options;
    public $location;
    public $description;

    private function __construct($plugin_info) {
        // $plugin_info should be object containing select * info from plugins table for this plugin
        $this->state = $plugin_info->state;
        $this->title = $plugin_info->title;
        $this->options = $plugin_info->options;
    }
    
    public static function get_all_plugins() {
		return CMS::Instance()->pdo->query('select * from plugins where state>-1')->fetchAll();
    }


	public function get_option($option_name) {
		foreach ($this->options as $option) {
			if (property_exists($option, $option_name)) {
				return $option->$option_name;
			}
		}
		return false;
    } 
    
    public function init() {
        CMS::show_error('Default plugin init called - should never happen');
    }

	public static function get_plugin_title ($id) {
		$query = "select title from plugins where id=?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($id));
		return $stmt->fetch()->title;
	}

	public function show_admin_form() {
		$this->form = new Form();
		$this->form->load_json(CMSPATH . "/plugins/");
	}

	public function load($id) {
		$info = CMS::Instance()->pdo->query('select * from plugins where id=' . $id)->fetch();
		$this->id = $info->id;
		$this->title = $info->title;
		$this->state = $info->state;
        $this->description = $info->description;
        $this->location = $info->location;
		$this->options = json_decode($info->options);
	}


	public function save($required_details_form, $plugin_options_form) {
		// update this object with submitted and validated form info
		$this->title = $required_details_form->get_field_by_name('title')->default;
		$this->state = $required_details_form->get_field_by_name('state')->default;
		$this->description = $required_details_form->get_field_by_name('description')->default;
		$this->options = array();
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
			$query = "update plugins set state=?, options=? where id=?";
			$stmt = CMS::Instance()->pdo->prepare($query);
			$params = array($this->state, $this->title, $this->note, $options_json, $this->id) ;
			$result = $stmt->execute( $params );
			
			if ($result) {
				CMS::Instance()->queue_message('Plugin options updated','success',Config::$uripath . '/admin/plugins/show');	
			}
			else {
				CMS::Instance()->queue_message('Plugin failed to save','danger',Config::$uripath . $_SERVER['REQUEST_URI']);	
			}
        }
        else {
            CMS::show_error('Unknown plugin');
        }
	}
}