<?php
namespace HoltBosse\Alba\Core;

class Cache {
    public function __construct() {
        // make sure cache path exists
        if (!is_dir($_ENV["cache_root"] . "/cache")) {
            mkdir($_ENV["cache_root"] . "/cache", 0755);
        }
    }

    public function ignore($request, $type=null) {
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
            if ($this->ignore($identifier, $type)) {
                return false;
            }
        }
        $filename = $this->gen_cache_filename($identifier, $type);
        $fullpath = $_ENV["cache_root"] . "/cache/" . $filename;
        if (file_exists($fullpath)) {
            $curtime = time();
            $filetime = filemtime($fullpath);
            $config_time = 30;
            if(isset($_ENV["cache_time"]) && is_numeric($_ENV["cache_time"])) {
                $config_time = (double) $_ENV["cache_time"];
            }
            $file_stale_time = $filetime + ($config_time * 60);
            // @phpstan-ignore-next-line
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
        $fullpath = $_ENV["cache_root"] . "/cache/" . $filename;
        file_put_contents($fullpath, $content);
    }

    public function get_cache($filepath) {
        return file_get_contents($filepath);
    }

    public function serve_cache($filepath) {
        readfile($filepath);
    }

    public function serve_page($filepath) {
        echo "<!-- Alba cache: " . date('F j, Y, g:i a', filemtime($filepath)) . " -->\n";
        readfile($filepath);
        exit();
    }
}