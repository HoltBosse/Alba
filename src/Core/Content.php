<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\Form\{Input, Form, Field};
Use HoltBosse\DB\DB;
Use \Exception;

class Content {
	public ?int $id;
	public string $title;
	public int $state;
	public string $description;
	public mixed $configuration;
	public string $updated;
	public int $content_type;
	public int $created_by;
	public int $updated_by;
	public string $note;
	public mixed $tags;
	public string $alias;
	public int $category;
	public mixed $custom_fields;
	public ?string $table_name;
	public mixed $start;
	public mixed $end;
	public string $content_location;
	public ?int $domain;

	// @phpstan-ignore missingType.iterableValue
	private static array $controllerRegistry = [
		"basic_article" => __DIR__ . '/../controllers/basic_article',
	];

	public function __construct(?int $content_type=0) {
		$this->id = null;
		$this->title = "";
		$this->description = "";
		$this->state = 1;
		$this->updated = date('Y-m-d H:i:s');
		$this->content_type = $content_type;
		$this->tags = [];
		if ($content_type != 0) {
			$this->content_location = $this->get_content_location($this->content_type);
			$this->custom_fields = JSON::load_obj_from_file(Content::getContentControllerPath($this->content_location) . '/custom_fields.json');
			$this->table_name = "controller_" . $this->custom_fields->id;
			$this->domain = null;
		}
		$this->created_by = CMS::Instance()->user->id;
		$this->updated_by = CMS::Instance()->user->id;
		$this->alias="";
		$this->category=0;
	}

	public static function registerContentController(string $contentName, string $contentPath): bool {
		if (!isset(self::$controllerRegistry[$contentName])) {
			self::$controllerRegistry[$contentName] = $contentPath;

			return true;
		}

		return false;
	}

	public static function registerContentControllerDir(string $contentControllerDirPath): void {
		foreach(glob($contentControllerDirPath . '/*') as $file) {
			Content::registerContentController(
				basename($file, '.php'),
				$file
			);
		}
	}

