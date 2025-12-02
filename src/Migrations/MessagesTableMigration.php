<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class MessagesTableMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'messages' LIMIT 1");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Messages table missing");
            } else {
                $this->status = new Message(true, MessageType::Success, "Messages table exists");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Messages table OK.");
        } else {
            DB::exec("DROP TABLE IF EXISTS `messages`;");
            DB::exec("
            CREATE TABLE `messages` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `state` int(11) NOT NULL DEFAULT 1,
                `userid` int(11) NOT NULL,
                `message` mediumtext NOT NULL,
                `type` mediumtext NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `userid` (`userid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");
            return new Message(true, MessageType::Success, "Messages table updated.");
        }
    }
}