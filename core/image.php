<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Image {
	public $id;
    public $filename;
    public $width;
    public $height;
    public $title;
    public $alt;
    public $mimetype;
    public static $image_sizes = [
		"thumb"=>200,
		"web"=>1920
	];
    // modified also available, but almost certainly never needed front-end
    
    public function __construct($id) {
        if (!is_numeric($id)) {
            CMS::Instance()->show_error('Cannot create image object from non-numerical id');
        }
        else {
            $this->id = $id;
            $db_image = DB::fetch('select * from media where id=?', $this->id);
            $this->filename = $db_image->filename;
            $this->width = $db_image->width;
            $this->height = $db_image->height;
            $this->title = $db_image->title;
            $this->alt = $db_image->alt;
            $this->mimetype = $db_image->mimetype;
        }       
    }
    
    public function render($size="original", $class="", $output_immediately=true, $attributes=[]) {
        // handle attributes
        $class = $attributes['class'] ?? '';
        $w = $attributes['w'] ?? null;
        $q = $attributes['q'] ?? null;
        $fmt = $attributes['fmt'] ?? null;
        $width_param = $this->width;
        $height_param = $this->height;
        if ($w && is_numeric($w)) {
            if ($w < $this->width) {
                $width_param = $w;
                // scale height
                $height_param = floor(($w/$this->width)*$this->height);
            }
        }

        // build url
        $url_domain_path = Config::$uripath . "/image/" . $this->id . "?";
        $url_params = [];
        if ($w) {$url_params['w'] = $w; }
        if ($q) {$url_params['q'] = $q; }
        if ($fmt) {$url_params['fmt'] = $fmt; }
        $url_params_string = http_build_query($url_params);
        $url = $url_domain_path . $url_params_string;
        $markup = "<img decode='async' width='{$width_param}' height='{$height_param}' loading='lazy' class='rendered_img {$class}' src='".$url."' alt='{$this->alt}' title='{$this->title}'/>";
        if ($output_immediately) {
            echo $markup;
        }
        else {
            return $markup;
        }
    }
}
