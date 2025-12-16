<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class WidgetDomainMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `widgets` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Widgets table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Widgets table has domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Pages table OK.");
        } else {
            DB::exec("ALTER TABLE `widgets` ADD `domain` int;"); //nullable int column, null means current domain aka all domains
            return new Message(true, MessageType::Success, "Widgets table updated.");
        }
    }
}