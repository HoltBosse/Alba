<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class PageOptionsMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `pages` LIKE 'page_options'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Pages table missing page_options column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Pages table has page_options column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Pages table OK.");
        } else {
            DB::exec("ALTER TABLE `pages` ADD `page_options` text NOT NULL COMMENT 'seo and og settings';");
            return new Message(true, MessageType::Success, "Added missing columns");
        }
    }
}