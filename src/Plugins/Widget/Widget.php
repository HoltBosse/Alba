<?php
namespace HoltBosse\Alba\Plugins\Widget;

Use HoltBosse\Alba\Core\{CMS, Plugin, Widget as CmsWidget};
Use HoltBosse\DB\DB;

class Widget extends Plugin {
    public function init() {
        CMS::add_action("content_ready_frontend",$this,'insert_widget'); // label, function, priority  
    }

    public function insert_widget ($page_contents, ...$args) {
        // FILTER
        $override_year = $this->get_option('testoption');
        $matches = [];
        preg_match_all("^\{{widget (.*?)\}}^", $page_contents, $matches, PREG_PATTERN_ORDER);
        //CMS::pprint_r ($matches);
        
        for ($n=0; $n<sizeof($matches[0]); $n++) {
            $widget_markup = ""; // default to blank string
            $widget_snippet = $matches[0][$n];
            $widget_id = $matches[1][$n];
            $widget = DB::fetch('SELECT * FROM widgets WHERE id=? AND state>0',$widget_id);
            // if widget loaded correctly
            if ($widget) {
                // create widget obj
                $type_info = CmsWidget::get_widget_type($widget->type);
                $widget_class_name = CmsWidget::getWidgetClass($type_info->location);
                $widget_of_type = new $widget_class_name();
                $widget_of_type->load($widget->id);
                // output widget into buffer and store
                ob_start();
                $widget_of_type->internal_render();
                $widget_markup = ob_get_contents();
                ob_end_clean();
            }
            else {
                $widget_markup = "<p class='note'>Unable to load widget {$widget_id}</p>";
            }
            $page_contents = str_replace($widget_snippet, $widget_markup, $page_contents);
            //$page_contents = str_replace("<p>" . $widget_snippet . "</p>", $widget_markup, $page_contents);
        }

        return $page_contents;
    }
}





