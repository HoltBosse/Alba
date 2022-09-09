<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Cache {
    public function __construct() {
        // make sure cache path exists
        if (!is_dir(CMSPATH . "/cache")) {
            mkdir(CMSPATH . "/cache", 0755);
        }
    }

    private function gen_cache_filename($identifier, $type) {
        return $type . "_" . hash('md4', $identifier);
    }

    public function url_cached($request) {
        // checks if cached page exists and is older than cache
        // TODO time check based on Config::$caching_time
        $filename = $this->gen_cache_filename($request, 'url');
        $fullpath = CMSPATH . "/cache/" . $filename;
        if (file_exists($fullpath)) {
            return $fullpath;
        }
        return false;
    }

    public function create_cache($identifier, $type, $content) {
        $filename = $this->gen_cache_filename($identifier, 'url');
        $fullpath = CMSPATH . "/cache/" . $filename;
        file_put_contents($fullpath, $content);
    }

    public function serve_page($filepath) {
        // todo: headers?
        echo readfile($filepath);
        exit();
    }
}