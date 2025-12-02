<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class TagCustomFieldsMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tags' AND COLUMN_NAME = 'custom_fields'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Tag table missing custom fields column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Tag table has custom fields column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Tags table OK.");
        } else {
            DB::exec("ALTER TABLE tags ADD COLUMN `custom_fields` text");
            return new Message(true, MessageType::Success, "Tags table updated.");
        }
    }
}