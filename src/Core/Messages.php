<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \Exception;

class Messages {
		
	var $msgId;
	var $msgTypes = ['info', 'success', 'danger', 'warning'];
	var $msgClass = 'alert';
	var $msgWrapper = "<div class='%s notification is-%s' role='alert'><button type='button' class='close delete' data-dismiss='alert' aria-label='Close'></button>\n%s</div>\n";
	var $msgBefore = '<p>';
	var $msgAfter = "</p>\n";


	public function __construct() {
		
		// Generate a unique ID for this user and session
		$this->msgId = md5(uniqid());
		
		// Create the session array if it doesnt already exist
		if( !array_key_exists('flash_messages', $_SESSION) ) $_SESSION['flash_messages'] = [];
	

	}
	

	public function add($type, $message, $redirect_to=null) {

		
		if( !isset($type) || !isset($message[0]) ) return false;

		// Replace any shorthand codes with their full version
		if( strlen(trim($type)) == 1 ) {
			$type = str_replace( ['h', 'i', 'w', 'e', 's'], ['help', 'info', 'warning', 'error', 'success'], $type );
		
		} 
		
		// Make sure it's a valid message type
		if( !in_array($type, $this->msgTypes) ) {
			throw new Exception('"' . strip_tags($type) . '" is not a valid message type!' );
		}
		
		// If the session array doesn't exist, create it
		//if( !array_key_exists( $type, $_SESSION['flash_messages'] ) ) $_SESSION['flash_messages'][$type] = [];
		
		$_SESSION['flash_messages'][$type][] = $message;

		if( !is_null($redirect_to) ) {
			header("Location: $redirect_to");
			exit();
		}
		
		return true;
		
	}

	public static function save_message($users, $message, $type="info") {
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
            $params = array_merge($params, [$user, $message, $type]);
            return "(?, ?, ?)";
        }, $users));

        DB::exec($sql, $params);

        return true;
    }
	
	//-----------------------------------------------------------------------------------------------
	// display()
	// print queued messages to the screen
	//-----------------------------------------------------------------------------------------------

	public function display($type='all', $print=true) {
		$messages = '';
		$data = '';
		
		if( !isset($_SESSION['flash_messages']) ) return false;
		
		// Print a certain type of message?
		if( in_array($type, $this->msgTypes) ) {
			foreach( $_SESSION['flash_messages'][$type] as $msg ) {
				$messages .= $this->msgBefore . $msg . $this->msgAfter;
			}

			$data .= sprintf($this->msgWrapper, $this->msgClass, $type, $messages);
			
			// Clear the viewed messages
			$this->clear($type);
		
		// Print ALL queued messages
		} elseif( $type == 'all' ) {
			foreach( $_SESSION['flash_messages'] as $type => $msgArray ) {
				$messages = '';
				foreach( $msgArray as $msg ) {
					$messages .= $this->msgBefore . $msg . $this->msgAfter;	
				}
				$data .= sprintf($this->msgWrapper, $this->msgClass, $type, $messages);
			}

			//get server messages - check that the table exists, else the install page will not be able to load
			if(DB::fetchAll("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'messages' LIMIT 1")) {
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
		
		// Invalid Message Type?
		} else { 
			return false;
		}
		
		// Print everything to the screen or return the data
		if( $print ) { 
			echo $data; 
		} else { 
			return $data; 
		}
	}
	

	public function hasErrors() { 
		return empty($_SESSION['flash_messages']['error']) ? false : true;	
	}
	

	public function hasMessages($type=null) {
		if( !is_null($type) ) {
			if( !empty($_SESSION['flash_messages'][$type]) ) return $_SESSION['flash_messages'][$type];	
		} else {
			foreach( $this->msgTypes as $type ) {
				if( !empty($_SESSION['flash_messages']) ) return true;	
			}
		}
		return false;
	}
	

	public function clear($type='all') { 
		if( $type == 'all' ) {
			unset($_SESSION['flash_messages']); 
		} else {
			unset($_SESSION['flash_messages'][$type]);
		}
		return true;
	}
	
	public function __toString() { return $this->hasMessages();	}

	public function __destruct() {
		//$this->clear();
	}


}