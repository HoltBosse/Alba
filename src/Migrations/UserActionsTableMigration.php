<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;

class UserActionsTableMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'user_actions' LIMIT 1");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "User Actions table missing");
            } else {
                $this->status = new Message(true, MessageType::Success, "User Actions table exists");
            }
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "User Actions table OK.");
        } else {
            DB::exec("DROP TABLE IF EXISTS `user_actions`;");
            DB::exec("
            CREATE TABLE `user_actions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `userid` int(11) NOT NULL,
                `date` timestamp NOT NULL DEFAULT current_timestamp(),
                `type` varchar(255) NOT NULL,
                `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");
            return new Message(true, MessageType::Success, "User Actions table updated.");
        }
    }
}