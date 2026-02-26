<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Form, Input};
Use \PDOException;
Use Respect\Validation\Validator as v;
Use HoltBosse\Form\Field;

class Page {
	public int $id;
	public int $state;
	public string $title;
	public string $alias;
	public int $template_id;
	public ?object $template;
	public ?int $parent;
	public ?int $content_type;
	public ?int $view;
	public ?string $updated;
	public ?string $view_configuration;
	public ?string $page_options; // json string from db / or serialized from form submission
	public ?Form $page_options_form;
	public ?int $domain;
	public ?string $controller = null;
	public mixed $view_configuration_object = null;

	public function __construct() {
		$this->id = 0;
		$this->state = 1;
		$this->title = "";
		$this->alias = "";
		$this->template_id = 1;
		$this->template = null;
		$this->parent = null;
		$this->updated = date('Y-m-d H:i:s');
		$this->content_type = null;
		$this->view = null;
		$this->view_configuration = null;
		$this->page_options_form = new Form(realpath(__DIR__ . "/../admin/controllers/pages/views/edit/page_options.json"));
		$this->page_options = null;
		$this->domain = CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
	}

	public function get_url(): string {
		// TODO: save url in new column on page save/update
		$segments = [$this->alias];
		$parent = $this->parent;
		if ($this->alias=='home' && $parent<0) {
			return $_ENV["uripath"] . "/"; 
		}
		while ($parent>=0) {
			$result = DB::fetch("select parent,alias from pages where id=?", [$parent]);
			$parent = $result->parent;
			array_unshift ($segments, $result->alias);
			//$segments[] = $result->alias;
		}
		$url = $_ENV["uripath"] . '/' . implode('/',$segments);
		return $url;
	}

	public function get_page_option_value(string $option_name): mixed {
		return $this->page_options_form->getFieldByName($option_name)->default;
	}

	public function set_page_option_value(string $option_name, mixed $value): bool {
		$this->page_options_form->getFieldByName($option_name)->default = $value;
		return true;
	}
	
