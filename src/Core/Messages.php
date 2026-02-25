<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;

class Messages {
	protected string $msgClass = 'alert';
	protected string $msgWrapper = "<div class='%s notification is-%s' role='alert'><button type='button' onclick='this.parentNode.remove()' class='close delete' data-dismiss='alert' aria-label='Close'></button>\n%s</div>\n";
	protected string $msgBefore = '<p>';
	protected string $msgAfter = "</p>\n";

	public static function add(MessageType $type, string $message, ?string $redirect_to=null): bool {
		// Create the session array if it doesnt already exist
		if(!array_key_exists('flash_messages', $_SESSION)) {
			$_SESSION['flash_messages'] = [];
		}
		
		$_SESSION['flash_messages'][$type->value][] = $message;

		if( !is_null($redirect_to) ) {
			header("Location: $redirect_to");
			exit();
		}
		
		return true;
	}

	public static function save_message(mixed $users, string $message, MessageType $type=MessageType::Info): bool {
        $users = is_array($users) ? $users : [$users];
        $users = array_filter($users, function($user) {
            return is_numeric($user);
        });

        $users = array_map(function($user) {
            return (int)$user;
        }, $users);

        $users = array_unique($users);

        $users = array_filter($users, function($user) {
            return $user > 0;
        });

        $users = array_values($users);

        if(count($users) == 0) {
            return false;
        }

        $params = [];
        $sql = "INSERT INTO `messages` (`userid`, `message`, `type`) VALUES ";
        $sql .= implode(",", array_map(function($user) use ($message, $type, &$params) {
            $params = array_merge($params, [$user, $message, $type->value]);
            return "(?, ?, ?)";
        }, $users));

        DB::exec($sql, $params);

        return true;
    }

	public function display(): bool {
		$messages = '';
		$data = '';
		
		if( !isset($_SESSION['flash_messages']) ) return false;

		foreach( $_SESSION['flash_messages'] as $type => $msgArray ) {
			$messages = '';
			foreach( $msgArray as $msg ) {
				$messages .= $this->msgBefore . $msg . $this->msgAfter;	
			}
			$data .= sprintf($this->msgWrapper, $this->msgClass, $type, $messages);
		}

		//get server messages - check that the table exists, else the install page will not be able to load
		if(DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'messages' AND TABLE_SCHEMA = ? LIMIT 1", [$_ENV["dbname"]])) {
			$messages = DB::fetchAll("SELECT * FROM messages WHERE userid=? AND state=1", CMS::Instance()->user->id);
			if(sizeof($messages) > 0) {
				foreach($messages as $item) {
					$data .= sprintf($this->msgWrapper, $this->msgClass, $item->type, $this->msgBefore . $item->message . $this->msgAfter);
				}
				//state is set to 0 as that is read, while -1 is deleted
				$messageIds = implode(",", array_column($messages, "id"));
				DB::exec("UPDATE messages set state=0 WHERE id IN ($messageIds)");
			}
		}
		
		// Clear ALL of the messages
		$this->clear();

		echo $data;

		return true;
	}

	public function clear(?MessageType $type=null): bool { 
		if(isset($type)) {
			unset($_SESSION['flash_messages'][$type->value]); 
		} else {
			unset($_SESSION['flash_messages']);
		}
		return true;
	}
}