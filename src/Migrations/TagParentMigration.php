<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class TagParentMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tags' AND COLUMN_NAME = 'parent'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Tag table missing parent column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Tag table has parent column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Tags table OK.");
        } else {
            DB::exec("ALTER TABLE tags ADD COLUMN `parent` int(11) DEFAULT NULL");
            return new Message(true, MessageType::Success, "Tags table updated.");
        }
    }
}