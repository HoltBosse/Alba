<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$user = new User();
$all_users = $user->get_all_users();