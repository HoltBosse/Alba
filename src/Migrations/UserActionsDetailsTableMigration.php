<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class UserActionsDetailsTableMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'user_actions_details' AND TABLE_SCHEMA = ? LIMIT 1", [$_ENV["dbname"]]);
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "User Actions Details table missing");
            } else {
                $this->status = new Message(true, MessageType::Success, "User Actions Details table exists");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "User Actions Details table OK.");
        } else {
            DB::exec("DROP TABLE IF EXISTS `user_actions_details`;");
            DB::exec("
            CREATE TABLE `user_actions_details` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `action_id` int(11) NOT NULL,
                `json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`json`)),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");
            return new Message(true, MessageType::Success, "User Actions Details table updated.");
        }
    }
}