<?php
namespace HoltBosse\Alba\Core;

/**
 * Cache - WordPress transients wrapper
 * Provides wrapper methods for WordPress transient API
 */
class Cache {
    public function __construct() {
        // WordPress handles cache initialization
    }

    /**
     * Check if cache should be ignored for this request
     */
    public function ignore(string $request, ?string $type=null): bool {
        if(isset($_ENV["cache_ignore"])) {
            foreach (explode(",", $_ENV["cache_ignore"]) as $partial_path) {
                if (strpos($request, $partial_path)===0) {
                    // ignore
                    if ($type==='url') {
                        // output nice message for full URL cache situation
                        echo "<!-- Alba cache IGNORE -->\n";
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Generate cache key from identifier and type
     */
    private function gen_cache_key(string $identifier, string $type): string {
        return 'alba_' . $type . "_" . hash('md4', $identifier);
    }

    /**
     * Get cache expiration time in seconds
     */
    private function get_cache_expiration(): int {
        $config_time = 30; // default 30 minutes
        if(isset($_ENV["cache_time"]) && is_numeric($_ENV["cache_time"])) {
            $config_time = (float) $_ENV["cache_time"];
        }
        return (int) ($config_time * 60); // convert minutes to seconds
    }

    /**
     * Check if cache exists and is not stale
     * Returns the cached data or null if not cached
     * Wrapper for WordPress get_transient()
     */
    public function is_cached(string $identifier, string $type): ?string {
        // first check if path is ignored for urls
        if ($type=='url') {
            if ($this->ignore($identifier, $type)) {
                return null;
            }
        }
        
        $cache_key = $this->gen_cache_key($identifier, $type);
        $cached = get_transient($cache_key);
        
        return $cached !== false ? $cached : null;
    }

    /**
     * Create/update cache
     * Wrapper for WordPress set_transient()
     */
    public function create_cache(string $identifier, string $type='url', string $content=""): void {
        $cache_key = $this->gen_cache_key($identifier, $type);
        $expiration = $this->get_cache_expiration();
        set_transient($cache_key, $content, $expiration);
    }

    /**
     * Get cache content (for backwards compatibility)
     */
    public function get_cache(string $identifier): string {
        // For backwards compatibility - assumes identifier is the cache key
        $cached = get_transient($identifier);
        return $cached !== false ? $cached : '';
    }

    /**
     * Serve cached content directly
     */
    public function serve_cache(string $identifier): void {
        $cached = $this->get_cache($identifier);
        echo $cached;
    }

    /**
     * Serve cached page and exit
     */
    public function serve_page(string $identifier): void {
        $cached = get_transient($identifier);
        if ($cached !== false) {
            echo "<!-- Alba cache (WordPress transients) -->\n";
            echo $cached;
            exit();
        }
    }

    /**
     * Delete a specific cache entry
     * Wrapper for WordPress delete_transient()
     */
    public function delete_cache(string $identifier, string $type='url'): bool {
        $cache_key = $this->gen_cache_key($identifier, $type);
        return delete_transient($cache_key);
    }

    /**
     * Clear all Alba caches
     * Uses WordPress database query to delete all transients starting with 'alba_'
     */
    public function clear_all_caches(): bool {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_alba_%' 
             OR option_name LIKE '_transient_timeout_alba_%'"
        );
        return true;
    }
}