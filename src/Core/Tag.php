<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \PDO;
Use HoltBosse\Form\{Input, Form};

class Tag {
	public int $id;
	public string $title;
	public int $state;
	public string $alias;
	public mixed $form;
	public string $note;
	public mixed $filter;
	public string $description;
	public ?int $image;
	public bool $public;
	public int $parent;
	public int $category;
	public mixed $custom_fields;
	public mixed $contenttypes;
	public ?int $domain = null;

	public function load(int $id): bool {
		$info = DB::fetch('SELECT * FROM tags WHERE id=?', [$id]);
		if ($info) {
			$this->id = $info->id;
			$this->title = $info->title;
			$this->state = $info->state;
			$this->note = $info->note;
			$this->alias = $info->alias;
			$this->filter = $info->filter;
			$this->description = $info->description;
			$this->image = $info->image;
			$this->public = $info->public;
			$this->parent = $info->parent;
			$this->category = $info->category;
			$this->custom_fields = $info->custom_fields;
			$this->contenttypes = DB::fetchAll("SELECT content_type_id FROM tag_content_type WHERE tag_id=?", $this->id, ["mode"=>PDO::FETCH_COLUMN]);
			$this->domain = isset($info->domain) ? $info->domain : null;
			return true;
		} else {
			return false;
		}

	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_tags_for_content(int $content_id, int $content_type_id=-1): array {
		// default to media/image content type
		$result = DB::fetchAll("select * from tags where state>0 and id in (select tag_id from tagged where content_id=? and content_type_id=?)", [$content_id, $content_type_id]);
		return $result;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_tags_available_for_content_type (int $content_type_id): array {
		$result = DB::fetchAll("select * from tags where state>0 and filter=2 and id in (select tag_id from tag_content_type where content_type_id=?)", [$content_type_id]);
		$result2 = DB::fetchAll("select * from tags where state>0 and filter=1 and id not in (select tag_id from tag_content_type where content_type_id=?)", [$content_type_id]);
		return array_merge ($result,$result2);
	}

	// @phpstan-ignore missingType.iterableValue
	public static function set_tags_for_content(int $content_id, array $tag_array, int $content_type_id): void {
		DB::exec("delete from tagged where content_id=? and content_type_id=?", [$content_id,$content_type_id]);
		foreach ($tag_array as $tag_id) {
			DB::exec("insert into tagged (tag_id, content_id, content_type_id) values (?,?,?)", [$tag_id, $content_id, $content_type_id]);
		}
	}

	public function get_depth(): int {
		//legacy compat
		return 0;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_all_tags(): array {
		return DB::fetchAll("SELECT * FROM tags");
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_all_tags_by_depth(int $parent=0, int $depth=-1): array {
		$depth = $depth+1;
		$result=[];
		$children = DB::fetchAll("select t.*, cat.title as cat_title from (tags t) left join categories cat on t.category=cat.id where t.state>-1 and t.parent=?", [$parent]);
		foreach ($children as $child) {
			$child->depth = $depth;
			$result[] = $child;
			$result = array_merge ($result, Tag::get_all_tags_by_depth($child->id, $depth));
		}
		return $result;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function get_tag_content_types(int $id): array {
		return DB::fetchAll("SELECT content_type_id from tag_content_type where tag_id=?", [$id]);
	}

	public static function get_tag_content_type_titles(int $id, ?int $domain=null): string {
		if($domain==null) {
			$domain==CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		}

		$tag = new Tag();
		$tag->load($id);
		$titles_obj = DB::fetchAll("SELECT id, title from content_types where id in (select content_type_id from tag_content_type where tag_id=?)", [$id]);
		$titles = [];
		if (in_array('-1',$tag->contenttypes)) {
			$titles[] = "Media";
		}
		if (in_array('-2',$tag->contenttypes)) {
			$titles[] = "Users";
		}
		foreach($titles_obj as $t) {
			if(!Content::isAccessibleOnDomain($t->id, $domain)) {
				continue;
			}
			$titles[] = $t->title;
		}
		return implode(', ',$titles);
	}



	public function save(Form $required_details_form, ?Form $custom_fields_form = null): bool {
		// update this object with submitted and validated form info
		$this->title = $required_details_form->getFieldByName('title')->default;
		$this->state = $required_details_form->getFieldByName('state')->default;
		$this->note = $required_details_form->getFieldByName('note')->default;
		$this->alias = $required_details_form->getFieldByName('alias')->default;
		$this->filter = $required_details_form->getFieldByName('filter')->default;
		$this->image = $required_details_form->getFieldByName('image')->default;
		$this->description = $required_details_form->getFieldByName('description')->default;
		$this->public = $required_details_form->getFieldByName('public')->default;
		$this->contenttypes = $required_details_form->getFieldByName('contenttypes')->default;
		$this->parent = $required_details_form->getFieldByName('parent')->default;
		$this->category = $required_details_form->getFieldByName('category')->default;
		$this->custom_fields = $custom_fields_form ? json_encode($custom_fields_form) : "";

		$domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);

		
		//if shared accross all domains
		if (isset($_ENV["tag_custom_fields_file_path"])) {
			$customFieldsFormObject = json_decode(file_get_contents($_ENV["tag_custom_fields_file_path"]));
			if(isset($customFieldsFormObject->multi_domain_shared_instances) && $customFieldsFormObject->multi_domain_shared_instances==true) {
				$domain = null;
			}
		}

		//run last
		if($this->id && $this->domain!==null && $this->domain!==$domain) {
			//dont change domain if it already has one
			$domain = $this->domain;
		}

		$this->domain = $domain;

		if ($this->id) {

			Actions::add_action("tagupdate", (object) [
				"affected_tag"=>$this->id,
			]);

			// check we are not trying to make a child node a parent
			if ($this->parent) {
				$parent_id = $this->parent;
				while ($parent_id) {
					$parent_tag = new Tag();
					$parent_tag->load($parent_id);
					$parent_id = $parent_tag->parent;
					if ($parent_tag->parent) {
						if ($parent_id==$this->parent) {
							// can't be child of itself
							CMS::Instance()->log('Tag cannot be child of itself');
							return false;
						}
					}
				}
			}
			// reach here, parent is valid or empty
			
			// update
			$query = "UPDATE tags SET state=?, public=?, title=?, alias=?, image=?, note=?, description=?, filter=?, parent=?, category=?, custom_fields=?, domain=? WHERE id=?";
			if (!$this->alias) {
				$this->alias = Input::stringURLSafe($this->title);
			} else {
				$this->alias = Input::stringURLSafe($this->alias);
			}
			if (!$this->image) {
				$this->image=null;
			}
			$params = [$this->state, $this->public, $this->title, $this->alias, $this->image, $this->note, $this->description, $this->filter, $this->parent, $this->category, $this->custom_fields, $this->domain, $this->id ] ;
			$result = DB::exec($query, $params);
			if ($result) {
				// clear any content types applicable to this tag from tag_content_type
				
				DB::exec("DELETE FROM tag_content_type WHERE tag_id=?", [$this->id]);
				
				// insert new tag content_type relationships if required
				foreach ($this->contenttypes as $contenttype) {
					DB::exec('INSERT INTO tag_content_type (tag_id,content_type_id) VALUES (?,?)', [$this->id, $contenttype]);
				}
				Hook::execute_hook_actions('on_tag_save', $this);
				return true;	
			}
			else {
				CMS::Instance()->log('Tag failed to save');
				return false;
			}
		}
		else {
			// new
			$query = "INSERT INTO tags (state,public,title,alias,note,filter,description,image,parent,category,custom_fields,domain) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
			
			if (!$this->alias) {
				$this->alias = Input::stringURLSafe($this->title);
			} else {
				$this->alias = Input::stringURLSafe($this->alias);
			}
			if (!$this->image) {
				$this->image=null;
			}
			$params = [$this->state, $this->public, $this->title, $this->alias, $this->note, $this->filter, $this->description, $this->image, $this->parent, $this->category, $this->custom_fields, $this->domain] ;
			$result = DB::exec($query, $params);
			if ($result) {
				// insert new tag content_type relationships if required
				$new_id = DB::getLastInsertedId();
				foreach ($this->contenttypes as $contenttype) {
					DB::exec('INSERT INTO tag_content_type (tag_id,content_type_id) VALUES (?,?)', [$new_id, $contenttype]);
				}
				$this->id = (int) $new_id;

				Actions::add_action("tagcreate", (object) [
					"affected_tag"=>$this->id,
				]);

				Hook::execute_hook_actions('on_tag_save', $this);
				return true;	
			}
			else {
				CMS::Instance()->log('New tag failed to save');
				return false;
			}
		}
	}
}