<?php
namespace HoltBosse\Alba\Migrations;

use HoltBosse\Alba\Core\{Migration, Message, MessageType, CMS};
use HoltBosse\DB\DB;
use Symfony\Component\Console\Output\OutputInterface;

class RunAllMigration extends Migration {
    public function isNeeded(): Message {
        /* if($this->status == null) {
            $result = DB::fetchAll("show columns FROM `widgets` LIKE 'domain'");
            if(!$result) {
                $this->status = new Message(false, MessageType::Warning, "Widgets table missing domain column");
            } else {
                $this->status = new Message(true, MessageType::Success, "Widgets table has domain column");
            }
        } */

        return new Message(false, MessageType::Warning, "Method not implemented");
    }

    public function run(?OutputInterface $output=null): Message {
        /* if($this->isNeeded()->success) {
            return new Message(true, MessageType::Success, "Pages table OK.");
        } else {
            DB::exec("ALTER TABLE `widgets` ADD `domain` text;"); //nullable text column, null means current domain aka all domains
            return new Message(true, MessageType::Success, "Widgets table updated.");
        } */

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
        ];
    }
}