<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Widget_menu extends Widget {

	public function get_page($id) {
		$query = "select * from pages where id=?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($id));
		$page = $stmt->fetch();
		return $page;
	}

	public function custom_save() {
		$menu_designer_config_json = Input::getvar('menu_designer_config','STRING');
		$obj = new stdClass();
		$obj->name = "menu_designer_config";
		$obj->value = $menu_designer_config_json;
		// add to this widget objects already created options array created during save base class function call
		$this->options[] = $obj;
	}

	public function render_custom_backend() {
		$all_pages = Page::get_all_pages_by_depth();
		?>
		<section class='flex gap' id='menu_designer_wrap'>

			<input type="hidden" type="text" name="menu_designer_config" id="menu_designer_config" value=""/>

			<aside id='menu_designer_page_listing'>
				<h3 class='is-5 is-heading title'>Add Page(s)</h3><hr>
				<div class='field box'>
					<?php foreach ($all_pages as $page):?>
						<label class='checkbox'>
							<input type='checkbox' value='<?php echo $page->id;?>'>
							<?php for ($n=0; $n<$page->depth; $n++) {
								echo "&nbsp;-&nbsp;";
							}
							echo "<span class='page_title'>" . $page->title . "</span>"; ?>
						</label>
					<?php endforeach; ?>
					<button type='button' class='button btn is-success' id='menu_desiger_add_pages'>Add</button>
				</div>
			</aside>

			<aside id='menu_designer_misc'>
				<h3 class='is-5 is-heading title'>Add Links & Headings</h3><hr>
				<fieldset class='box'>
					<legend class='box subtitle is-5'>Heading Text</legend>
					<div class="field">
						<label class="label">Heading Text</label>
						<div class="control">
							<input id="heading_text" name="heading_text" class="input" type="text"  placeholder="Heading Text">
						</div>
					</div>
					<button type='button' class='button btn is-success noteditonly' id='menu_desiger_add_heading'>Add Heading</button>
					<button type='button' class='button btn is-warning editonly' id='menu_desiger_edit_heading'>Update Heading</button>
				</fieldset>
				<fieldset  class='box'>
					<legend  class='box subtitle is-5'>Link</legend>
					<div class="field">
						<label class="label">Link Text</label>
						<div class="control">
							<input id='link_text' name="link_textl" class="input" type="text"  placeholder="Text">
						</div>
					</div>
					<div class="field">
						<label class="label">URL</label>
						<div class="control">
							<input id='link_url' name="link_url" class="input" type="text"  placeholder="URL">
						</div>
					</div>
					<div class='field'>
						<label class="checkbox">
							<input id='link_newtab' name='link_newtab' type="checkbox">
							Open in new tab/window?
						</label>
					</div>
					<button type='button' class='button btn is-success noteditonly' id='menu_desiger_add_link'>Add Link</button>
					<button type='button' class='button btn is-warning editonly' id='menu_desiger_edit_link'>Update Link</button>
				</fieldset>
			</aside>

			<aside id='menu_designer_tree_wrap'>
				<h3 class='is-5 is-heading title'>Menu Designer</h3>
				<p>Drag & Drop to re-arrange. Drop to the right hand edge of a menu item to add it as a child.</p>
				<p><em>Changes here will not be reflected on the front-end until the widget has been saved.</em></p>
				<hr>
				<div id="menu_designer_tree" class="menu_node" >
				</div>
			</aside>

		</section> 

		
		<?php
		
		// inject saved menu config into javscript - or start with default if none loaded

		$menu_designer_config_json = $this->get_option_value('menu_designer_config');

		if ($menu_designer_config_json!=="{}" && $this->get_option_value('menu_designer_config')) {
			echo "<script>";
			echo "// got menu config \n";
			echo "var menu_designer_config = " . html_entity_decode ($menu_designer_config_json) . ";";
		}
		else {
		?>
		<script>
		var menu_designer_config = {
			"title":"root",
			"id":"root",
			"type":"root",
			"children":[],
			"info":{}
		}; 
		</script>
		<?php } ?>
		</script>

		<script src='<?php echo Config::$uripath;?>/admin/js/menu_widget_admin.js'></script>
		<?php
	}


	public function render_menu($node) {
		//CMS::pprint_r ($node);
		if ($node->type!=='root') {
			$title = "";
			$target = '';
			$is_anchor = true;
			$classList.="";
			$is_anchor = true; // set to false for headers or other non-links

			if ($node->children) {
				$classList.=" parent ";
			}

			// display all nodes except root
			if ($node->type=='page') {
				$page = new Page();
				if ($page->load_from_id($node->info->page_id)) {
					$url = $page->get_url();
					$classList.=" page ";
					if ($tree->value==CMS::Instance()->page->id) {
						$classList.=" current ";
					}
					if ($tree->children) {
						$classList.=" parent ";
					}
					$title = $page->title;
					// following can be used to doing clever things with different page types other than basic articles
					$content_type_title = Content::get_content_type_title($page->content_type);
				}
				else {
					// error getting page - deleted? (should still show if unpublished - link just will fail)
					$title = "Error finding " . $node->title;
					$classList.=" page_error ";
				}
			}
			if ($node->type=="link") {
				$title = $node->title;
				$url = $node->info->url;
				if ($node->info->newtab) {
					$target = "_blank";
					$classList.=" external_link ";
				}
			}
			if ($node->type=="heading") {
				$title = $node->title;
				$classList.=" menu_heading ";
				$is_anchor = false;
			}

				
			echo "<li class='{$classList}'>";
			if ($is_anchor) {
				echo "<a target='{$target}' class='page_id_{$page->id}' href='{$url}'>" . $title . "</a>";
			}
			else {
				echo $node->title;
			}
			if ($node->children) {
				echo "<ul>";
				foreach ($node->children as $child) {
					$this->render_menu($child);
				}
				echo "</ul>";
			}
		}
		else {
			// render roots children - aka home etc...
			if ($node->children) {
				echo "<ul>";
				foreach ($node->children as $child) {
					$this->render_menu($child);
				}
				echo "</ul>";
			}
		}
	}

	public function render() {
		//CMS::pprint_r ($this);
		//echo "<hr>";
		//CMS::pprint_r ($this->options[0]->value);
		//$this_menu_structure = json_decode(html_entity_decode($this->options[0]->value));
		$this_menu_structure = json_decode(html_entity_decode($this->get_option_value('menu_designer_config')));
		//CMS::pprint_r ($this->get_option_value('menu_designer_config'));
		
		//echo json_last_error_msg();
		//CMS::pprint_r ($this_menu_structure);
		//echo "<code>Menu Widget</code>";
		echo "<div class='tree_wrap'>";
		$this->render_menu($this_menu_structure);
		echo "</div>";
	}
}