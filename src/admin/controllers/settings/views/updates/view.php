<?php

Use HoltBosse\Alba\Core\{CMS, Content, Component};
Use HoltBosse\DB\DB;
use HoltBosse\Alba\Migrations\{RunAllMigration};

function show_message (string $heading, string $text, string $class) {
	echo "<article class=\"message $class\">
	<div class=\"message-header\">
		<p>$heading</p>
		<button class=\"delete\" aria-label=\"delete\"></button>
	</div>
	<div class=\"message-body\">
		$text
	</div>
</article>";
}

Component::addon_page_title("System Version and Updates");
?>

<hr>
<h5 class='is-5 title is-title'>Legacy DB Checks/Fixes</h5>

<?php
foreach(RunAllMigration::getAllMigrations() as $migrationDetails) {
	$migrationInstance = $migrationDetails[1];
	$migrationStatus = $migrationInstance->run();
	show_message($migrationDetails[0], $migrationStatus->message, $migrationStatus->type->toCssClass());
}