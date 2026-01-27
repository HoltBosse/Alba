<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class MediaStateMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `media` LIKE 'state'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Media table missing state column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Media table has state column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Media table OK.");
        } else {
            DB::exec("ALTER TABLE `media` ADD `state` int DEFAULT 1;");
            return new Message(true, MessageType::Success, "Media table updated.");
        }
    }
}