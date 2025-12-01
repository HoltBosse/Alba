<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;

class GroupBackendMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `groups` LIKE 'backend'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Groups table missing backend column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Groups table has backend column");
            }
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Pages table OK.");
        } else {
            DB::exec("ALTER TABLE `groups` ADD `backend` text;"); //nullable text column, null means current domain aka all domains
            DB::exec("UPDATE `groups` SET `backend` = ? WHERE id=1;", [1]); //enable admin user to be able to access backend by default
            return new Message(true, MessageType::Success, "Groups table updated.");
        }
    }
}