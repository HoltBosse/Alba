<?php
namespace HoltBosse\Alba\Plugins\FrontEndEditButton;

Use HoltBosse\Alba\Core\{CMS, Plugin, Widget};
Use HoltBosse\Form\{Form, Input};
Use \PDO;
Use HoltBosse\DB\DB;

class FrontEndEditButton extends Plugin {
    public function init() {
        CMS::add_action("on_widget_render",$this,'handle_widget_render'); // label, function, priority 
        CMS::add_action("before_content_item_render",$this,'handle_before_content_item_render');
    }

    private function get_data() {
        $pluginOptions = array_combine(array_column($this->options, 'name'), array_column($this->options, 'value'));
        $groupOptionsArray = json_decode($pluginOptions["access"] ?? "") ?? [];
        $userGroups = DB::fetchAll("SELECT group_id FROM user_groups WHERE user_id=?", CMS::Instance()->user->id, ["mode"=>PDO::FETCH_COLUMN]);

        return (object) [
            "pluginOptions" => $pluginOptions,
            "groupOptionsArray" => $groupOptionsArray,
            "userGroups" => $userGroups,
        ];
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
        $data = $this->get_data();
        if($this->validateGroup($data->groupOptionsArray, $data->userGroups) && !CMS::Instance()->isAdmin()) {
            $page_contents = "
                <div class='front_end_edit_wrap' >
                    <a style='' href='/admin/widgets/edit/{$params[0]->id}'>EDIT &ldquo;" . Input::stringHtmlSafe($params[0]->title) . "&rdquo;</a>
                </div>
            " . $page_contents;

            return $page_contents;
        } else {
            return $page_contents;
        }
    }

    public function handle_before_content_item_render($params) {
        // render EDIT CONTENT button for admins on front-end
        // if called by 'before_content_item_render' Hook action can be added to any 'single' content items controller view
        // expects params[0] to be content_type and params[1] to be content_id and params[2] to be optional title
        $data = $this->get_data();
        if($this->validateGroup($data->groupOptionsArray, $data->userGroups) && !CMS::Instance()->isAdmin()) {
            if (isset($params[2])) {
                $title = "&ldquo;" . Input::stringHtmlSafe($params[2]) . "&rdquo;";
            }
            else {
                $title = "CONTENT";
            }
            ?>
            <div class='front_end_edit_wrap' >
                <a style='' href='https://cms.bobmitch.com/admin/content/edit/<?=$params[0];?>/<?=$params[1];?>'>EDIT <?=$title;?></a>
            </div>
            <?php
        }
    }
}




