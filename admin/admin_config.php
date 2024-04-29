<?php

defined('CMSPATH') or die; // prevent unauthorized access 

class Admin_Config {

    static $access = [
        "" => [1,2],
        "content" => [1, 2],
        "tags" => [1, 2],
        "categories" => [1, 2],
        "images" => [1, 2]
    ];
    static $show_ids_in_tables = false;

}