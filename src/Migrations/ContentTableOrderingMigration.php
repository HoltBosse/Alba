<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS, Content};
use HoltBosse\DB\DB;

class ContentTableOrderingMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $content_table_ordering_ok = true;
            $contentTypes = Content::get_all_content_types();
            foreach($contentTypes as $type) {
                $tableName = Content::get_table_name_for_content_type($type->id);

                $orderingOk = DB::fetchAll(
                    "SELECT `ordering`, COUNT(`ordering`) AS ordering_count
                    FROM `$tableName`
                    GROUP BY `ordering`
                    HAVING COUNT(`ordering`) > 1"
                );

                if(sizeof($orderingOk)>0) {
                    $content_table_ordering_ok = false;
                }
            }
            $result = $content_table_ordering_ok;
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Content tables have ordering conflicts");
            } else {
                $this->status = new Message(true, MessageType::Success, "Content tables ordering OK");
            }
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Content table ordering OK.");
        } else {
            $contentTypes = Content::get_all_content_types();
            foreach($contentTypes as $type) {
                $tableName = Content::get_table_name_for_content_type($type->id);

                DB::exec("SET @manualordering := 0;");
                DB::exec(
                    "UPDATE `$tableName` cbh
                    JOIN (
                        SELECT id, (@manualordering := @manualordering + 1) AS new_ordering
                        FROM `$tableName`
                        ORDER BY ordering, id
                    ) ordered_items ON cbh.id = ordered_items.id
                    SET cbh.ordering = ordered_items.new_ordering;"
                );
            }
            return new Message(true, MessageType::Success, "Content table ordering updated.");
        }
    }
}