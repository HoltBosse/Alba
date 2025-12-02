<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class DomainsTableMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'domains' LIMIT 1");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Domains table missing");
            } else {
                $this->status = new Message(true, MessageType::Success, "Domains table exists");
            }
        }

        return $this->status;
    }

    public function run(?OutputInterface $output=null): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Domains table OK.");
        } else {
            DB::exec("DROP TABLE IF EXISTS `domains`;");
            DB::exec("
            CREATE TABLE `domains` (
                `id` int(11) NOT NULL,
                `value` text NOT NULL,
                `display` text NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");

            if(isset($_ENV["domains"])) {
                $domains = explode(",", $_ENV["domains"]);
                foreach($domains as $index=>$domain) {
                    $domain = trim($domain);
                    DB::exec("INSERT INTO `domains` (`id`, `value`, `display`) VALUES (?, ?, ?);", [$index, $domain, $domain]);
                }
            } elseif($output===null) {
                DB::exec("INSERT INTO `domains` (`id`, `value`, `display`) VALUES (?, ?, ?);", [0, $_SERVER["HTTP_HOST"], $_SERVER["HTTP_HOST"]]);
            } else {
                $domain = trim(readline("Please enter the primary domain for this CMS installation (e.g. example.com): "));
                DB::exec("INSERT INTO `domains` (`id`, `value`, `display`) VALUES (?, ?, ?);", [0, $domain, $domain]);
            }

            return new Message(true, MessageType::Success, "Domains table created.");
        }
    }
}