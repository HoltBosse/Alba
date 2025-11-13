<?php

Use HoltBosse\Alba\Core\{CMS, Content, Component};
Use HoltBosse\DB\DB;

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
show_message('Pages Table - Options Column', $pageOptionsMigrationStatus->message, $pageOptionsMigrationStatus->type->toCssClass());
show_message('Pages Table - Domain Column', $pageDomainMigrationStatus->message, $pageDomainMigrationStatus->type->toCssClass());
show_message('Plugins Table', $pluginsTableMigrationStatus->message, $pluginsTableMigrationStatus->type->toCssClass());
show_message('Tags Table - Parent Column', $tagParentMigrationStatus->message, $tagParentMigrationStatus->type->toCssClass());
show_message('Categories Table', $categoryTableMigrationStatus->message, $categoryTableMigrationStatus->type->toCssClass());
show_message('Tags Table - Category Column', $tagCategoryMigrationStatus->message, $tagCategoryMigrationStatus->type->toCssClass());
show_message('Categories Table - Custom Fields Column', $categoryCustomFieldsMigrationStatus->message, $categoryCustomFieldsMigrationStatus->type->toCssClass());
show_message('Tags Table - Custom Fields Column', $tagCustomFieldsMigrationStatus->message, $tagCustomFieldsMigrationStatus->type->toCssClass());
show_message('Redirects Table', $redirectsCustomFieldsMigrationStatus->message, $redirectsCustomFieldsMigrationStatus->type->toCssClass());
show_message('Redirects Table - Domain Column', $redirectDomainMigrationStatus->message, $redirectDomainMigrationStatus->type->toCssClass());
show_message('User Actions Table', $userActionsTableMigrationStatus->message, $userActionsTableMigrationStatus->type->toCssClass());
show_message('User Actions Details Table', $userActionsDetailsTableMigrationStatus->message, $userActionsDetailsTableMigrationStatus->type->toCssClass());
show_message('Form Submissions Table', $formSubmissionsTableMigrationStatus->message, $formSubmissionsTableMigrationStatus->type->toCssClass());
show_message('Messages Table', $messagesTableMigrationStatus->message, $messagesTableMigrationStatus->type->toCssClass());
show_message('Form Instances Table', $formInstancesMigrationStatus->message, $formInstancesMigrationStatus->type->toCssClass());
show_message('Content Table Ordering', $contentTableOrderingMigrationStatus->message, $contentTableOrderingMigrationStatus->type->toCssClass());
show_message('Pages Table - Domain Column Update', $pagesDomainsMigrationStatus->message, $pagesDomainsMigrationStatus->type->toCssClass());
show_message('Redirects Table - Domain Column Update', $redirectsDomainsMigrationStatus->message, $redirectsDomainsMigrationStatus->type->toCssClass());