<?php
namespace HoltBosse\Alba\Actions\ContentCreate;

use HoltBosse\Alba\Core\{Content, CMS, Actions};
use HoltBosse\DB\DB;
Use HoltBosse\Form\Form;

class ContentCreate extends Actions {

    public function display(): void {
        $contentTableName = Content::get_table_name_for_content_type($this->options->content_type);
        $contentDetails = DB::fetch("SELECT * FROM `{$contentTableName}` WHERE id=?", $this->options->content_id);

        $url = null;
        if($contentDetails->state>0) {
            $url = "/admin/content/edit/" . $contentDetails->id . "/" . $contentDetails->content_type;
        }

        $contentTypeLabel = DB::fetch("SELECT * FROM content_types WHERE id=?", $contentDetails->content_type);

        $this->render_row($url, "Created \"$contentTypeLabel->title\" Content: " . $contentDetails->title);
    }

    public function display_diff(object $viewmore): string {
        ob_start();

        $customFields = json_decode(file_get_contents(Content::getContentControllerPath(Content::get_content_location($this->options->content_type)) . "/custom_fields.json"))->fields;
        $fieldTypeLookup = [];
        //CMS::pprint_r($customFields);
        
        foreach($customFields as $field) {
            $fieldTypeLookup[$field->name] = $field;
        }


        $coreFields = ["id", "state", "ordering", "title", "alias", "content_type", "start", "end", "created_by", "updated_by", "note", "category"];
        $coreFieldsRender = [
            "state"=>function($input) {
                if($input==1) {
                    return "Published";
                } elseif($input==0) {
                    return "Unpublished";
                } else {
                    //TODO: handle custom statuses
                    return $input;
                }
            },
            "created_by"=>function($input) {
                $user = DB::fetch("SELECT * FROM users WHERE id=?", $input ?? 0);
                return $user ? "$user->username ($user->email)" : null;
            },
            "updated_by"=>function($input) {
                $user = DB::fetch("SELECT * FROM users WHERE id=?", $input ?? 0);
                return $user ? "$user->username ($user->email)" : null;
            },
            "category"=>function($input) {
                if($input == 0) {
                    return "none";
                } else {
                    // @phpstan-ignore-next-line
                    $category = DB::fetch("SELECT * FROM categories WHERE id=?", $input ?? 0);
                    return $category->title;
                }
            }
        ];
        
        foreach(json_decode($viewmore->json) as $field=>$item) {
            if(in_array($field, $coreFields)) {
                echo "
                    <tr>
                        <td>$field</td>
                        <td>" . (isset($coreFieldsRender[$field]) ? $coreFieldsRender[$field]($item->before) : $item->before) . "</td>
                        <td>" . (isset($coreFieldsRender[$field]) ? $coreFieldsRender[$field]($item->after) : $item->after) . "</td>
                    </tr>
                ";
            } else {
                $classname = Form::getFieldClass($fieldTypeLookup[$field]->type);
                $beforeFieldInstance = new $classname();
                $afterFieldInstance = new $classname();

                $fieldTypeLookup[$field]->default = $item->before;
                $beforeFieldInstance->loadFromConfig($fieldTypeLookup[$field]);
                $fieldTypeLookup[$field]->default = $item->after;
                $afterFieldInstance->loadFromConfig($fieldTypeLookup[$field]);

                echo "
                    <tr>
                        <td>$field</td>
                        <td>{$beforeFieldInstance->getFriendlyValue((object)["return_in_text_html_form"=>true])}</td>
                        <td>{$afterFieldInstance->getFriendlyValue((object)["return_in_text_html_form"=>true])}</td>
                    </tr>
                ";
            }
        }

        $buffer = ob_get_clean();

        if($buffer=="") {
            return "
                <tr>
                    <td>empty</td>
                    <td>empty</td>
                    <td>empty</td>
                </tr>
            ";
        } else {
            return $buffer;
        }
    }
}