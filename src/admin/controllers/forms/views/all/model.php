<?php

Use HoltBosse\Alba\Core\{CMS, Configuration};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;

$pageSize = Configuration::get_configuration_value ('general_options', 'pagination_size'); 

$forms = DB::fetchall("SELECT * FROM form_instances WHERE state>=0 AND (domain IS NULL OR domain=?)", [$_SESSION["current_domain"]]);
$formsCount = DB::fetch("SELECT COUNT(*) AS c FROM form_instances WHERE state>=0 AND (domain IS NULL OR domain=?)", [$_SESSION["current_domain"]])->c;
