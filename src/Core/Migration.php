<?php
namespace HoltBosse\Alba\Core;

//use

class Migration {
    protected ?Message $status = null; //internal cache for if the migration needs to be run or not

    public function isNeeded(): Message {
        if($this->status == null) {
            $this->status = new Message(false, MessageType::Success, "No migration needed");
        }

        return $this->status;
    }

    public function run(): Message {
        if($this->isNeeded()->success) {
            return new Message(true, MessageType::Info, "No migration needed");
        } else {
            return new Message(false, MessageType::Danger, "Migration not implemented");
        }
    }
}
