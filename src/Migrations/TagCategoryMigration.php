<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;

class TagCategoryMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tags' AND COLUMN_NAME = 'category'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Tag table missing category column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Tag table has category column");
            }
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Tags table OK.");
        } else {
            DB::exec("ALTER TABLE tags ADD COLUMN `category` int(11) DEFAULT 0");
            return new Message(true, MessageType::Success, "Tags table updated.");
        }
    }
}