	public static function getContentControllerPath(string $contentName): ?string {
		if (isset(self::$controllerRegistry[$contentName])) {
			return realpath(self::$controllerRegistry[$contentName]);
		}

		return null;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function getContentControllerNames(): array {
		return array_keys(self::$controllerRegistry);
	}

	public static function isAccessibleOnDomain(int $content_type_id, ?int $domain_index = NULL): bool {
		if($domain_index===NULL) {
			$domain_index = CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		}

		$location = Content::get_content_location($content_type_id);
		$custom_fields = JSON::load_obj_from_file(Content::getContentControllerPath($location) . '/custom_fields.json');
		if (isset($custom_fields->domains)) {
			if (!in_array($domain_index, $custom_fields->domains)) {
				return false;
			}
		}
		return true;
	}

	public static function get_table_name_for_content_type(int $type_id): string {
		$location = Content::get_content_location($type_id);
		$custom_fields = JSON::load_obj_from_file(Content::getContentControllerPath($location) . '/custom_fields.json');
		if ($custom_fields->id ?? null) {
			$table_name = "controller_" . $custom_fields->id ;
			return $table_name;
		}
		else {
			throw new Exception('Unable to determine table name for content id ' . $type_id);
		}
	}

	private function make_alias_unique(): void {
		$is_unique = false;
		while (!$is_unique) {
			$results = DB::fetchAll("SELECT * from `{$this->table_name}` where alias=? and content_type=?", [$this->alias, $this->content_type] );
			// if this is an existing content item, make sure we don't count itself as a clashing alias
			$self_clash = false;
			if ($this->id) {
				foreach ($results as $potential_clash) {
					if ($potential_clash->id==$this->id) {
						$self_clash=true;
						break;
					}
				}
			}
			if ( (sizeof($results) > 0 && !$self_clash) || ((sizeof($results) > 1 && $self_clash )) ) {
				// if clash isn't with just itself
				if ($this->id) {
					// add id to alias to make unique for existing content item
					$this->alias = $this->alias . "-" . $this->id;
					if (CMS::Instance()->isAdmin()) {
						CMS::Instance()->queue_message('Added content id as suffix to "URL Friendly" field to ensure uniqueness.','warning');
					}
				}
				else {
					$this->alias = $this->alias . "-" . uniqid();
					if (CMS::Instance()->isAdmin()) {
						CMS::Instance()->queue_message('Added random suffix to "URL Friendly" field to ensure uniqueness.','warning');
					}
				}
			}
			else {
				$is_unique=true;
			}
		}
	}

	public function get_field(string $field_name): mixed {
		//CMS::pprint_r ($this);
		if (!$this->table_name) {			
			throw new Exception('Unknown table name');
		}
		$query = "SELECT `{$field_name}` as v from `{$this->table_name}` where id=?";
		$value = DB::fetch($query, [$this->id])->v; // todo: can we make col name param?
		if ($value) {
			return $value; 
		}
		else {
			return null;
		}
	}

	public function load(?int $id, ?int $content_type): bool {
		$table_name = Content::get_table_name_for_content_type($content_type);
		$info = DB::fetch("SELECT * FROM `{$table_name}` WHERE id=?",[$id]);
		if ($info) {
			$this->id = $info->id;
			$this->title = $info->title;
			$this->state = $info->state;
			$this->note = $info->note;
			$this->alias = $info->alias;
			$this->start = $info->start;
			$this->end = $info->end;
			$this->table_name = $table_name;
			$this->content_type = $info->content_type;
			$this->content_location = $this->get_content_location($this->content_type);
			$this->created_by = $info->created_by;
			$this->updated_by = $info->updated_by;
			$this->tags = Tag::get_tags_for_content($this->id, $this->content_type);
			$this->category = $info->category;
			$this->domain = $info->domain;
			return true;
		}
		else {
			return false;
		}
	}

	public function load_from_alias(string $alias, int $content_type): bool {
		$table_name = Content::get_table_name_for_content_type($content_type);
		$info = DB::fetch("SELECT * FROM `{$table_name}` WHERE alias=?",[$alias]);
		if ($info) {
			$this->table_name = $table_name;
			$this->id = $info->id;
			$this->title = $info->title;
			$this->state = $info->state;
			$this->note = $info->note;
			$this->alias = $info->alias;
			$this->start = $info->start;
			$this->end = $info->end;
			$this->content_type = $info->content_type;
			$this->content_location = $this->get_content_location($this->content_type);
			$this->created_by = $info->created_by;
			$this->tags = Tag::get_tags_for_content($this->id, $this->content_type);
			$this->category = $info->category;
			$this->domain = $info->domain;
			return true;
		}
		else {
			return false;
		}
	}

	public function duplicate(): bool {
		$table_name = Content::get_table_name_for_content_type($this->content_type);
		$location = Content::get_content_location($this->content_type);
		$content_form = new Form (Content::getContentControllerPath($location) . "/custom_fields.json");
		// remember original id
		$original_id = $this->id;
		// make current duplicate id blank
		$this->id = null;
		// add copy to title
		$this->title = $this->title . " - Copy";
		$this->make_alias_unique();
		// ordering
		$ordering = DB::fetch("SELECT (max(ordering)+1) as ordering from `{$table_name}`")->ordering;
		if (!$ordering) {
			$ordering=1;
		}
		$params = [$this->state, $ordering, $this->title, $this->alias, $this->content_type, $this->updated_by, $this->updated_by, $this->note, $this->start, $this->end, $this->category, $this->domain];
		$required_result = DB::exec("INSERT into `{$table_name}` (state,ordering,title,alias,content_type, created_by, updated_by, note, start, end, category, domain) values(?,?,?,?,?,?,?,?,?,?,?,?)", $params);
		if ($required_result) {
			$this->id = (int) DB::getLastInsertedId();

			Actions::add_action("contentcreate", (object) [
				"content_id"=>$this->id,
				"content_type"=>$this->content_type,
			]);

			// set tags
			// note - $this->tags is already array - unlike code in save() function below
			$tag_id_array=[];
			foreach ($this->tags as $curtag) {
				$tag_id_array[] = $curtag->id;
			}
			Tag::set_tags_for_content((int) $this->id, $tag_id_array, $this->content_type);
			// copy content fields
			foreach ($content_form->fields as $field) {
				if (isset($field->save)) {
					if ($field->save===false) {
						// field have save property set explicitly to false - SKIP saving
						// this may be a field such as an SQL statement from a non-cms table, or just markup etc
						continue;
					}
				}
				// get og field value and insert into new content
				// $dup_query = 'update ' . $table_name . " as o set o." . $field->name . " = (SELECT c.{$field->name} from {$table_name} c where c.id={$original_id}) where o.id=?";
				// above query does not work - query optimizer makes it so sql see table as same, and cannot update from self selection
				// leaving for future us to learn from repeatedly
				$dup_query = "UPDATE `{$table_name}` AS n INNER JOIN `{$table_name}` AS o ON o.id=? AND n.id=? SET n.{$field->name} = o.{$field->name}";
				//CMS::pprint_r ($dup_query); CMS::pprint_r ($this->id); die();
				DB::exec($dup_query, [$original_id, $this->id]); 
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function save(Form $required_details_form, Form $content_form, string $return_url=''): bool {
		// return URL not used anymore - left for now for legacy

		$userActionDiff = [];
		if ($this->id) {
			$previousContentItem = DB::fetch("SELECT * FROM `{$this->table_name}` WHERE id=?", $this->id);
		} else {
			$previousContentItem = (object) [];
		}
		
		// update this object with submitted and validated form info
		$this->title = trim($required_details_form->getFieldByName('title')->default);
		$this->state = $required_details_form->getFieldByName('state')->default;
		$this->note = $required_details_form->getFieldByName('note')->default;
		$this->alias = $required_details_form->getFieldByName('alias')->default;
		if (!$this->alias) {
			$this->alias = Input::stringURLSafe($this->title);
		} else {
			$this->alias = Input::stringURLSafe($this->alias);
		}
		$this->start = $required_details_form->getFieldByName('start')->default;
		$this->end = $required_details_form->getFieldByName('end')->default;
		$this->updated_by = CMS::Instance()->user->id;
		$this->tags = $required_details_form->getFieldByName('tags')->default; 
		$this->category = $required_details_form->getFieldByName('category')->default; 

		// ensure alias is unique for content_type - will use id for existing content, random 4 digit number otherwise
		$this->make_alias_unique();

		if(strlen($this->alias)>$required_details_form->getFieldByName('alias')->maxlength) {
			CMS::Instance()->queue_message('Auto generated alias too long','danger', $_SERVER['HTTP_REFERER']);
			die;
		}

		if (!$this->start) {
			$this->start = time();
		}
		else {
			//$this->start = date("Y-m-d H:i:s", strtotime($this->start));
			$this->start = strtotime($this->start);
		}
		if (!$this->end) {
			$this->end = null;
		}
		else {
			//$this->end = date("Y-m-d H:i:s", strtotime($this->end));
			$this->end = strtotime($this->end);
		}

		$starttime = $this->start ? date("Y-m-d H:i:s", $this->start) : null;
        $endtime = $this->end ? date("Y-m-d H:i:s", $this->end) : null;
		
		Hook::execute_hook_actions('before_content_save', $this, $content_form);

		$content_location = $this->get_content_location($this->content_type);
		$custom_fields = JSON::load_obj_from_file(Content::getContentControllerPath($content_location) . '/custom_fields.json');
		$domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);

		
		//if shared accross all domains
		if(isset($custom_fields->multi_domain_shared_instances) && $custom_fields->multi_domain_shared_instances===true) {
			$domain = null; // null means shared across all domains
		}
		
		//run last
		if($this->id && $this->domain!==null && $this->domain!==$domain) {
			//dont change domain if it already has one
			$domain = $this->domain;
		}

		$this->domain = $domain;

		if ($this->id) {
			$actionId = Actions::add_action("contentupdate", (object) [
				"content_id"=>$this->id,
				"content_type"=>$this->content_type,
			]);

			$checkFields = ["state", "title", "alias", "note", "updated_by", "category"];
			foreach($checkFields as $field) {
				if($this->$field != $previousContentItem->$field) {
					$userActionDiff[$field] = (object) [
						"before"=> $previousContentItem->$field,
						"after"=> $this->$field,
					];
				}
			}

			// update
			$params = [$this->state, $this->title, $this->alias, $this->note, $starttime, $endtime, $this->updated_by, $this->category, $this->domain, $this->id] ;
			$required_result = DB::exec("UPDATE `{$this->table_name}` SET state=?,  title=?, alias=?, note=?, start=?, end=?, updated_by=?, category=?, domain=? WHERE id=?", $params);
		}
		else {
			// new
			//CMS::pprint_r ($this);
			// get next order value
			$ordering = DB::fetch("SELECT (max(ordering)+1) as ordering from `{$this->table_name}`")->ordering;
			if (!$ordering) {
				$ordering=1;
			}
			$query = "insert into `{$this->table_name}` (state,ordering,title,alias,content_type, created_by, updated_by, note, start, end, category, domain) values(?,?,?,?,?,?,?,?,?,?,?,?)";
			$params = [$this->state, $ordering, $this->title, $this->alias, $this->content_type, $this->updated_by, $this->updated_by, $this->note, $starttime, $endtime, $this->category, $this->domain];
			$required_result = DB::exec($query, $params);
			if ($required_result) {
				// update object id with inserted id
				$this->id = (int) DB::getLastInsertedId();

				$actionId = Actions::add_action("contentcreate", (object) [
					"content_id"=>$this->id,
					"content_type"=>$this->content_type,
				]);

				$checkFields = ["state", "title", "alias", "note", "updated_by", "category"];
				foreach($checkFields as $field) {
					$userActionDiff[$field] = (object) [
						"before"=> null,
						"after"=> $this->$field,
					];
				}
			}
		}
		if (!$required_result) {
			CMS::Instance()->log("Failed to save content");
            return false;
		}

		// set tags
		Tag::set_tags_for_content($this->id, json_decode($this->tags), $this->content_type);

		// now save fields
		// first remove old field data if any exists
		// NO MORE content_fields
		// DB::exec("delete from content_fields where content_id=?", [$this->id));

		$error_text="";
		foreach ($content_form->fields as $field) {
			// insert field info
			if (isset($field->save)) {
				if ($field->save===false) {
					// field have save property set explicitly to false - SKIP saving
					// this may be a field such as an SQL statement from a non-cms table, or just markup etc
					continue;
				}
			}
			// TODO: handle other arrays 
			/* CMS::pprint_r ($field);  */
			if ($field->filter=="ARRAYOFINT") {
				// convert array of int to string
				if (is_array($field->default)) {
					$field->default = implode(",",$field->default);
				}
			}
			// always update, either new row for new content, or id available for update
			// content casting
			// make sure INT is good
			if ($field->coltype=="INTEGER") {
				$field->default = (int)$field->default;
			}

			$fieldName = $field->name;
			if($field->default != $previousContentItem->$fieldName) {
				$userActionDiff[$fieldName] = (object) [
					"before"=> $previousContentItem->$fieldName,
					"after"=> $field->default,
				];
			}

			$result = DB::exec("update `{$this->table_name}` set `{$field->name}`=? where id=?", [$field->default, $this->id]);
			if (!$result) {
				$error_text .= "Error saving: " . $field->name . " ";
				CMS::Instance()->log("Error saving: " . $field->name);
			}
		}

		Actions::add_action_details($actionId, (object) $userActionDiff);

		if ($error_text) {
			return false;
		}
		else {
			Hook::execute_hook_actions('on_content_save', $this);
			return true;
		}
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_all_content_types(): array {
		$result = DB::fetchAll('SELECT * from content_types where state > 0 order by id ASC');
		return $result ? $result : [];
	}
	
	public static function get_content_type_title(int $content_type): ?string {
		if (!$content_type) {
			return null;
		}
		if ($content_type==-1) {
			return "User";
		}
		if ($content_type==-2) {
			return "Image/Media";
		}
		if ($content_type==-3) {
			return "Tag";
		}
		$result = DB::fetch("SELECT title from content_types where id=?", [$content_type]);
		if ($result) {
			return $result->title;
		}
		else {
			return null;
		}
	}

	public static function get_content_type_fields(int $content_type): ?object {
		if (!$content_type) {
			return null;
		}
		$result = DB::fetch("SELECT * from content_types where id=?", [$content_type]);
		if ($result) {
			return $result;
		}
		else {
			return null;
		}
	}

	public static function get_content_type_id(?string $controller_location): ?int {
		if (!$controller_location) {
			return null;
		}
		$result = DB::fetch("SELECT id from content_types where controller_location=?", [$controller_location]);
		if ($result) {
			return $result->id;
		}
		else {
			//CMS::Instance()->queue_message('Failed to determine content type id for controller_location: ' . $controller_location, "error");
			return null;
		}
	}

	public static function get_config_value (mixed $config, mixed $key): mixed {
		// $config is array of {name:"",value:""} pairs
		foreach ($config as $config_pair) {
			if ($config_pair->name==$key) {
				return $config_pair->value;
			}
		}
		return null;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_applicable_tags (?int $content_type_id): array {
		$tags = DB::fetchAll(
			"SELECT *
			from tags
			where (
				filter=2
				and id in (
					SELECT tag_id
					from tag_content_type
					where content_type_id=?
				)
			) or (
				filter=1
				and id
				not in (
					SELECT tag_id
					from tag_content_type
					where content_type_id=?
				)
			)",
			[$content_type_id, $content_type_id]
		);
		return $tags ? $tags : [];
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_applicable_categories (?int $content_type_id): array {
		$query = "SELECT * from categories where content_type=?";
		$cats = DB::fetchAll($query, [$content_type_id]);
		return $cats ? $cats : [];
	}

	public static function get_content_location(?int $content_type_id): ?string {
		$result = DB::fetch("SELECT controller_location from content_types where id=?", [$content_type_id]);
		return $result ? $result->controller_location : null;
	}

	public static function get_view_location(?int $view_id): ?string {
		$result = DB::fetch("SELECT location from content_views where id=?", [$view_id]);
		return $result ? $result->location : null;
	}

	public static function get_content_type_for_view (?int $view_id): ?int {
		$result = DB::fetch("SELECT content_type_id from content_views where id=?", [$view_id]);
		return $result ? $result->content_type_id : null;
	}

	public static function get_view_title(int $view_id): ?string {
		if (!$view_id) {
			return null;
		}
		$result = DB::fetch("SELECT title from content_views where id=?", [$view_id]);
		if ($result) {
			return $result->title;
		}
		else {
			return null;
		}
	}

	public static function get_all_content_for_id (int $id, int $content_type): object {
		$table = Content::get_table_name_for_content_type($content_type);
		return DB::fetch("SELECT * from `{$table}` where id=?", [$id]);
	}

	#[\Deprecated(message: "get_table_name_for_content_type", since: "3.20.0")]
	public static function get_table_name(int $content_type_id): string {
		return Content::get_table_name_for_content_type($content_type_id);
	}

	#[\Deprecated(message: "use ContentSearch instead", since: "3.0.0")] //@phpstan-ignore-line
	public static function get_all_content($order_by="id", $type_filter=false, $id=null, $tag=null, $published_only=null, $list_fields=[], $ignore_fields=[], $filter_field=null, $filter_val=null, $page=0, $search="", $custom_pagination_size=null) {
		//add inputed filters, and then if id is present, add that to filters as well
		if ($filter_field) {
			$filters = [$filter_field=>$filter_val];
		}
		else {
			$filters = null;
		}
		$id ? $filters["id"] = $id : "";

		if(!$list_fields && $type_filter) {
			$location = Content::get_content_location($type_filter);
			if(!is_numeric($type_filter)) {
				$location = $type_filter;
			}
			$form = JSON::load_obj_from_file(Content::getContentControllerPath($location) . '/custom_fields.json');
			foreach($form->fields as $field) {
				/** @var Field $field */
				if (isset($field->save)) {
					if ($field->save===true) {
						$list_fields[] = $field->name;
					}
				}
				else {
					// assume saveable
					$list_fields[] = $field->name;
				} 
			}
		}

		$content_search = new ContentSearch();
		$content_search->order_by = $order_by;
		$content_search->type_filter = $type_filter;
		$content_search->tags = [$tag];
		$content_search->published_only = $published_only;
        $content_search->list_fields = $list_fields;
		$content_search->ignore_fields = $ignore_fields;
        $content_search->filters = $filters;
		$content_search->page = $page;
		$content_search->searchtext = $search;
		$content_search->page_size = $custom_pagination_size;

		return $content_search->exec();
	}

}