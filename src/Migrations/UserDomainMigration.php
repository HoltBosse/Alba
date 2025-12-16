<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class UserDomainMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `users` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Users table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Users table has domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Users table OK.");
        } else {
            DB::exec("ALTER TABLE `users` ADD `domain` INT NOT NULL default 0;");
            
            DB::exec("ALTER TABLE `users` DROP INDEX `email`;");
            DB::exec("ALTER TABLE `users` DROP INDEX `username`;");

            DB::exec("ALTER TABLE `users` ADD UNIQUE KEY `email_domain` (`email`,`domain`);");
            DB::exec("ALTER TABLE `users` ADD UNIQUE KEY `username_domain` (`username`,`domain`);");

            return new Message(true, MessageType::Success, "Users table updated.");
        }
    }
}