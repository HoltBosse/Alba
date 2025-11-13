<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;

class CategoryCustomFieldsMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'categories' AND COLUMN_NAME = 'custom_fields'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Category table missing custom fields column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Category table has custom fields column");
            }
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Category table OK.");
        } else {
            DB::exec("ALTER TABLE categories ADD COLUMN `custom_fields` text");
            return new Message(true, MessageType::Success, "Category table updated.");
        }
    }
}