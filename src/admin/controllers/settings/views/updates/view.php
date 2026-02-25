<?php

Use HoltBosse\Alba\Core\{CMS, Content, Component};
Use HoltBosse\DB\DB;
use HoltBosse\Alba\Migrations\{RunAllMigration};
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Alba\Components\Admin\MessageBox\MessageBox;

(new TitleHeader())->loadFromConfig((object)[
	"header"=>"System Version and Updates",
])->display();
?>

<hr>
<h5 class='is-5 title is-title'>Legacy DB Checks/Fixes</h5>

<?php
foreach(RunAllMigration::getAllMigrations() as $migrationDetails) {
	$migrationInstance = $migrationDetails[1];
	$migrationStatus = $migrationInstance->run();

	(new MessageBox())->loadFromConfig((object)[
		"heading" => $migrationDetails[0],
		"text" => $migrationStatus->message,
		"classList" => [$migrationStatus->type->toCssClass()],
	])->display();

}