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
        // checks if cached file for a request exists and isn't stale
        // if it's good, returns full path to cache file
        // otherwise returns false
        $filename = $this->gen_cache_filename($request, 'url');
        $fullpath = CMSPATH . "/cache/" . $filename;
        if (file_exists($fullpath)) {
            $curtime = time();
            $cache_stale_time = 
            $filetime = filemtime($fullpath);
            if ($filetime && is_numeric($cache_stale_time)) {
                $file_stale_time = $filetime + (Config::$cache['time'] * 60);
                if ($file_stale_time <= $curtime) {
                    // cache not stale yet
                    return $fullpath;
                }
                else {
                    // cache is stale - delete file
                    unlink($fullpath);
                }
            }
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