<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Access {
    public static function can_access($page_groups=[], $user_groups=[]) {
        //prep and parse the inputs
        if (!$user_groups) {
            $user_groups = CMS::Instance()->user->groups;
        }
        if (!$page_groups && defined("ADMINPATH")) {
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