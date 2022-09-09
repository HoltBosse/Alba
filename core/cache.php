<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Cache {
    public function __construct() {
        // make sure cache path exists
        if (!is_dir(CMSPATH . "/cache")) {
            mkdir(CMSPATH . "/cache", 0755);
        }
    }

    public function ignore($request) {
        foreach (Config::$caching['ignore'] as $partial_path) {
            if (strpos($request, $partial_path)===0) {
                // ignore
                return true;
            }
        } 
    }

    private function gen_cache_filename($identifier, $type) {
        return $type . "_" . hash('md4', $identifier);
    }

    public function is_cached($identifier, $type) {
        // checks if cache file for an identifier/type exists 
        // and that it isn't stale
        // if it's good, returns full path to cache file
        // otherwise returns false

        // first check if path is ignored for urls
        if ($type=='url') {
            if ($this->ignore($identifier)) {
                return false;
            }
        }
        $filename = $this->gen_cache_filename($identifier, $type);
        $fullpath = CMSPATH . "/cache/" . $filename;
        if (file_exists($fullpath)) {
            $curtime = time();
            $filetime = filemtime($fullpath);
            $file_stale_time = $filetime + (Config::$caching['time'] * 60);
            if ($filetime && is_numeric($file_stale_time)) {
                if ($file_stale_time > $curtime) {
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

    public function create_cache($identifier, $type='url', $content="") {
        // content agnostic - type commonly 'url' for full page
        // but can be extended to create cache for anything
        $filename = $this->gen_cache_filename($identifier, $type);
        $fullpath = CMSPATH . "/cache/" . $filename;
        file_put_contents($fullpath, $content);
    }

    public function get_cache($filepath) {
        return file_get_contents($filepath);
    }

    public function serve_cache($filepath) {
        readfile($filepath);
    }

    public function serve_page($filepath) {
        // todo: headers?
        if ($this->ignore($request)) {
            return false;
        }
        readfile($filepath);
        exit();
    }
}