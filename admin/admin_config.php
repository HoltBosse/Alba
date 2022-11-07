<?php

defined('CMSPATH') or die; // prevent unauthorized access 

class Admin_Config {

    static $access = [
        "content" => [1, 2],
        "tags" => [1, 2],
        "categories" => [1, 2],
        "media" => [1, 2]
    ];

}