	public static function get_page_depth(int $id): int {
		$parent_root = false;
		$parent=$id;
		$depth = 0;
		while (!$parent_root) {
			$result = DB::fetch("SELECT parent,alias FROM pages WHERE id=?", [$parent]);
			$parent = $result->parent;
			$depth++;
			if ($parent=="-1") {
				$parent_root=true;
			}
		}
		return $depth;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_all_pages(): array {
		$result = DB::fetchAll("SELECT * FROM pages WHERE state>-1");
		return $result;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_all_pages_by_depth(int $parent=-1, int $depth=-1): array {
		$depth = $depth+1;
		$result=[];
		$children = DB::fetchAll("SELECT * FROM pages WHERE state>-1 AND parent=? ORDER BY domain", [$parent]);
		foreach ($children as $child) {
			$child->depth = $depth;
			$result[] = $child;
			$result = array_merge ($result, Page::get_all_pages_by_depth($child->id, $depth));
		}
		return $result;
	}

	// @phpstan-ignore-next-line missingType.iterableValue
	public static function get_pages_from_id_array(array $id_array): array {
		$in_string = implode(',',$id_array);
		$query = "select * from pages where id in ({$in_string})";
		$result = DB::fetchAll($query);
		return  $result;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function has_overrides (int $page): array {
		$w = DB::fetchAll("select widgets from page_widget_overrides where page_id=? and (widgets is not null and widgets <> '')", [$page]);
		//CMS::pprint_r ($w);
		return $w;
	}

	public function load_from_post(): bool {
		$this->title = Input::getvar('title', v::StringVal());
		$this->state = Input::getvar('state',v::IntVal(), 1);
		$this->template_id = Input::getvar('template',v::IntVal());
		$this->alias = Input::getvar('alias',v::StringVal());
		if (!$this->alias) {
			$this->alias = Input::stringURLSafe($this->title);
		}
		$this->parent = Input::getvar('parent',v::IntVal());
		$this->content_type = Input::getvar('content_type',v::IntVal());
		$this->view = Input::getvar('content_type_controller_view',v::IntVal());

		// OLD: view_options now handles by options_form.json in view
		$this->view_configuration = json_encode(Input::getvar('view_options',v::arrayType()));
		// TODO: load from options_form
		// e.g. $options_form = new Form(form location);
		// $options_form->setFromSubmit();
		// validate
		// jsonify
		// save as $this->view_configuration
		
		$this->id = Input::getvar('id',v::IntVal());

		$this->domain = Input::getvar("domain", v::StringVal());

		$this->page_options_form->setFromSubmit();
		return true;
	}

	public function load_from_id(int $id): bool {
		$result = DB::fetch("select * from pages where id=?", [$id] );
		if ($result) {
			$this->id = $result->id;
			$this->state = $result->state;
			$this->title = $result->title;
			$this->alias = $result->alias;
			$this->template_id = $result->template;
			$this->template = new Template($this->template_id);
			$this->parent = $result->parent;
			$this->updated = $result->updated;
			$this->content_type = $result->content_type;
			$this->view = $result->content_view;
			$this->view_configuration = $result->content_view_configuration;
			$this->page_options = $result->page_options;
			$this->page_options_form->deserializeJson($this->page_options); // json from db pulled into form object in page
			$this->domain = $result->domain;
			return true;
		}
		else {
			return false;
		}
	}


	public function load_from_alias(string $alias): bool {
		$result = DB::fetch("select * from pages where alias=?", [$alias]);
		if ($result) {
			$this->id = $result->id;
			$this->state = $result->state;
			$this->title = $result->title;
			$this->alias = $result->alias;
			$this->template_id = $result->template;
			$this->template = new Template($this->template_id);
			$this->parent = $result->parent;
			$this->updated = $result->updated;
			$this->content_type = $result->content_type;
			$this->view = $result->content_view;
			$this->view_configuration = $result->content_view_configuration;
			$this->page_options = $result->page_options;
			$this->page_options_form->deserializeJson($this->page_options); // json from db pulled into form object in page
			return true;
		}
		else {
			return false;
		}
	}



	public function save(): bool {
		if ($this->id) {
			Actions::add_action("pagecreate", (object) [
				"affected_page"=>$this->id,
			]);
			// update
			$result = DB::exec(
				"UPDATE pages SET state=?, title=?, alias=?, content_type=?, content_view=?, parent=?, template=?, page_options=?, content_view_configuration=?, domain=? WHERE id=?",
				[
					$this->state, 
					$this->title, 
					$this->alias, 
					$this->content_type,
					is_numeric($this->view) ? $this->view : NULL,
					$this->parent,
					$this->template_id,
					$this->page_options,
					$this->view_configuration,
					$this->domain,
					$this->id,
				]
			);
			if ($result) {
				// saved ok
				return true;
			}
			else {
				return false;
			}
		}
		else {
			// insert new
			try {
				$result = DB::exec(
					"INSERT INTO pages (state, title, alias, content_type, content_view, parent, template, page_options, content_view_configuration, domain) VALUES (?,?,?,?,?,?,?,?,?,?)",
					[
						$this->state, 
						$this->title, 
						$this->alias, 
						$this->content_type,
						is_numeric($this->view) ? $this->view : NULL,
						$this->parent,
						$this->template_id,
						$this->page_options,
						$this->view_configuration,
						$this->domain,
					]
				);	
			}
			catch (PDOException $e) {
				//CMS::Instance()->queue_message('Error saving page','danger',$_ENV["uripath"].'/admin/pages/');
				if ($_ENV["debug"]) {
					CMS::Instance()->queue_message('Error saving page: ' . $e->getMessage(),'danger',$_ENV["uripath"].'/admin/pages/');
					//echo "<code>" . $e->getMessage() . "</code>";
				}
				$result = false;
				exit(0);
			}
			if ($result) {
				// update page id with last pdo insert
				$this->id = (int) DB::getLastInsertedId();
				Actions::add_action("pagecreate", (object) [
					"affected_page"=>$this->id,
				]);
				return true;
			}
			else {
				// todo - check for username/email already existing and clarify
				CMS::Instance()->queue_message('Unable to create page.','danger',$_ENV["uripath"].'/admin/pages');
				return false;
			}
		}
	}
}