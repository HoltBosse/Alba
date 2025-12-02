<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class RedirectsDomainMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `redirects` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Redirects table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Redirects table has domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Redirects table OK.");
        } else {
            DB::exec("ALTER TABLE `redirects` ADD `domain` text NOT NULL;");
            DB::exec("UPDATE `redirects` SET `domain`=?", CMS::getDomainIndex($_SERVER["HTTP_HOST"]));
            return new Message(true, MessageType::Success, "Redirects table updated.");
        }
    }
}