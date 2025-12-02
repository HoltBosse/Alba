<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class GroupDomainMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `groups` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Groups table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Groups table has domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Pages table OK.");
        } else {
            DB::exec("ALTER TABLE `groups` ADD `domain` text;"); //nullable text column, null means current domain aka all domains
            DB::exec("UPDATE `groups` SET `domain` = ? WHERE id=1;", [0]); //for single site installs, set default group to domain 0
            return new Message(true, MessageType::Success, "Groups table updated.");
        }
    }
}