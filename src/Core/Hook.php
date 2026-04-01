<?php
namespace HoltBosse\Alba\Core;

/**
 * Hook - WordPress hook wrapper
 * Provides wrapper methods for WordPress hook system
 */
class Hook {
	public ?string $label;
	// @phpstan-ignore missingType.iterableValue
    public array $actions;

	public function __construct() {
		$this->label = null;
		$this->actions = [];
	}

	/**
	 * Count actions registered for a hook
	 * Wrapper for WordPress has_action()
	 */
	static public function count_actions_for_hook (string $hook_label): int {
		global $wp_filter;
		if (isset($wp_filter[$hook_label])) {
			return count($wp_filter[$hook_label]->callbacks);
		}
		return 0;
	}

	/**
	 * Execute hook actions
	 * Wrapper for WordPress do_action()
	 */
	static public function execute_hook_actions (string $hook_label, mixed ...$args): bool {
		do_action($hook_label, ...$args);
		return true;
	}

	/**
	 * Execute hook filters
	 * Wrapper for WordPress apply_filters()
	 */
	static public function execute_hook_filters (string $hook_label, mixed $data, mixed ...$args): mixed {
		return apply_filters($hook_label, $data, ...$args);
	}

	/**
	 * Add an action hook
	 * Wrapper for WordPress add_action()
	 */
	static public function add_action(string $hook_label, callable $callback, int $priority = 10, int $accepted_args = 1): bool {
		return add_action($hook_label, $callback, $priority, $accepted_args);
	}

	/**
	 * Add a filter hook
	 * Wrapper for WordPress add_filter()
	 */
	static public function add_filter(string $hook_label, callable $callback, int $priority = 10, int $accepted_args = 1): bool {
		return add_filter($hook_label, $callback, $priority, $accepted_args);
	}

	/**
	 * Remove an action hook
	 * Wrapper for WordPress remove_action()
	 */
	static public function remove_action(string $hook_label, callable $callback, int $priority = 10): bool {
		return remove_action($hook_label, $callback, $priority);
	}

	/**
	 * Remove a filter hook
	 * Wrapper for WordPress remove_filter()
	 */
	static public function remove_filter(string $hook_label, callable $callback, int $priority = 10): bool {
		return remove_filter($hook_label, $callback, $priority);
	}

	/**
	 * Check if action has been added
	 * Wrapper for WordPress has_action()
	 */
	static public function has_action(string $hook_label, callable|false $callback = false): bool|int {
		return has_action($hook_label, $callback);
	}

	/**
	 * Check if filter has been added
	 * Wrapper for WordPress has_filter()
	 */
	static public function has_filter(string $hook_label, callable|false $callback = false): bool|int {
		return has_filter($hook_label, $callback);
	}
}