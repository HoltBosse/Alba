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
            if (Config::debug()) {
                CMS::pprint_r("Cannot create image object from non-numerical id");
                CMS::pprint_r(debug_backtrace());
                die();
            } else {
                CMS::Instance()->show_error('Cannot create image object from non-numerical id');
            }
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
    
    public function render($size="", $class="", $output_immediately=true, $attributes=[]) {
        // size and class used in v <= 2.4.77
        // $w attribute supercedes $size
        // kept for back compat - class param and class passed via attribute are combined
        // handle attributes
        $class = $class . " " . ($attributes['class'] ?? ''); 
        $w = $attributes['w'] ?? null;
        if (!$w) {
            if ($size && !is_numeric($size)) {
                // no w attribute, string size - figure out or default to 1920
                $w = $this->image_sizes[$size] ?? '1920';
            }
            elseif ($size) {
                // no $w attr, but got numeric $size
                $w = $size;
            }
            else {
                $w = $this->width; // default to og size if no $size or $w attr
            }
        }
        $q = $attributes['q'] ?? null;
        $fmt = $attributes['fmt'] ?? null;
        $loading = $attributes['loading'] ?? "lazy"; // use eager for headings
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
        $url_domain_path = Config::uripath() . "/image/" . $this->id . "?";
        $url_params = [];
        if ($w) {$url_params['w'] = $w; }
        if ($q) {$url_params['q'] = $q; }
        if ($fmt) {$url_params['fmt'] = $fmt; }
        $url_params_string = http_build_query($url_params);
        $url = $url_domain_path . $url_params_string;
        $markup = "<img decode='async' width='{$width_param}' height='{$height_param}' loading='{$loading}' class='rendered_img {$class}' src='".$url."' alt='{$this->alt}' title='{$this->title}'/>";
        if ($output_immediately) {
            echo $markup;
        }
        else {
            return $markup;
        }
    }
}
