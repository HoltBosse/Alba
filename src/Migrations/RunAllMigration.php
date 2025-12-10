<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class RunAllMigration extends Migration {
    public function isNeeded(): Message {
        return new Message(false, MessageType::Warning, "Method not implemented");
    }

    public function run(?OutputInterface $output=null): Message {
        return new Message(false, MessageType::Warning, "Run each migration seperately.");
    }

    public static function getAllMigrations(): array {
        return [
            ['Pages Table - Options Column', new PageOptionsMigration()],
            ['Pages Table - Domain Column', new PageDomainMigration()],
            ['Plugins Table', new PluginTableMigration()],
            ['Tags Table - Parent Column', new TagParentMigration()],
            ['Categories Table', new CategoriesTableMigration()],
            ['Tags Table - Category Column', new TagCategoryMigration()],
            ['Categories Table - Custom Fields Column', new CategoryCustomFieldsMigration()],
            ['Tags Table - Custom Fields Column', new TagCustomFieldsMigration()],
            ['Redirects Table', new RedirectsTableMigration()],
            ['Redirects Table - Domain Column', new RedirectsDomainMigration()],
            ['User Actions Table', new UserActionsTableMigration()],
            ['User Actions Details Table', new UserActionsDetailsTableMigration()],
            ['Form Submissions Table', new FormSubmissionsTableMigration()],
            ['Messages Table', new MessagesTableMigration()],
            ['Form Instances Table', new FormInstancesTableMigration()],
            ['Content Table Ordering', new ContentTableOrderingMigration()],
            ['Pages Table - Domain Column Update', new PageDomainsMigration()],
            ['Redirects Table - Domain Column Update', new RedirectsDomainsMigration()],
            ['Groups Table - Backend Column Update', new GroupBackendMigration()],
            ['Widgets Table - Domain Column Update', new WidgetDomainMigration()],
            ['Groups Table - Domain Column Update', new GroupDomainMigration()],
            ['Domains Table', new DomainsTableMigration()],
            ['Content Table - Domain Column Update', new ContentDomainMigration()],
            ['Tags Table - Domain Column Update', new TagDomainMigration()],
            ['Categories Table - Domain Column Update', new CategoryDomainMigration()],
            ['Form Instances Table - Domain Column', new FormDomainMigration()],
            ['User Table - Domain Column', new UserDomainMigration()],
            ['Media Table - Domain Column', new MediaDomainMigration()],
        ];
    }
}