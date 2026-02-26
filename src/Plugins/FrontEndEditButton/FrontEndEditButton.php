<?php
namespace HoltBosse\Alba\Plugins\FrontEndEditButton;

Use HoltBosse\Alba\Core\{CMS, Plugin, Widget};
Use HoltBosse\Form\{Form, Input};
Use \PDO;
Use HoltBosse\DB\DB;
Use \stdClass;

class FrontEndEditButton extends Plugin {
    public function init(): void {
        CMS::add_action("on_widget_render",$this,'handle_widget_render'); // label, function, priority 
    }

    private function get_data(): stdClass {
        $pluginOptions = array_combine(array_column($this->options, 'name'), array_column($this->options, 'value'));
        $groupOptionsArray = json_decode($pluginOptions["access"] ?? "") ?? [];
        $userGroups = DB::fetchAll("SELECT group_id FROM user_groups WHERE user_id=?", CMS::Instance()->user->id, ["mode"=>PDO::FETCH_COLUMN]);

        return (object) [
            "pluginOptions" => $pluginOptions,
            "groupOptionsArray" => $groupOptionsArray,
            "userGroups" => $userGroups,
        ];
    }

    // @phpstan-ignore-next-line missingType.iterableValue
    private function validateGroup(array $groupOptions, array $userGroups): bool {
        foreach($userGroups as $item) {
            if(in_array($item, $groupOptions)) {
                return true;
            }
        }
        return false;
    }

    public function handle_widget_render(string $page_contents, mixed $params): string {
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
}




