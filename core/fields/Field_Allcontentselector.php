<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Allcontentselector extends Field {

	public $select_options;

	function __construct($id=1) {
		$this->id = "content_id_" . $id;
		$this->name = "content_id_" . $id;
		$this->select_options=[];
		$this->default = $id;
	}

	public function display() {
		$required="";
		if (ADMINPATH) {
			// only show all options on admin - slow and potentially ambiguous titles
			$options_all_articles = CMS::Instance()->pdo->query("select c.*, t.title as content_type_title from content c, content_types t where c.content_type = t.id and c.state=1 order by id ASC")->fetchAll();
		}
		if ($this->required) {$required=" required ";}
		echo "<div class='field'>";
			echo "<label class='label'>" . $this->label . "</label>";
			echo "<div class='control'>";
				echo "<div class='select'>";
					echo "<select {$required} id='{$this->id}' name='{$this->name}'>";
						if ($this->required) {
							echo "<option value='' >{$this->label}</option>";
						}
						foreach ($options_all_articles as $tag) {
							if ($tag->state==1) {
								$selected = "";
								if ($tag->id == $this->default) { $selected="selected";}
								echo "<option {$selected} value='{$tag->id}'>{$tag->title} ({$tag->content_type_title})</option>";
							}
						}
					echo "</select>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}
	}


	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_Contentselector = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_Contentselector.designer_template = `
			<div class="field">
				<h2 class='heading title'>Text Field</h2>	

				<label class="label">Label</label>
				<div class="control has-icons-left has-icons-right">
					<input required name="label" class="input iss-success" type="label" placeholder="Label" value="">
				</div>

				<label class="label">Required</label>
				<div class="control has-icons-left has-icons-right">
					<input name="required" class="checkbox iss-success" type="checkbox"  value="">
				</div>
			</div>`;
		</script>
		<?php 
	}

	public function designer_display() {

	}

	public function get_friendly_value() {
		$stmt = CMS::Instance()->pdo->prepare('select title from content where id=?');
		/* $query = "select p.id as id, p.title as title, c1.content as jobnumber, c2.content as initials 
		from content p, content_fields c1, content_fields c2 
		WHERE c1.content_id = p.id 
		and c1.name = 'number' 
		and c2.content_id = (select content from content_fields where name='client' and content_id=p.id) 
		and c2.name = 'initials' 
		AND p.id = ? ";
		$stmt = CMS::Instance()->pdo->prepare($query); */
		$stmt->execute(array($this->default));
		$result = $stmt->fetch();
		//CMS::pprint_r ($this);
		//$friendly = $result->initials . $result->jobnumber . " - " . $result->title;
		$friendly = $result->title;
		return $friendly;
	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'NUMBER';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->select_options = $config->select_options ?? [];
		$this->default = $config->default ?? '';
		$this->type = $config->type ?? 'error!!!';
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}