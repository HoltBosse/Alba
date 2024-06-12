<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_UserPicker extends Field {

	public $select_options;

	function __construct($tagid="") {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->default = "";
		$this->type = "UserPicker";
	}

	

	public function display() {
		//CMS::pprint_r ($this);
		$this->array_values = json_decode ($this->default);
		$required="";
		$all_users=DB::fetchAll('select * from users where state=1 ORDER BY username  ASC');

		if ($this->required) {$required=" required ";}
		// if id needs to be unique for scripting purposes, make sure replacement text inserted
		// this will be replaced during repeatable template literal js injection when adding new
		// repeatable form item
		if ($this->in_repeatable_form===null) {
			$repeatable_id_suffix='';
		}
		else {
			$repeatable_id_suffix='{{repeatable_id_suffix}}';
		}
		/* CMS::pprint_r ($this->array_values);
		CMS::pprint_r ($all_users); */

		if ($this->multiple) {
			$is_multiple="is-multiple";
			$multiple=" multiple ";
		}
        echo "<style>.field_id_livestreamuser .ss-main{min-width: 60ch;}</style>";
		echo "<div class='field'>";
			echo "<label class='label'>" . $this->label . "</label>";
			echo "<div class='control'>";
				echo "<div class='select {$is_multiple}'>";
					echo "<select class='{$is_multiple}' {$multiple} {$required} id='{$this->id}{$repeatable_id_suffix}' {$this->get_rendered_name($this->multiple)}>";
						
							echo "<option value='' >{$this->label}</option>";
						
						foreach ($all_users as $item) {
							$selected="";
							if (is_array($this->array_values) && in_array($item->id, $this->array_values)) {
								$selected=" selected ";
							}
                            if (is_numeric($this->array_values) && ($item->id == $this->array_values)) {
                                $selected=" selected ";
                            }
							echo "<option {$selected} value='{$item->id}'>{$item->username} ($item->email)</option>";
							
						}
					echo "</select>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}
		
        if ($this->in_repeatable_form===null) {
            echo "<script>new SlimSelect({ select: '#{$this->id}' });</script>"; 
        }
        else {
            // also inject id_suffix to be replace at injection time
            echo "<script>new SlimSelect({ select: '#{$this->id}{$repeatable_id_suffix}' });</script>"; 
        }
		
    }


	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'ARRAYOFINT';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->select_options = $config->select_options ?? [];
		$this->default = $config->default ?? "";
		$this->type = $config->type ?? 'error!!!';
		$this->wrapclass = $config->wrapclass ?? "";
		$this->multiple = $config->multiple ?? false;
	}

	public function get_friendly_value($helpful_info) {
		$stmt = CMS::Instance()->pdo->prepare('SELECT title from content where id=?');
		$stmt->execute(array($this->default));
		return $stmt->fetch()->title;
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}