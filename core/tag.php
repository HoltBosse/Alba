
<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Tag {
	public $id;
	public $title;
	public $state;
	public $alias;

	public function show_admin_form() {
		$this->form = new Form();
		$this->form->load_json(CMSPATH . "/tags/");
	}

	public function load($id) {
		$info = CMS::Instance()->pdo->query('select * from tags where id=' . $id)->fetch();
		$this->id = $info->id;
		$this->title = $info->title;
		$this->state = $info->state;
		$this->note = $info->note;
		$this->alias = $info->alias;
		$this->filter = $info->filter;
		$this->description = $info->description;
		$this->image = $info->image;
		$this->public = $info->public;
		$query = "select content_type_id from tag_content_type where tag_id=?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($this->id));
		$this->contenttypes = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

	}

	public static function get_tags_for_content($content_id) {
		$query = "select * from tags where id in (select tag_id from tagged where content_id=?)";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($content_id));
		return $stmt->fetchAll();
	}

	public static function get_all_tags() {
		$query = "select * from tags";
		return CMS::Instance()->pdo->query($query)->fetchAll();
	}

	public static function get_tag_content_types($id) {
		$query = "select content_type_id from tag_content_type where tag_id=?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($id));
		return $stmt->fetchAll();
	}

	public static function get_tag_content_type_titles($id) {
		$tag = new Tag();
		$tag->load($id);
		$query = "select title from content_types where id in (select content_type_id from tag_content_type where tag_id=?)";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($id));
		$titles_obj = $stmt->fetchAll();
		$titles = array();
		if (in_array('-1',$tag->contenttypes)) {
			$titles[] = "Media";
		}
		foreach($titles_obj as $t) {
			$titles[] = $t->title;
		}
		return implode(', ',$titles);
	}

	public function save($required_details_form) {
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

		
		if ($this->id) {
			
			// update
			$query = "update tags set state=?, public=?, title=?, alias=?, image=?, note=?, description=?, filter=? where id=?";
			$stmt = CMS::Instance()->pdo->prepare($query);
			$params = array($this->state, $this->public, $this->title, $this->alias, $this->image, $this->note, $this->description, $this->filter, $this->id) ;
			$result = $stmt->execute( $params );
			
			if ($result) {
				// clear any content types applicable to this tag from tag_content_type
				$query = "delete from tag_content_type where tag_id=?";
				$stmt = CMS::Instance()->pdo->prepare($query);
				$stmt->execute(array($this->id));
				// insert new tag content_type relationships if required
				foreach ($this->contenttypes as $contenttype) {
					$stmt = CMS::Instance()->pdo->prepare('insert into tag_content_type (tag_id,content_type_id) values (?,?)');
					$stmt->execute(array($this->id, $contenttype));
				}
				CMS::Instance()->queue_message('Tag updated','success',Config::$uripath . '/admin/tags/show');	
			}
			else {
				CMS::Instance()->queue_message('Tag failed to save','danger', $_SERVER['REQUEST_URI']);	
			}
		}
		else {
			// new
			$query = "insert into tags (state,public,title,alias,note,filter,description,image) values(?,?,?,?,?,?,?,?)";
			$stmt = CMS::Instance()->pdo->prepare($query);
			if (!$this->alias) {
				$this->alias = Input::stringURLSafe($this->title);
			}
			if (!$this->image) {
				$this->image=null;
			}
			$params = array($this->state, $this->public, $this->title, $this->alias, $this->note, $this->filter, $this->description, $this->image);
			$result = $stmt->execute( $params );
			if ($result) {
				// insert new tag content_type relationships if required
				$new_id = CMS::Instance()->pdo->lastInsertId();
				foreach ($this->contenttypes as $contenttype) {
					$stmt = CMS::Instance()->pdo->prepare('insert into tag_content_type (tag_id,content_type_id) values (?,?)');
					$stmt->execute(array($new_id, $contenttype));
				}
				CMS::Instance()->queue_message('New tag saved','success',Config::$uripath . '/admin/tags/');	
			}
			else {
				CMS::Instance()->queue_message('New tag failed to save','danger', $_SERVER['REQUEST_URI']);	
			}
		}
	}
}