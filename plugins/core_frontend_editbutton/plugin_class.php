<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_frontend_editbutton extends Plugin {
    public function init() {
        CMS::add_action("on_widget_render",$this,'handle_widget_render'); // label, function, priority  
    }

    private function validateGroup($groupOptions, $userGroups) {
        foreach($userGroups as $item) {
            if(in_array($item, $groupOptions)) {
                return true;
            }
        }
        return false;
    }

    public function handle_widget_render($page_contents, $params) {
        $pluginOptions = array_combine(array_column($this->options, 'name'), array_column($this->options, 'value'));
        $groupOptionsArray = json_decode($pluginOptions["access"] ?? "") ?? [];
        $userGroups = DB::fetchall("SELECT group_id FROM user_groups WHERE user_id=?", CMS::Instance()->user->id, ["mode"=>PDO::FETCH_COLUMN]);

        if($this->validateGroup($groupOptionsArray, $userGroups)) {
            $page_contents = "
                <div class='front_end_edit_wrap' >
                    <a style='' target='_blank' href='/admin/widgets/edit/{$params[0]->id}'>EDIT &ldquo;" . htmlspecialchars($params[0]->title) . "&rdquo;</a>
                </div>
            " . $page_contents;
            return $page_contents;
        } else {
            return $page_contents;
        }
    }
}




