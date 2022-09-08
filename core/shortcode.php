<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Shortcode {
    public $title;
	public $fn;

    public function __construct($title, $function) {
        $this->title = $title; // must contain no spaces/special chars
        $this->fn = $function;
        $this->register();
    }

    private function register() {
        CMS::Instance()->shortcodes[$this->title] = $this;
    }

    public static function get_shortcode_attributes_regex() {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/'; 
    }

    function shortcode_parse_attributes($attributes_text) {
        // TODO: clean room implementation
        $attributes = [];
        $pattern = Shortcode::get_shortcode_attributes_regex();
        $attributes_text = preg_replace( "/[\x{00a0}\x{200b}]+/u", ' ', $attributes_text );
        if ( preg_match_all( $pattern, $attributes_text, $match, PREG_SET_ORDER ) ) {

            foreach ( $match as $m ) {
                if ( ! empty( $m[1] ) ) {
                    $attributes[ strtolower( $m[1] ) ] = stripcslashes( $m[2] );
                } elseif ( ! empty( $m[3] ) ) {
                    $attributes[ strtolower( $m[3] ) ] = stripcslashes( $m[4] );
                } elseif ( ! empty( $m[5] ) ) {
                    $attributes[ strtolower( $m[5] ) ] = stripcslashes( $m[6] );
                } elseif ( isset( $m[7] ) && strlen( $m[7] ) ) {
                    $attributes[] = stripcslashes( $m[7] );
                } elseif ( isset( $m[8] ) && strlen( $m[8] ) ) {
                    $attributes[] = stripcslashes( $m[8] );
                } elseif ( isset( $m[9] ) ) {
                    $attributes[] = stripcslashes( $m[9] );
                }
            }
    
            // Reject any unclosed HTML elements.
            foreach ( $attributes as &$value ) {
                if ( false !== strpos( $value, '<' ) ) {
                    if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
                        $value = '';
                    }
                }
            }
        } else {
            $attributes = ltrim( $text );
        }
        return $attributes;
    }

    public static function get_shortcode_regex($tagnames=null) {
        if (!$tagnames) {
            // none passed, play safe and get all registered
            $tagnames = CMS::Instance()->shortcode_tags;
        }
        // TODO: clean room implementation
        $tagregexp = implode( '|', array_map( 'preg_quote', $tagnames ) );
        return '\\['                             // Opening bracket.
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
            . "($tagregexp)"                     // 2: Shortcode name.
            . '(?![\\w-])'                       // Not followed by word character or hyphen.
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag...
            .     '\\]'                          // ...and closing bracket.
            . '|'
            .     '\\]'                          // Closing bracket.
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
            .             '[^\\[]*+'             // Not an opening bracket.
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
            .                 '[^\\[]*+'         // Not an opening bracket.
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag.
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]].
    }

    public static function exec_all($contents) {
        $CMS = CMS::Instance();
        // early checks for quick exits
        if (!$CMS->shortcodes) {
            return $contents;
        }
        if (strpos($contents, '[') === false) {
            return $contents;
        }
        // find shortcode tags in content
        // if none match registered shortcodes, another early exit
        preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $contents, $matches );
        $required_shortcode_tags = array_intersect( array_keys($CMS->shortcodes), $matches[1] );
        if (!$required_shortcode_tags) {
            return $contents;
        }
        $pattern = Shortcode::get_shortcode_regex($required_shortcode_tags);
	    $contents = preg_replace_callback( "/$pattern/", 'Shortcode::apply_shortcode', $contents );
        return $contents;
    }

    public static function apply_shortcode($match) {
        // escape double [[ ]]
        if ( '[' === $match[1] && ']' === $match[6] ) {
            return substr( $match[0], 1, -1 );
        }
        $title = $match[2];
        $attributes = Shortcode::shortcode_parse_attributes($match[3]);
        $shortcode_content = $match[5] ?? null;
        $new_contents = (CMS::Instance()->shortcodes[$title]->fn)($shortcode_content, $attributes, $title);
        return $new_contents;
    }
}

// test shortcode
$image_shortcode = new Shortcode("image", function($shortcode_content, $attributes, $title){
    $img = new Image($attributes['id']);
    if ($img) {
        return $img->render('web',$class, false, $attributes); // false - output immediately turned off to capture markup
    }
    else {
        return "<span>&nbsp;Image Not Found&nbsp;</span>";
    }
});