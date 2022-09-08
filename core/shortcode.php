<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Shortcode {
    public $title;
	public $fn;

    public function __construct($title, $function) {
        $this->title = $title;
        $this->fn = $function;
        $this->register();
    }

    private function register() {
        CMS::Instance()->shortcodes[] = $this;
    }

    public function exec_all($contents) {
        foreach (CMS::Instance()->shortcodes as $sc) {
            $contents = ($sc->fn)($contents);
        }
        return $contents;
    }
}

// test shortcode
$foo = new Shortcode("boo", function($contents, ...$args){
    return $contents . "<h1>boo</h1>" ;
});