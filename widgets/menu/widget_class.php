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

	public function render_menu($tree) {
		if ($tree->parent!==null) {
			// display all nodes except root
			$page = new Page();
			$page->load_from_id($tree->value);
			if ($page->load_from_id($tree->value)) {
				$url = $page->get_url();
				$target = '';
				$is_anchor = true;
				$classList="";
				if ($tree->value==CMS::Instance()->page->id) {
					$classList.=" current ";
				}
				if ($tree->children) {
					$classList.=" parent ";
				}

				$content_type_title = Content::get_content_type_title($page->content_type);

				if ($content_type_title=="External Link") {
					$page->view_configuration_object = json_decode($page->view_configuration);
					$view_config = $page->view_configuration_object;
					$content_id = Content::get_config_value ($view_config, 'content_id');
					$content_item = Content::get_all_content(false, $page->content_type, $content_id)[0]; // false 1st param is ordering field
					//CMS::pprint_r ($content_item);
					$url = $content_item->f_url;
					if ($content_item->f_newtab==1) {
						$target = "_blank";
						$classList.=" external_link ";
					}
				}
				if ($content_type_title=="Menu Heading") {
					$classList.=" menu_heading ";
					$is_anchor = false;
				}

				
				echo "<li class='{$classList}'>";
				if ($is_anchor) {
					echo "<a target={$target} class='page_id_{$tree->value}' href='{$url}'>" . $page->title . "</a>";
				}
				else {
					echo $page->title;
				}
				if ($tree->children) {
					echo "<ul>";
					foreach ($tree->children as $child) {
						$this->render_menu($child);
					}
					echo "</ul>";
				}
				echo "</li>";
			}
			else {
				// no page found - errant menu item
				echo "<li class='menu_error'>" . $tree->text . "</li>";
			}
		}
		else {
			// render roots children - aka home etc...
			if ($tree->children) {
				echo "<ul>";
				foreach ($tree->children as $child) {
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
		$this_menu_structure = json_decode(html_entity_decode($this->options[0]->value));
		//echo json_last_error_msg();
		//CMS::pprint_r ($this_menu_structure);
		//echo "<code>Menu Widget</code>";
		echo "<div class='tree_wrap'>";
		$this->render_menu($this_menu_structure);
		echo "</div>";
	}
}