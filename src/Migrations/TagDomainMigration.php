<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class TagDomainMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `tags` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Tags table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Tags table has domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Tags table OK.");
        } else {
            DB::exec("ALTER TABLE `tags` ADD `domain` int;"); //nullable int column, null means current domain aka all domains
            return new Message(true, MessageType::Success, "Tags table updated.");
        }
    }
}