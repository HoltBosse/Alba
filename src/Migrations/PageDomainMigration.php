<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class PageDomainMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `pages` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Pages table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Pages table has domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Pages table OK.");
        } else {
            DB::exec("ALTER TABLE `pages` ADD `domain` text NOT NULL;");
	        DB::exec("UPDATE `pages` SET `domain`=?", CMS::getDomainIndex($_SERVER["HTTP_HOST"]));
            return new Message(true, MessageType::Success, "Pages table updated.");
        }
    }
}