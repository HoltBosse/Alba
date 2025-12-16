<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationDomainMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `configurations` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Configurations table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Configurations table has domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Configurations table OK.");
        } else {
            DB::exec("ALTER TABLE `configurations` DROP PRIMARY KEY;");
            DB::exec("ALTER TABLE `configurations` ADD `domain` INT NOT NULL default 0;");

            DB::exec("ALTER TABLE `configurations` ADD UNIQUE KEY `name_domain` (`name`,`domain`);");

            return new Message(true, MessageType::Success, "Configurations table updated.");
        }
    }
}