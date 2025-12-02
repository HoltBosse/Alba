<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class FormInstancesTableMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'form_instances' LIMIT 1");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Form Instances table missing");
            } else {
                $this->status = new Message(true, MessageType::Success, "Form Instances table exists");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Form Instances table OK.");
        } else {
            DB::exec("DROP TABLE IF EXISTS `form_instances`;");
            DB::exec(
                "CREATE TABLE `form_instances` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `state` tinyint(4) NOT NULL DEFAULT 1,
                    `title` varchar(255) NOT NULL,
                    `alias` varchar(255) NOT NULL,
                    `created_by` int(11) NOT NULL,
                    `updated_by` int(11) NOT NULL,
                    `emails` varchar(255) DEFAULT NULL,
                    `submit_page` varchar(255) DEFAULT NULL,
                    `location` varchar(255) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
            );
            return new Message(true, MessageType::Success, "Form Instances table updated.");
        }
    }
}