<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class FormDomainMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `form_instances` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Form instances table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Form instances table has domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Form instances table OK.");
        } else {
            DB::exec("ALTER TABLE `form_instances` ADD `domain` int;");
            return new Message(true, MessageType::Success, "Form instances table updated.");
        }
    }
}