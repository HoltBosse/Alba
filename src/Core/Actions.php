<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use \DateTime;

class Actions {

    public $id;
    public $userid;
    public $date;
    public $options;

    private static $actionsRegistry = [
        "contentcreate" => "HoltBosse\\Alba\\Actions\\ContentCreate\\ContentCreate",
        "contentdelete" => "HoltBosse\\Alba\\Actions\\ContentDelete\\ContentDelete",
        "contentupdate" => "HoltBosse\\Alba\\Actions\\ContentUpdate\\ContentUpdate",
        "mediacreate" => "HoltBosse\\Alba\\Actions\\MediaCreate\\MediaCreate",
        "mediadelete" => "HoltBosse\\Alba\\Actions\\MediaDelete\\MediaDelete",
        "mediaupdate" => "HoltBosse\\Alba\\Actions\\MediaUpdate\\MediaUpdate",
        "pagecreate" => "HoltBosse\\Alba\\Actions\\PageCreate\\PageCreate",
        "pagedelete" => "HoltBosse\\Alba\\Actions\\PageDelete\\PageDelete",
        "pageupdate" => "HoltBosse\\Alba\\Actions\\PageUpdate\\PageUpdate",
        "tagcreate" => "HoltBosse\\Alba\\Actions\\TagCreate\\TagCreate",
        "tagdelete" => "HoltBosse\\Alba\\Actions\\TagDelete\\TagDelete",
        "tagupdate" => "HoltBosse\\Alba\\Actions\\TagUpdate\\TagUpdate",
        "usercreate" => "HoltBosse\\Alba\\Actions\\UserCreate\\UserCreate",
        "userdelete" => "HoltBosse\\Alba\\Actions\\UserDelete\\UserDelete",
        "userlogin" => "HoltBosse\\Alba\\Actions\\UserLogin\\UserLogin",
        "userupdate" => "HoltBosse\\Alba\\Actions\\UserUpdate\\UserUpdate",
    ];

    function __construct($action) {
		$this->id = $action->id;
        $this->userid = $action->userid;
        $this->date = $action->date;
        $this->options = json_decode($action->json);
    }

    public static function registerAction(string $actionName, string $actionClass): bool {
		if (!isset(self::$actionsRegistry[$actionName])) {
			self::$actionsRegistry[$actionName] = $actionClass;

			return true;
		}

		return false;
	}

    public static function getActionClass(string $type): ?string {
        if (isset(self::$actionsRegistry[$type])) {
            return self::$actionsRegistry[$type];
        }
        return null;
    }

    public static function getActionTypes(): array {
        return array_keys(self::$actionsRegistry);
    }

    public static function add_action($type, $action, $userid=0) {
        if ($userid==0) {$userid=CMS::Instance()->user->id;}
        if (!is_numeric($userid)) {$userid=0;} //triple check - cms can be dumb when a user is timed out

        DB::exec("INSERT INTO user_actions (userid, type, json) VALUES (?, ?, ?)", [$userid, $type, json_encode($action)]);

        return DB::getLastInsertedId();
    }

    public static function add_action_details($actionid, $details) {
        DB::exec("INSERT INTO user_actions_details (action_id, json) VALUES (?, ?)", [$actionid, json_encode($details)]);
    }

    public function render_user() {
        $user = DB::fetch("SELECT * FROM users WHERE id=?", $this->userid);
        if($user->id) {
            $safeUsername = Input::stringHtmlSafe($user->username);
            return "$safeUsername ($user->email)";
        } else {
            return "unknown";
        }
    }

    public function render_time() {
        $time1 = new DateTime($this->date);
        $now = new DateTime(DB::fetch("SELECT NOW() as currentime")->currentime); //get now from sql which honors the server timezone while php does not
        $interval = $time1->diff($now,true);

        if ($interval->y || $interval->m || $interval->d) {
            return $this->date;
        } elseif ($interval->h) {
            if($interval->h > 1) {
                return $interval->h . ' hours ago';
            } else {
                return $interval->h . ' hour ago';
            }
        } elseif ($interval->i) {
            return $interval->i . ' minutes ago';
        } else {
            return "less than 1 minute ago";
        }
    }

    public function render_row($url, $message) {
        $viewmore = DB::fetch("SELECT * FROM user_actions_details WHERE action_id=?", $this->id);

        echo "<tr>";
            echo "<td>" . $this->render_user() . "</td>";
            echo "<td>";
                if($url) {
                    echo "<a href='$url'>";
                }
                    echo $message;
                if($url) {
                    echo "</a>";
                }
            echo "</td>";
            if($viewmore) {
                echo "<td><a href='" . ($_ENV["uri_path"] ?? "") . "/admin/audit/more/$viewmore->id'>View</a></td>";
            } else {
                echo "<td></td>";
            }
            echo "<td>" . $this->render_time() . "</td>";
        echo "</tr>";
    }

    public function display() {
        $this->render_row(false, "unknown action occured");
    }

    public function display_diff($viewmore) {
        return "
            <tr>
                <td>unknown</td>
                <td>unknown</td>
                <td>unknown</td>
            </tr>
        ";
    }
}