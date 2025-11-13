<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;

class PageDomainsMigration extends Migration {
    public function isNeeded(): Message {
        if($this->status == null) {
            $result = DB::fetchall("SELECT DISTINCT domain FROM pages WHERE domain NOT REGEXP '^[0-9]+$'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Pages table domain column missing");
            } else {
                $this->status = new Message(true, MessageType::Success, "Pages table domain column exists");
            }
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Pages table OK.");
        } else {
            $page_domains_ok = DB::fetchall("SELECT DISTINCT domain FROM pages WHERE domain NOT REGEXP '^[0-9]+$'");

            if(sizeof($page_domains_ok)>0) {
                foreach($page_domains_ok as $item) {
                    DB::exec(
                        "UPDATE pages SET domain=? WHERE domain=?",
                        [CMS::getDomainIndex($item->domain), $item->domain]
                    );
                }
            }
            return new Message(true, MessageType::Success, "Pages table updated.");
        }
    }
}