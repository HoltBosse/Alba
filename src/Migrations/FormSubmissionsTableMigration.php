<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class FormSubmissionsTableMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'form_submissions' LIMIT 1");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Form Submissions table missing");
            } else {
                $this->status = new Message(true, MessageType::Success, "Form Submissions table exists");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Form Submissions table OK.");
        } else {
            DB::exec("DROP TABLE IF EXISTS `form_submissions`;");
            DB::exec("
            CREATE TABLE `form_submissions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `form_id` varchar(255) NOT NULL,
                `form_path` varchar(255) NOT NULL,
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `form_submissions_chk_1` CHECK (json_valid(`data`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");
            return new Message(true, MessageType::Success, "Form Submissions table updated.");
        }
    }
}