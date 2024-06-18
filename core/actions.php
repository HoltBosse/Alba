<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Actions {

    public $id;
    public $userid;
    public $date;
    public $options;

    function __construct($action) {
		$this->id = $action->id;
        $this->userid = $action->userid;
        $this->date = $action->date;
        $this->options = json_decode($action->json);
    }

    public static function add_action($type, $action, $userid=0) {
        if ($userid==0) {$userid=CMS::Instance()->user->id;}
        if (!is_numeric($userid)) {$userid=0;} //triple check - cms can be dumb when a user is timed out

        DB::exec("INSERT INTO user_actions (userid, type, json) VALUES (?, ?, ?)", [$userid, $type, json_encode($action)]);

        return DB::get_last_insert_id();
    }

    public static function add_action_details($actionid, $details) {
        DB::exec("INSERT INTO user_actions_details (action_id, json) VALUES (?, ?)", [$actionid, json_encode($details)]);
    }

    public function render_user() {
        $user = DB::fetch("SELECT * FROM users WHERE id=?", $this->userid);
        return "$user->username ($user->email)";
    }

    public function render_time() {
        $time1 = new DateTime($this->date);
        $now = new DateTime();
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

    public function render_row($url, $message, $trashme) {
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
                echo "<td><a href='" . Config::uri_path() . "/admin/audit/more/$viewmore->id'>View</a></td>";
            } else {
                echo "<td></td>";
            }
            echo "<td>" . $this->render_time() . "</td>";
        echo "</tr>";
    }
}