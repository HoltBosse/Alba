<?php
namespace HoltBosse\Alba\Core;

class Access {
    private static $adminAccessRegistry = [
        "" => [1,2],
        "content" => [1, 2],
        "tags" => [1, 2],
        "categories" => [1, 2],
        "images" => [1, 2]
    ];

    public static function registerAdminAccessRule(string $path, array $groups): bool {
        if(!isset(self::$adminAccessRegistry[$path])) {
            self::$adminAccessRegistry[$path] = $groups;

            return true;
        }

        return false;
    }

    public static function registerAdminAccessRuleOverride(string $path, array $groups) {
        self::$adminAccessRegistry[$path] = $groups;
    }

    public static function getAdminAccessRule(string $path): ?array {
        return self::$adminAccessRegistry[$path];
    }

    public static function can_access($page_groups=[], $user_groups=[]) {
        //prep and parse the inputs
        if (!$user_groups) {
            $user_groups = CMS::Instance()->user->groups;
        }
        if (!$page_groups && CMS::Instance()->isAdmin()) {
            // default to admin in absence of explicit admin config
            // remove sizeof check for php 8.0+
            $page_groups=[1];
        }

        //logic
        if(!$page_groups) {
            return true;
        }

        foreach ($user_groups as $group) {
            if (in_array($group->id, $page_groups)) {
                return true;
            }
        }

        return false;
    }
}