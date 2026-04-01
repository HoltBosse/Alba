<?php
namespace HoltBosse\Alba\Core;

Use \PDO;
Use HoltBosse\Form\{Input, Form};
Use HoltBosse\Alba\Core\File;

/**
 * Tag - WordPress taxonomy wrapper
 * Provides wrapper methods for WordPress tag/taxonomy functions
 */
class Tag {
	public ?int $id = null;
	public string $title;
	public int $state;
	public string $alias;
	public mixed $form;
	public string $note;
	public mixed $filter;
	public string $description;
	public ?int $image;
	public int $public;
	public int $parent;
	public int $category;
	public mixed $custom_fields;
	public mixed $contenttypes;
	public ?int $domain = null;
	private string $taxonomy = 'post_tag'; // Default to WordPress tag taxonomy

	/**
	 * Load tag by ID
	 * Wrapper for WordPress get_term()
	 */
	public function load(int $id): bool {
		$term = get_term($id);
		
		if ($term && !is_wp_error($term)) {
			$this->id = $term->term_id;
			$this->title = $term->name;
			$this->state = 1; // WordPress terms don't have state by default
			$this->note = get_term_meta($term->term_id, 'alba_note', true) ?: '';
			$this->alias = $term->slug;
			$this->filter = get_term_meta($term->term_id, 'alba_filter', true);
			$this->description = $term->description;
			$this->image = get_term_meta($term->term_id, 'alba_image', true);
			$this->public = (int) get_term_meta($term->term_id, 'alba_public', true) ?: 1;
			$this->parent = $term->parent;
			$this->category = (int) get_term_meta($term->term_id, 'alba_category', true) ?: 0;
			$this->custom_fields = get_term_meta($term->term_id, 'alba_custom_fields', true);
			$this->contenttypes = get_term_meta($term->term_id, 'alba_content_types', true) ?: [];
			$this->domain = get_term_meta($term->term_id, 'alba_domain', true);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get tags for content
	 * Wrapper for WordPress wp_get_post_terms() or get_terms()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_tags_for_content(int $content_id, int $content_type_id=-1): array {
		// For WordPress posts
		if ($content_type_id > 0) {
			$terms = wp_get_post_terms($content_id, 'post_tag');
		} else {
			// For other content types (media, users) use term meta
			$taxonomy = 'alba_tag_' . abs($content_type_id);
			$terms = get_terms([
				'taxonomy' => $taxonomy,
				'hide_empty' => false,
			]);
		}
		
		if (is_wp_error($terms)) {
			return [];
		}
		
		$tags = [];
		foreach ($terms as $term) {
			$tags[] = (object) [
				'id' => $term->term_id,
				'title' => $term->name,
				'alias' => $term->slug,
				'state' => 1
			];
		}
		
		return $tags;
	}

	/**
	 * Get tags available for content type
	 * Wrapper for WordPress get_terms()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_tags_available_for_content_type (int $content_type_id): array {
		$args = [
			'taxonomy' => 'post_tag',
			'hide_empty' => false,
		];
		
		$terms = get_terms($args);
		
		if (is_wp_error($terms)) {
			return [];
		}
		
		$tags = [];
		foreach ($terms as $term) {
			// Check if tag is available for this content type
			$contenttypes = get_term_meta($term->term_id, 'alba_content_types', true) ?: [];
			$filter = get_term_meta($term->term_id, 'alba_filter', true);
			
			if ($filter == 2 && in_array($content_type_id, $contenttypes)) {
				$tags[] = $term;
			} elseif ($filter == 1 && !in_array($content_type_id, $contenttypes)) {
				$tags[] = $term;
			}
		}
		
		return $tags;
	}

	/**
	 * Set tags for content
	 * Wrapper for WordPress wp_set_post_terms()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function set_tags_for_content(int $content_id, array $tag_array, int $content_type_id): void {
		if ($content_type_id > 0) {
			// WordPress post
			wp_set_post_terms($content_id, $tag_array, 'post_tag');
		} else {
			// Other content types - store in post meta or custom table
			update_post_meta($content_id, 'alba_tags_' . abs($content_type_id), $tag_array);
		}
	}

	public function get_depth(): int {
		//legacy compat
		return 0;
	}

	/**
	 * Get all tags
	 * Wrapper for WordPress get_terms()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_all_tags(): array {
		$terms = get_terms([
			'taxonomy' => 'post_tag',
			'hide_empty' => false,
		]);
		
		if (is_wp_error($terms)) {
			return [];
		}
		
		return $terms;
	}

	/**
	 * Get all tags by depth (hierarchical)
	 * Wrapper for WordPress get_terms()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_all_tags_by_depth(int $parent=0, int $depth=-1): array {
		$depth = $depth+1;
		$result=[];
		
		$children = get_terms([
			'taxonomy' => 'post_tag',
			'parent' => $parent,
			'hide_empty' => false,
		]);
		
		if (is_wp_error($children)) {
			return [];
		}
		
		foreach ($children as $child) {
			$tag_data = (object) [
				'id' => $child->term_id,
				'title' => $child->name,
				'alias' => $child->slug,
				'state' => 1,
				'parent' => $child->parent,
				'depth' => $depth,
				'category' => get_term_meta($child->term_id, 'alba_category', true),
				'cat_title' => '' // TODO: implement if needed
			];
			$result[] = $tag_data;
			$result = array_merge($result, Tag::get_all_tags_by_depth($child->term_id, $depth));
		}
		
		return $result;
	}

	/**
	 * Get tag content types
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_tag_content_types(int $id): array {
		$contenttypes = get_term_meta($id, 'alba_content_types', true) ?: [];
		$result = [];
		
		foreach ($contenttypes as $ct) {
			$result[] = (object) ['content_type_id' => $ct];
		}
		
		return $result;
	}

	/**
	 * Get tag content type titles
	 */
	public static function get_tag_content_type_titles(int $id, ?int $domain=null): string {
		if($domain==null) {
			$domain = CMS::getDomainIndex($_SERVER["HTTP_HOST"]);
		}

		$contenttypes = get_term_meta($id, 'alba_content_types', true) ?: [];
		$titles = [];
		
		if (in_array('-1', $contenttypes) || in_array(-1, $contenttypes)) {
			$titles[] = "Media";
		}
		if (in_array('-2', $contenttypes) || in_array(-2, $contenttypes)) {
			$titles[] = "Users";
		}
		
		// Get content type titles from WordPress post types or custom storage
		foreach($contenttypes as $ct) {
			if ($ct > 0) {
				$post_type = get_post_type_object($ct);
				if ($post_type) {
					$titles[] = $post_type->labels->singular_name;
				}
			}
		}
		
		return implode(', ', $titles);
	}

	/**
	 * Save tag
	 * Wrapper for WordPress wp_insert_term() and wp_update_term()
	 */
	public function save(Form $required_details_form, ?Form $custom_fields_form = null): bool {
		// update this object with submitted and validated form info
		$this->title = $required_details_form->getFieldByName('title')->default;
		$this->state = $required_details_form->getFieldByName('state')->default;
		$this->note = $required_details_form->getFieldByName('note')->default;
		$this->alias = $required_details_form->getFieldByName('alias')->default;
		$this->filter = $required_details_form->getFieldByName('filter')->default;
		$this->image = $required_details_form->getFieldByName('image')->default;
		$this->description = $required_details_form->getFieldByName('description')->default;
		$this->public = (int) $required_details_form->getFieldByName('public')->default;
		$this->contenttypes = $required_details_form->getFieldByName('contenttypes')->default;
		$this->parent = $required_details_form->getFieldByName('parent')->default;
		$this->category = $required_details_form->getFieldByName('category')->default;
		$this->custom_fields = $custom_fields_form ? json_encode($custom_fields_form) : "";

		$domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);

		//if shared across all domains
		if (isset($_ENV["tag_custom_fields_file_path"])) {
			$customFieldsFormObject = json_decode(File::getContents($_ENV["tag_custom_fields_file_path"]));
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

		// Prepare alias/slug
		if (!$this->alias) {
			$this->alias = sanitize_title($this->title);
		} else {
			$this->alias = sanitize_title($this->alias);
		}

		// Prepare term arguments
		$args = [
			'description' => $this->description,
			'slug' => $this->alias,
			'parent' => $this->parent
		];

		if ($this->id) {
			// Update existing term
			Actions::add_action("tagupdate", (object) [
				"affected_tag"=>$this->id,
			]);

			// check we are not trying to make a child node a parent
			if ($this->parent) {
				$parent_id = $this->parent;
				while ($parent_id) {
					$parent_term = get_term($parent_id);
					if (!$parent_term || is_wp_error($parent_term)) {
						break;
					}
					$parent_id = $parent_term->parent;
					if ($parent_id == $this->id) {
						// can't be child of itself
						CMS::Instance()->log('Tag cannot be child of itself');
						return false;
					}
				}
			}

			$result = wp_update_term($this->id, $this->taxonomy, array_merge(['name' => $this->title], $args));
			
			if (is_wp_error($result)) {
				CMS::Instance()->log('Tag failed to save: ' . $result->get_error_message());
				return false;
			}
			
			// Update term meta
			update_term_meta($this->id, 'alba_state', $this->state);
			update_term_meta($this->id, 'alba_public', $this->public);
			update_term_meta($this->id, 'alba_note', $this->note);
			update_term_meta($this->id, 'alba_filter', $this->filter);
			update_term_meta($this->id, 'alba_image', $this->image);
			update_term_meta($this->id, 'alba_category', $this->category);
			update_term_meta($this->id, 'alba_custom_fields', $this->custom_fields);
			update_term_meta($this->id, 'alba_domain', $domain);
			update_term_meta($this->id, 'alba_content_types', $this->contenttypes);
			
			Hook::execute_hook_actions('on_tag_save', $this);
			return true;
		}
		else {
			// Create new term
			$result = wp_insert_term($this->title, $this->taxonomy, $args);
			
			if (is_wp_error($result)) {
				CMS::Instance()->log('New tag failed to save: ' . $result->get_error_message());
				return false;
			}
			
			$this->id = $result['term_id'];
			
			// Save term meta
			update_term_meta($this->id, 'alba_state', $this->state);
			update_term_meta($this->id, 'alba_public', $this->public);
			update_term_meta($this->id, 'alba_note', $this->note);
			update_term_meta($this->id, 'alba_filter', $this->filter);
			update_term_meta($this->id, 'alba_image', $this->image);
			update_term_meta($this->id, 'alba_category', $this->category);
			update_term_meta($this->id, 'alba_custom_fields', $this->custom_fields);
			update_term_meta($this->id, 'alba_domain', $domain);
			update_term_meta($this->id, 'alba_content_types', $this->contenttypes);
			
			Actions::add_action("tagcreate", (object) [
				"affected_tag"=>$this->id,
			]);
			
			Hook::execute_hook_actions('on_tag_save', $this);
			return true;
		}
	}
}