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
				$classList="";
				if ($tree->value==CMS::Instance()->page->id) {
					$classList=" current ";
				}
				echo "<li class='{$classList}'><a class='page_id_{$tree->value}' href='{$url}'>" . $page->title . "</a></li>";
			}
			else {
				// no page found - errant menu item
				echo "<li class='menu_error'>" . $tree->text . "</li>";
			}
		}
		// traverse children
		if ($tree->children) {
			echo "<ul>";
			foreach ($tree->children as $child) {
				$this->render_menu($child);
			}
			echo "</ul>";
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