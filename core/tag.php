
<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Tag {
	public $id;
	public $title;
	public $state;
	public $alias;
	public $form;
	public $note;
	public $filter;
	public $description;
	public $image;
	public $public;
	public $parent;
	public $category;
	public $custom_fields;
	public $contenttypes;

	public function show_admin_form() {
		$this->form = new Form();
		$this->form->load_json(CMSPATH . "/tags/");
	}

	public function load($id) {
		$info = DB::fetch('select * from tags where id=?', [$id]);
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
		//$result = DB::fetchAll("select content_type_id from tag_content_type where tag_id=?", [$this->id));
		$this->contenttypes = DB::fetchAll("select content_type_id from tag_content_type where tag_id=?", $this->id. ["mode"=>PDO::FETCH_COLUMN]);

	}

	public static function get_tags_for_content($content_id, $content_type_id=-1) {
		// default to media/image content type
		$result = DB::fetchAll("select * from tags where state>0 and id in (select tag_id from tagged where content_id=? and content_type_id=?)", [$content_id, $content_type_id]);
		return $result;
	}

	public static function get_tags_available_for_content_type ($content_type_id) {
		$result = DB::fetchAll("select * from tags where state>0 and filter=2 and id in (select tag_id from tag_content_type where content_type_id=?)", [$content_type_id]);
		$result2 = DB::fetchAll("select * from tags where state>0 and filter=1 and id not in (select tag_id from tag_content_type where content_type_id=?)", [$content_type_id]);
		return array_merge ($result,$result2);
	}

	public static function set_tags_for_content($content_id, $tag_array, $content_type_id) {
		DB::exec("delete from tagged where content_id=? and content_type_id=?", [$content_id,$content_type_id]);
		foreach ($tag_array as $tag_id) {
			DB::exec("insert into tagged (tag_id, content_id, content_type_id) values (?,?,?)", [$tag_id, $content_id, $content_type_id]);
		}
	}

	public function get_depth() {
		//legacy compat
		return 0;
	}

	public static function get_all_tags() {
		return DB::fetchAll("SELECT * FROM tags");
	}

	public static function get_all_tags_by_depth($parent=0, $depth=-1) {
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

	public static function get_tag_content_types($id) {
		return DB::fetchAll("select content_type_id from tag_content_type where tag_id=?", [$id]);
	}

	public static function get_tag_content_type_titles($id) {
		$tag = new Tag();
		$tag->load($id);
		$titles_obj = DB::fetchAll("select title from content_types where id in (select content_type_id from tag_content_type where tag_id=?)", [$id]);
		$titles = [];
		if (in_array('-1',$tag->contenttypes)) {
			$titles[] = "Media";
		}
		if (in_array('-2',$tag->contenttypes)) {
			$titles[] = "Users";
		}
		foreach($titles_obj as $t) {
			$titles[] = $t->title;
		}
		return implode(', ',$titles);
	}



	public function save($required_details_form, $custom_fields_form = "") {
		// update this object with submitted and validated form info
		$this->title = $required_details_form->get_field_by_name('title')->default;
		$this->state = $required_details_form->get_field_by_name('state')->default;
		$this->note = $required_details_form->get_field_by_name('note')->default;
		$this->alias = $required_details_form->get_field_by_name('alias')->default;
		$this->filter = $required_details_form->get_field_by_name('filter')->default;
		$this->image = $required_details_form->get_field_by_name('image')->default;
		$this->description = $required_details_form->get_field_by_name('description')->default;
		$this->public = $required_details_form->get_field_by_name('public')->default;
		$this->contenttypes = $required_details_form->get_field_by_name('contenttypes')->default;
		$this->parent = $required_details_form->get_field_by_name('parent')->default;
		$this->category = $required_details_form->get_field_by_name('category')->default;
		$this->custom_fields = $custom_fields_form ? json_encode($custom_fields_form) : "";

		if ($this->parent=="0"||$this->parent=="") {
			$this->parent = 0;
		}

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
			$query = "update tags set state=?, public=?, title=?, alias=?, image=?, note=?, description=?, filter=?, parent=?, category=?, custom_fields=? where id=?";
			if (!$this->alias) {
				$this->alias = Input::stringURLSafe($this->title);
			}
			if (!$this->image) {
				$this->image=null;
			}
			$params = [$this->state, $this->public, $this->title, $this->alias, $this->image, $this->note, $this->description, $this->filter, $this->parent, $this->category, $this->custom_fields, $this->id ] ;
			$result = DB::exec($query, $params);
			if ($result) {
				// clear any content types applicable to this tag from tag_content_type
				
				DB::exec("delete from tag_content_type where tag_id=?", [$this->id]);
				
				// insert new tag content_type relationships if required
				foreach ($this->contenttypes as $contenttype) {
					DB::exec('insert into tag_content_type (tag_id,content_type_id) values (?,?)', [$this->id, $contenttype]);
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
			$query = "insert into tags (state,public,title,alias,note,filter,description,image,parent,category,custom_fields) values(?,?,?,?,?,?,?,?,?,?,?)";
			
			if (!$this->alias) {
				$this->alias = Input::stringURLSafe($this->title);
			}
			if (!$this->image) {
				$this->image=null;
			}
			$params = [$this->state, $this->public, $this->title, $this->alias, $this->note, $this->filter, $this->description, $this->image, $this->parent, $this->category, $this->custom_fields];
			$result = DB::exec($query, $params);
			if ($result) {
				// insert new tag content_type relationships if required
				$new_id = DB::getLastInsertedId();
				foreach ($this->contenttypes as $contenttype) {
					DB::exec('insert into tag_content_type (tag_id,content_type_id) values (?,?)', [$new_id, $contenttype]);
				}
				$this->id = $new_id;

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