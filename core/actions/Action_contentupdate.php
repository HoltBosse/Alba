<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Action_contentupdate extends Actions {

    public function display() {
        $contantTableName = Content::get_table_name_for_content_type($this->options->content_type);
        $contentDetails = DB::fetch("SELECT * FROM `{$contantTableName}` WHERE id=?", $this->options->content_id);

        $url = null;
        if($contentDetails->state>0) {
            $url = "/admin/content/edit/" . $contentDetails->id . "/" . $contentDetails->content_type;
        }

        $contentTypeLabel = DB::fetch("SELECT * FROM content_types WHERE id=?", $contentDetails->content_type);

        $this->render_row($url, "Updated \"$contentTypeLabel->title\" Content: " . $contentDetails->title, false);
    }

    public function display_diff($viewmore) {
        ob_start();

        $customFields = json_decode(file_get_contents(CMSPATH . "/controllers/" . Content::get_content_location($this->options->content_type) . "/custom_fields.json"))->fields;
        $fieldTypeLookup = [];
        //CMS::pprint_r($customFields);
        
        foreach($customFields as $field) {
            $fieldTypeLookup[$field->name] = $field;
        }


        $coreFields = ["id", "state", "ordering", "title", "alias", "content_type", "start", "end", "created_by", "updated_by", "note", "category"];
        
        foreach(json_decode($viewmore->json) as $field=>$item) {
            if(in_array($field, $coreFields)) {
                echo "
                    <tr>
                        <td>$field</td>
                        <td>$item->before</td>
                        <td>$item->after</td>
                    </tr>
                ";
            } else {
                $classname = "Field_" . $fieldTypeLookup[$field]->type;
                $beforeFieldInstance = new $classname();
                $afterFieldInstance = new $classname();

                $fieldTypeLookup[$field]->default = $item->before;
                $beforeFieldInstance->load_from_config($fieldTypeLookup[$field]);
                $fieldTypeLookup[$field]->default = $item->after;
                $afterFieldInstance->load_from_config($fieldTypeLookup[$field]);

                echo "
                    <tr>
                        <td>$field</td>
                        <td>{$beforeFieldInstance->get_friendly_value([])}</td>
                        <td>{$afterFieldInstance->get_friendly_value([])}</td>
                    </tr>
                ";
            }
        }

        return ob_get_clean();
    }
}