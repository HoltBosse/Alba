<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class TagDescriptionTextMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll(
                "SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tags' AND COLUMN_NAME = 'description' AND TABLE_SCHEMA = ? LIMIT 1",
                [$_ENV["dbname"]]
            );

            if(!$result) {
                $this->status = new Message(true, MessageType::Success, "Tags table description column not found");
            } elseif(strtolower($result[0]->DATA_TYPE) === 'varchar') {
                $this->status = new Message(false, MessageType::Warning, "Tags table description column is varchar");
            } else {
                $this->status = new Message(true, MessageType::Success, "Tags table description column is text");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Tags table OK.");
        } else {
            DB::exec("ALTER TABLE `tags` MODIFY COLUMN `description` text;");
            return new Message(true, MessageType::Success, "Tags table updated.");
        }
    }
}
