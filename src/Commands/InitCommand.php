<?php
namespace HoltBosse\Alba\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use HoltBosse\DB\DB;
use Symfony\Component\Dotenv\Dotenv;
use PDO;
use Exception;

#[AsCommand(
    name: 'init',
    description: 'Init Alba Project',
    help: 'This command scaffolds a new Alba project in the specified directory.',
)]
class InitCommand extends Command {
    public function __invoke(OutputInterface $output): int {
        $output->writeln("Beginning Alba project initialization...");

        $installPath = readline("Enter project root or [" . getcwd() . "] for default: ");

        if($installPath==="") {
            $installPath = getcwd();
        }

        if(!file_exists($installPath)) {
            $output->writeln("invalid path [$installPath] - exiting!!!");
            return Command::INVALID;
        }

        if(file_exists($installPath . "/../.env")) {
            $envPath = realpath($installPath . "/../.env");

            $output->writeln("found .env: $envPath");
            $output->writeln("loading... ");

            try {
                $dotenv = new Dotenv();
                $dotenv->load($envPath);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
                return Command::FAILURE;
            }

            $output->writeln("Loaded .env");
        } else {
            $env = [
                "dbhost" => 'localhost',
                "dbname" => null,
                "dbuser" => null,
                "dbpass" => null,
                "dbchar" => 'utf8mb4',
                "sitename" => null,
                "uripath" => "",
                "debug" => "false",
                "debugwarnings" => "false",
                "domain" => 'auto',
                "admintemplate" => 'clean',
                "frontendlogin" => "false",
                "dev_banner" => "false",
            ];

            $output->writeln("starting env creation");

            foreach($env as $k => $v) {
                $default = "";
                if($v!==null) {
                    $nicev = $v;

                    if($v==="") {
                        $nicev = "empty";
                    }

                    $default = " [$nicev] for default";
                }

                $input = readline("Enter configuration option '$k'$default: ");

                if($input==="") {
                    $env[$k] = $v;
                //@phpstan-ignore-next-line
                } elseif(str_contains($input ?? "", " ")) {
                    $env[$k] = "\"$input\"";
                } else {
                    $env[$k] = $input;
                }
            }

            $output->writeln("");
            $output->writeln("writing .env");

            $envPath = realpath($installPath . "/..") . "/.env";
            
            $fileData = "";

            foreach($env as $k=>$v) {
                $fileData = $fileData . "$k=$v" . PHP_EOL;
            }

            file_put_contents($envPath, $fileData);

            $output->writeln("attempting to load .env");

            try {
                $dotenv = new Dotenv();
                $dotenv->load($envPath);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
                return Command::FAILURE;
            }

            $output->writeln("completed & loaded env");
        }

        $output->writeln("");

        $output->writeln("checking db connection");

        $dsn = "mysql:host=" . $_ENV["dbhost"] . ";dbname=" . $_ENV["dbname"] . ";charset=" . $_ENV["dbchar"];
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, $_ENV["dbuser"], $_ENV["dbpass"], $options);
        } catch (\PDOException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln("check passsed");

        $output->writeln("");

        $output->writeln("connecting to database");

        DB::createInstance(
            "mysql:host=" . $_ENV["dbhost"] .";dbname=" . $_ENV["dbname"] .";charset=" . $_ENV["dbchar"],
            $_ENV["dbuser"],
            $_ENV["dbpass"]
        );

        $output->writeln("connected");

        $output->writeln("");

        $sqlFiles = glob(__DIR__ . "/sql/mariadb/*.sql");
        foreach($sqlFiles as $file) {
            $tableName = explode(".", basename($file))[0];

            $tableStatus = DB::fetch("SELECT count(*) AS c FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$tableName, $_ENV["dbname"]])->c;

            if($tableStatus) {
                $output->writeln("table $tableName is already installed, skipping");
            } else {
                $output->writeln("installing $tableName");
                DB::exec(file_get_contents($file));
                $output->writeln("installed");
            }
        }

        $output->writeln("table install done");

        echo "\n";

        if(DB::fetch("SELECT count(*) AS c FROM groups")->c == 0) {
            DB::exec("INSERT INTO `groups` (value, display) VALUES ('admin','Administrators')");
            DB::exec("INSERT INTO `groups` (value, display) VALUES ('editor','Contributors')");
            $output->writeln("groups installed");
        } else {
            $output->writeln("groups already installed");
        }

        if(DB::fetch("SELECT count(*) AS c FROM templates")->c == 0) {
            DB::exec("INSERT INTO templates (is_default, title, folder, description) VALUES (1,'basic','basic','A very simple template to get you started.')");
            $output->writeln("templates installed");
        } else {
            $output->writeln("templates already installed");
        }

        if(DB::fetch("SELECT count(*) AS c FROM content_types")->c == 0) {
            DB::exec("INSERT INTO content_types (title, controller_location, description, state) VALUES ('Basic Article','basic_article','A simple HTML content item with a WYSIWYG editor.',1)");
            $output->writeln("content_types installed");
        } else {
            $output->writeln("content_types already installed");
        }

        if(DB::fetch("SELECT count(*) AS c FROM content_views")->c == 0) {
            DB::exec("INSERT INTO content_views (content_type_id, title, location) VALUES (1,'Single Article','single')");
            DB::exec("INSERT INTO content_views (content_type_id, title, location) VALUES (1,'Blog','blog')");
            $output->writeln("content_views installed");
        } else {
            $output->writeln("content_views already installed");
        }

        if(DB::fetch("SELECT count(*) AS c FROM pages")->c == 0) {
            DB::exec(
                "INSERT INTO pages (title, alias, content_type, parent, template, page_options) VALUES (?,?,?,?,?,?)",
                [
                    "My Home Page",
                    "home",
                    -1,
                    -1,
                    0,
                    ""
                ]
            );

            $output->writeln("home page created");
        } else {
            $output->writeln("pages already exist");
        }

        $output->writeln("");

        if(DB::fetch("SELECT count(*) as c FROM users")->c == 0) {
            $user = [
                "username"=>null,
                "email"=>null,
                "password"=>null,
            ];

            $output->writeln("Admin User Configurator");

            foreach($user as $k=>$v) {
                $user[$k] = readline("Enter [$k]: ");
            }

            $hash = password_hash($user["password"], PASSWORD_DEFAULT);
            DB::exec(
                "INSERT INTO users (username, email, password, state) VALUES (?,?,?,1)",
                [
                    $user["username"],
                    $user["email"],
                    $hash,
                ]
            );

            $userId = DB::getLastInsertedId();
            $adminGroupId = DB::fetch("SELECT id FROM `groups` WHERE value='admin'")->id;

            DB::exec(
                "INSERT INTO user_groups (user_id, group_id) VALUES (?,?)",
                [
                    $userId,
                    $adminGroupId,
                ]
            );

            $output->writeln("inserted user");
        } else {
            $output->writeln("user already added to database");
        }

        $output->writeln("");

        if(!file_exists($installPath . "/index.php")) {
            file_put_contents($installPath . "/index.php", file_get_contents(__DIR__ . "/datafiles/index.php"));

            $output->writeln("created index.php");
            $output->writeln("");
        }

        if(!file_exists($installPath . "/images")) {
            mkdir($installPath . "/images");
            mkdir($installPath . "/images/processed");

            $output->writeln("created images directory");
            $output->writeln("");
        }

        if(!file_exists($installPath . "/.htaccess")) {
            file_put_contents($installPath . "/.htaccess", file_get_contents(__DIR__ . "/datafiles/.htaccess"));

            $output->writeln("created .htaccess");
            $output->writeln("");
        }

        if(!file_exists($installPath . "/src/controllers")) {
            mkdir($installPath . "/src/controllers");

            $output->writeln("created controllers directory in src");
            $output->writeln("");
        }

        if(!file_exists($installPath . "/src/corecontrollers")) {
            mkdir($installPath . "/src/corecontrollers");

            $output->writeln("created corecontrollers directory in src");
            $output->writeln("");
        }

        if(!file_exists($installPath . "/src/Plugins")) {
            mkdir($installPath . "/src/Plugins");

            $output->writeln("created plugins directory in src");
            $output->writeln("");
        }

        if(!file_exists($installPath . "/src/templates")) {
            mkdir($installPath . "/src/templates");

            $output->writeln("created templates directory in src");
            $output->writeln("");
        }

        if(!file_exists($installPath . "/src/Widgets")) {
            mkdir($installPath . "/src/Widgets");

            $output->writeln("created Widgets directory in src");
            $output->writeln("");
        }

        $output->writeln("installer completed");

        return Command::SUCCESS;
    }
}