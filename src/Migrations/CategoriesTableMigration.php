<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;

class CategoriesTableMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.tables WHERE table_name = 'categories'LIMIT 1;");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Categories table does not exist");
            } else {
                $this->status = new Message(true, MessageType::Success, "Categories table exists");
            }
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Category table OK.");
        } else {
            DB::exec("CREATE TABLE `categories` (
                `id` int(11) NOT NULL,
                `state` int(11) NOT NULL DEFAULT '1',
                `title` varchar(64) NOT NULL,
                `content_type` int(11) NOT NULL COMMENT '-1 media, -2 user, -3 tag',
                `parent` int(11) NOT NULL DEFAULT '0'
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
            DB::exec("ALTER TABLE `categories` ADD PRIMARY KEY (`id`);");
            DB::exec("ALTER TABLE `categories` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
            return new Message(true, MessageType::Success, "Category table updated.");
        }
    }
}