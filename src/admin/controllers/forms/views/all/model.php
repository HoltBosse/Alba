<?php

Use HoltBosse\Alba\Core\{CMS, Configuration};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;

$pageSize = Configuration::get_configuration_value ('general_options', 'pagination_size'); 

$forms = DB::fetchall("SELECT * FROM form_instances WHERE state>=0");
$formsCount = DB::fetch("SELECT COUNT(*) AS c FROM form_instances WHERE state>=0")->c;

