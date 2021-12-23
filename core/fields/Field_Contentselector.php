<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Contentselector extends Field {

	public $select_options;

	function __construct($content_type=1) {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->content_type=$content_type;
		$this->tags=[];
	}

	public function display() {
		$required="";
		if ($this->content_type) {
			if (!is_numeric($this->content_type)) {
				// content type denoted by content controller location - this is a unique safe folder name
				// e.g. basic_article
				$this->content_type = Content::get_content_type_id($this->content_type);
			}
			if ($this->content_type && is_numeric($this->content_type)) {
				if (!$this->tags) {
					// default order is alphabetical
					$options_all_articles = CMS::Instance()->pdo->query("select * from content where content_type={$this->content_type} and state=1 order by title ASC")->fetchAll();
				}
				else {
					$tags_csv = "'".implode("','", $this->tags)."'";
					$query = "select c.* from content c where c.content_type={$this->content_type} and c.state=1 ";
					$query .= " and c.id in (";
						$query .= " select tc.content_id from tagged tc where tc.content_type_id={$this->content_type} and tc.tag_id in (";
							$query .= "select t.id from tags t where t.state>0 and t.alias in ($tags_csv)";
						$query .= ")";
					$query .= ") order by c.title ASC";
					$stmt = CMS::Instance()->pdo->query($query);
					$options_all_articles = $stmt->fetchAll();
				}
			}
		}
		if (!$options_all_articles) {
			// content type was not able to be established
			if (Config::$debug) {
				echo "<h5>Error determining content type</h5>";
				return false;
			}
		}
		if ($this->required) {$required=" required ";}
		echo "<div class='field'>";
			echo "<label class='label'>" . $this->label . "</label>";
			echo "<div class='control'>";
				echo "<div class='select'>";
					echo "<select {$required} id='{$this->id}' {$this->get_rendered_name()}>";
						if ($this->required) {
							echo "<option value='' >{$this->label}</option>";
						}
						elseif ($this->empty_string) {
							// not required, but we need a 0 value top option to signify nothing
							echo "<option value='0' >{$this->empty_string}</option>";
						}
						foreach ($options_all_articles as $tag) {
							if ($tag->state==1) {
								$selected = "";
								if ($tag->id == $this->default) { $selected="selected";}
								echo "<option {$selected} value='{$tag->id}'>{$tag->title}</option>";
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
		$this->content_type = $config->content_type ?? false;
		$this->empty_string = $config->empty_string ?? '';
		$this->tags = $config->tags ?? [];
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}