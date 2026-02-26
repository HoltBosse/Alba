<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

//we explicitly do not use cms content registered types since this can be run outside of cms context
class ContentDomainMigration extends Migration {
    // @phpstan-ignore missingType.iterableValue
    private function getContentTables(): array {
        $tables = DB::fetchAll("SELECT * FROM information_schema.tables WHERE table_name REGEXP '^controller_.*' AND TABLE_SCHEMA = ?", [$_ENV["dbname"]]);
        $tableNames = [];
        foreach($tables as $table) {
            $tableNames[] = $table->TABLE_NAME;
        }
        return $tableNames;
    }
    
    public function isNeeded(): Message {
        if($this->status == null) {
            $controllerTables = $this->getContentTables();
            
            $hasDomainColumn = 0;
            foreach($controllerTables as $table) {
                $columnResult = DB::fetchAll("SHOW COLUMNS FROM `$table` LIKE 'domain'");
                if($columnResult) {
                    $hasDomainColumn++;
                }
            }

            if($hasDomainColumn == sizeof($controllerTables)) {
                $this->status = new Message(true, MessageType::Success, "Content tables have domain column");
            } else {
                $this->status = new Message(false, MessageType::Warning, "Content tables missing domain column");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Content tables OK.");
        } else {
            $controllerTables = $this->getContentTables();
            foreach($controllerTables as $table) {
                DB::exec("ALTER TABLE `$table` ADD `domain` int;"); //nullable int column, null means current domain aka all domains
            }
            
            return new Message(true, MessageType::Success, "Widgets table updated.");
        }
    }
}