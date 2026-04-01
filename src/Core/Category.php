<?php
namespace HoltBosse\Alba\Core;

Use \Exception;
Use HoltBosse\Form\Form;
Use \StdClass;
Use HoltBosse\Alba\Core\File;

/**
 * Category - WordPress taxonomy wrapper
 * Provides wrapper methods for WordPress category/taxonomy functions
 */
class Category {
	public ?int $id = null;
	public ?string $title = null;
	public ?int $state = null;
	public ?int $content_type = null;
	public ?int $parent = null;
	public ?string $custom_fields = null;
	public ?string $content_location = null;
	public ?int $domain = null;
	private string $taxonomy = 'category'; // Default to WordPress category taxonomy

	public function __construct(int $content_type) {
		$this->id = null;
		$this->title = "";
		$this->state = 1;
		$this->parent = 0;
		$this->content_type = $content_type;
		$this->custom_fields = "";
		$this->domain = null;
		
		// Determine taxonomy based on content_type
		if ($content_type) {
			$this->content_location = Content::get_content_location($this->content_type);
			// Use custom taxonomy for Alba content types
			$this->taxonomy = 'alba_category_' . $content_type;
			
			// Register taxonomy if it doesn't exist
			if (!taxonomy_exists($this->taxonomy)) {
				register_taxonomy($this->taxonomy, 'post', [
					'hierarchical' => true,
					'label' => 'Alba Categories',
					'public' => true,
					'show_ui' => true,
					'show_in_rest' => true,
				]);
			}
		}
	}

	/**
	 * Get all categories by depth (hierarchical)
	 * Wrapper for WordPress get_terms()
	 */
	// @phpstan-ignore missingType.iterableValue
	public static function get_all_categories_by_depth(?int $content_type, int $parent=0, int $depth=-1): array {
		$depth = $depth+1;
		$result=[];
		
		// Determine taxonomy
		$taxonomy = $content_type ? 'alba_category_' . $content_type : 'category';
		
		// Get children
		$args = [
			'taxonomy' => $taxonomy,
			'parent' => $parent,
			'hide_empty' => false,
		];
		
		$children = get_terms($args);
		
		foreach ($children as $child) {
			$term_data = (object) [
				'id' => $child->term_id,
				'title' => $child->name,
				'parent' => $child->parent,
				'state' => 1, // WordPress terms don't have state
				'content_type' => $content_type,
				'depth' => $depth,
				'custom_fields' => get_term_meta($child->term_id, 'alba_custom_fields', true),
				'domain' => get_term_meta($child->term_id, 'alba_domain', true)
			];
			$result[] = $term_data;
			$result = array_merge($result, Category::get_all_categories_by_depth($content_type, $child->term_id, $depth));
		}
		
		return $result;
	}

	/**
	 * Get category count
	 * Wrapper for WordPress wp_count_terms()
	 */
	public static function get_category_count(int|string|null $content_type, string $search=""): stdClass {
		$taxonomy = $content_type ? 'alba_category_' . $content_type : 'category';
		
		$args = [
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
		];
		
		if ($search) {
			$args['search'] = $search;
		}
		
		$count = wp_count_terms($args);
		
		return (object) ['c' => (int) $count];
	}

	/**
	 * Load category by ID
	 * Wrapper for WordPress get_term()
	 */
	public function load(int $id): bool {
		$term = get_term($id, $this->taxonomy);
		
		if ($term && !is_wp_error($term)) {
			$this->id = $term->term_id;
			$this->title = $term->name;
			$this->state = 1; // WordPress terms don't have state
			$this->content_type = get_term_meta($term->term_id, 'alba_content_type', true) ?: $this->content_type;
			$this->parent = $term->parent;
			$this->custom_fields = get_term_meta($term->term_id, 'alba_custom_fields', true);
			$this->domain = get_term_meta($term->term_id, 'alba_domain', true);
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Save category
	 * Wrapper for WordPress wp_insert_term() and wp_update_term()
	 */
	public function save(Form $required_details_form, ?Form $custom_fields_form = null): bool {
		// update this object with submitted and validated form info
		$this->title = $required_details_form->getFieldByName('title')->default;
		$this->state = $required_details_form->getFieldByName('state')->default;
		$this->parent = $required_details_form->getFieldByName('parent')->default; 
		$this->content_type = $required_details_form->getFieldByName('content_type')->default; 
		$this->custom_fields = $custom_fields_form ? json_encode($custom_fields_form, JSON_THROW_ON_ERROR) : "";

		$domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);

		//if shared across all domains
		if (isset($_ENV["category_custom_fields_form_path"])) {
			$customFieldsFormObject = json_decode(File::getContents($_ENV["category_custom_fields_form_path"]));
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

		// Prepare term arguments
		$args = [
			'parent' => $this->parent,
			'slug' => sanitize_title($this->title)
		];

		if ($this->id) {
			// Update existing term
			Actions::add_action("categoryupdate", (object) [
				"affected_category"=>$this->id,
			]);
			
			$result = wp_update_term($this->id, $this->taxonomy, array_merge(['name' => $this->title], $args));
			
			if (is_wp_error($result)) {
				CMS::Instance()->log("Failed to update category: " . $result->get_error_message());
				return false;
			}
			
			// Update term meta
			update_term_meta($this->id, 'alba_state', $this->state);
			update_term_meta($this->id, 'alba_custom_fields', $this->custom_fields);
			update_term_meta($this->id, 'alba_domain', $domain);
			update_term_meta($this->id, 'alba_content_type', $this->content_type);
		}
		else {
			// Create new term
			$result = wp_insert_term($this->title, $this->taxonomy, $args);
			
			if (is_wp_error($result)) {
				CMS::Instance()->log("Failed to create category: " . $result->get_error_message());
				return false;
			}
			
			$this->id = $result['term_id'];
			
			// Save term meta
			update_term_meta($this->id, 'alba_state', $this->state);
			update_term_meta($this->id, 'alba_custom_fields', $this->custom_fields);
			update_term_meta($this->id, 'alba_domain', $domain);
			update_term_meta($this->id, 'alba_content_type', $this->content_type);
			
			Actions::add_action("categorycreate", (object) [
				"affected_category"=>$this->id,
			]);
		}

		Hook::execute_hook_actions('on_category_save', $this);
		return true;
	}

	/**
	 * Delete category
	 * Wrapper for WordPress wp_delete_term()
	 */
	public function delete(): bool {
		if (!$this->id) {
			return false;
		}
		
		$result = wp_delete_term($this->id, $this->taxonomy);
		return !is_wp_error($result);
	}
}