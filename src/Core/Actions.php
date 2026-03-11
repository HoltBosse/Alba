<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validatable;
Use Respect\Validation\Validator as v;
Use \DateTime;
Use \stdClass;

class Actions {
    public ?string $id = null;
    public ?string $userid = null;
    public ?string $date = null;
    public mixed $options = null;

    // @phpstan-ignore missingType.iterableValue
    private static array $actionsRegistry = [
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

    function __construct(stdClass $action) {
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

    // @phpstan-ignore missingType.iterableValue
    public static function getActionTypes(): array {
        return array_keys(self::$actionsRegistry);
    }

    public static function add_action(string $type, stdClass $action, ?int $userid=0): string {
        if(is_null($userid)) {
            $userid = 0;
        }
        if ($userid==0) {$userid=CMS::Instance()->user->id;}
        if (!is_numeric($userid)) {$userid=0;} //triple check - cms can be dumb when a user is timed out

        DB::exec("INSERT INTO user_actions (userid, type, json) VALUES (?, ?, ?)", [$userid, $type, json_encode($action)]);
        $id = DB::getLastInsertedId();

        if($id===false) {
            throw new \Exception("Failed to insert action into database");
        }

        return $id;
    }

    public static function isApiAccessEnabledForType(string $type): bool {
        $actionClass = self::getActionClass($type);
        if ($actionClass === null || !class_exists($actionClass)) {
            return false;
        }

        return $actionClass::isApiAccessEnabled();
    }

    public static function getActionDataSchemaForType(string $type): Validatable {
        $actionClass = self::getActionClass($type);
        if ($actionClass === null || !class_exists($actionClass)) {
            return v::alwaysInvalid();
        }

        return $actionClass::getActionDataSchema();
    }

    public static function isApiAccessEnabled(): bool {
        return false;
    }

    public static function getActionDataSchema(): Validatable {
        return v::AlwaysInvalid();
    }

    public static function insertCsrfTokenForType(string $type): void {
        $actionClass = self::getActionClass($type);
        if ($actionClass === null || !class_exists($actionClass)) {
            throw new \Exception("Unknown action type: $type");
        }

        $_SESSION["alba_action_csrf_tokens"] = $_SESSION["alba_action_csrf_tokens"] ?? [];
        
        $token = $_SESSION["alba_action_csrf_tokens"][$type] ?? bin2hex(random_bytes(32));
        $_SESSION["alba_action_csrf_tokens"][$type] = $token;

        echo "<script type='module'>window.albaActionCsrf = window.albaActionCsrf || {}; window.albaActionCsrf['$type'] = '$token';</script>";
    }

    public static function validateApiCsrfTokenForType(string $type, ?string $token): bool {
        if (!isset($_SESSION["alba_action_csrf_tokens"][$type])) {
            return false;
        }

        return hash_equals($_SESSION["alba_action_csrf_tokens"][$type], $token ?? '');
    }

    public static function add_action_details(string $actionid, stdClass $details): void {
        DB::exec("INSERT INTO user_actions_details (action_id, json) VALUES (?, ?)", [$actionid, json_encode($details)]);
    }

    public function render_user(): string {
        $user = DB::fetch("SELECT * FROM users WHERE id=?", $this->userid);
        if($user->id) {
            $safeUsername = Input::stringHtmlSafe($user->username);
            return "$safeUsername ($user->email)";
        } else {
            return "unknown";
        }
    }

    public function render_time(): string {
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

    public function render_row(?string $url, string $message): void {
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

    public function display(): void {
        $this->render_row(null, "unknown action occurred");
    }

    public function display_diff(stdClass $viewmore): string {
        return "
            <tr>
                <td>unknown</td>
                <td>unknown</td>
                <td>unknown</td>
            </tr>
        ";
    }
}