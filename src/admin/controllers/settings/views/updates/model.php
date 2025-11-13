<?php

Use HoltBosse\Alba\Core\{CMS, Content, Component};
Use HoltBosse\DB\DB;
use HoltBosse\Alba\Migrations\{PageOptionsMigration, PageDomainMigration, PluginTableMigration};
use HoltBosse\Alba\Migrations\{TagParentMigration, CategoriesTableMigration, TagCategoryMigration};
use HoltBosse\Alba\Migrations\{CategoryCustomFieldsMigration, TagCustomFieldsMigration, RedirectsTableMigration};
use HoltBosse\Alba\Migrations\{RedirectsDomainMigration, UserActionsTableMigration, UserActionsDetailsTableMigration};
use HoltBosse\Alba\Migrations\{FormSubmissionsTableMigration, MessagesTableMigration, FormInstancesTableMigration};
use HoltBosse\Alba\Migrations\{ContentTableOrderingMigration, PageDomainsMigration, RedirectsDomainsMigration};

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;

// Legacy DB Checks / Fixes

$pageOptionsMigrationStatus = (new PageOptionsMigration())->run();
$pageDomainMigrationStatus = (new PageDomainMigration())->run();
$pluginsTableMigrationStatus = (new PluginTableMigration())->run();
$tagParentMigrationStatus = (new TagParentMigration())->run();
$categoryTableMigrationStatus = (new CategoriesTableMigration())->run();
$tagCategoryMigrationStatus = (new TagCategoryMigration())->run();
$categoryCustomFieldsMigrationStatus = (new CategoryCustomFieldsMigration())->run();
$tagCustomFieldsMigrationStatus = (new TagCustomFieldsMigration())->run();
$redirectsCustomFieldsMigrationStatus = (new RedirectsTableMigration())->run();
$redirectDomainMigrationStatus = (new RedirectsDomainMigration())->run();
$userActionsTableMigrationStatus = (new UserActionsTableMigration())->run();
$userActionsDetailsTableMigrationStatus = (new UserActionsDetailsTableMigration())->run();
$formSubmissionsTableMigrationStatus = (new FormSubmissionsTableMigration())->run();
$messagesTableMigrationStatus = (new MessagesTableMigration())->run();
$formInstancesMigrationStatus = (new FormInstancesTableMigration())->run();
$contentTableOrderingMigrationStatus = (new ContentTableOrderingMigration())->run();
$pagesDomainsMigrationStatus = (new PageDomainsMigration())->run();
$redirectsDomainsMigrationStatus = (new RedirectsDomainsMigration())->run();