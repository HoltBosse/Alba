<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;

class PluginTableMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.tables WHERE table_name = 'plugins' LIMIT 1;");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Plugin table missing");
            } else {
                $this->status = new Message(true, MessageType::Success, "plugin table exists");
            }
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Plugins table OK.");
        } else {
            DB::exec("DROP TABLE IF EXISTS `plugins`;");
            DB::exec("CREATE TABLE `plugins` (
            `id` int(11) NOT NULL,
            `state` tinyint(4) NOT NULL DEFAULT '0',
            `title` varchar(255) NOT NULL,
            `location` varchar(255) NOT NULL,
            `options` text COMMENT 'options_json',
            `description` mediumtext
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            DB::exec("ALTER TABLE `plugins` ADD PRIMARY KEY (`id`);");
            DB::exec("ALTER TABLE `plugins` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
            return new Message(true, MessageType::Success, "Plugins table created.");
        }
    }
}