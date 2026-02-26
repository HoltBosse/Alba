<?php
namespace HoltBosse\Alba\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use HoltBosse\Alba\Migrations\{RunAllMigration};
Use Symfony\Component\Dotenv\Dotenv;
Use \Exception;
Use \ErrorException;
Use HoltBosse\DB\DB;
use Respect\Validation\Validator as v;
use HoltBosse\Alba\Core\File;

//we try catch this, since the cms debug code needs env vars to run

#[AsCommand(
    name: 'create',
    description: 'create basic widgets/controllers',
    help: 'This command creates basic widgets and controllers for Alba CMS via prompt.',
)]
class CreateCommand extends Command {
    protected function configure(): void {
        $this->addArgument('type', InputArgument::REQUIRED, 'What to create (e.g. widget/controller name)');
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int {
        $type = $input->getArgument('type');

        if (!in_array($type, ['controller', 'widget'])) {
            $output->writeln("<error>Invalid type '{$type}'. Must be 'controller' or 'widget'.</error>");
            return Command::FAILURE;
        }

        //in current dir thats this is being run from, check if there is a composer.json file
        if (!file_exists('composer.json')) {
            $output->writeln("<error>No composer.json found in current directory. Please run this command from the root of your project.</error>");
            return Command::FAILURE;
        }

        //read composer.json and get the psr-4 namespace for the src directory
        $composerJson = json_decode(File::getContents('composer.json'), true);
        $psr4 = $composerJson['autoload']['psr-4'] ?? null;

        if (!$psr4) {
            $output->writeln("<error>No PSR-4 autoloading found in composer.json. Please add a PSR-4 autoloading entry for your src directory.</error>");
            return Command::FAILURE;
        }

        $rootNamespace = array_keys($psr4)[0];

        $output->writeln("PSR-4 autoloading found: " . $rootNamespace);

        switch ($type) {
            case 'controller':
                $output->writeln("Creating controller...");

                $controllerName = trim((string) readline("Please enter your controller name: "));

                if(!file_exists('src/Controllers')) {
                    mkdir('src/Controllers', 0755, true);
                }

                if (file_exists("src/controllers/{$controllerName}/controller.php")) {
                    $output->writeln("<error>controller '{$controllerName}' already exists.</error>");
                    return Command::FAILURE;
                }

                $controllerTitle = trim((string) readline("Please enter a title for this controller (e.g. 'Blog Posts'): "));
                $controllerDescription = trim((string) readline("Please enter a description for this controller (e.g. 'Controller for managing blog posts'): "));

                mkdir("src/Controllers/{$controllerName}", 0755, true);
                file_put_contents("src/Controllers/{$controllerName}/controller.php", "");
                mkdir("src/Controllers/{$controllerName}/views", 0755, true);

                $customFields = (object) [
                    "title" => $controllerTitle,
                    "id" => $controllerName,
                    "fields" => []
                ];

                file_put_contents("src/Controllers/{$controllerName}/custom_fields.json", json_encode($customFields, JSON_PRETTY_PRINT));

                $config = (object) [
                    "title" => $controllerTitle,
                    "description" => $controllerDescription,
                ];

                file_put_contents("src/Controllers/{$controllerName}/controller_config.json", json_encode($config, JSON_PRETTY_PRINT));

                break;
            case 'widget':
                $output->writeln("Creating widget...");
                $widgetName = trim((string) readline("Please enter your widget name: "));

                //check that src/Widgets exists, if not create it
                if (!file_exists('src/Widgets')) {
                    mkdir('src/Widgets', 0755, true);
                }

                //check that it starts with a capital letter and only contains letters and numbers
                if (!v::alnum()->noWhitespace()->validate($widgetName) || !ctype_upper($widgetName[0])) {
                    $output->writeln("<error>Invalid widget name. Must start with a capital letter and only contain letters and numbers.</error>");
                    return Command::FAILURE;
                }

                //check that src/Widgets/{$widgetName}/{$widgetName}.php does not already exist
                if (file_exists("src/Widgets/{$widgetName}/{$widgetName}.php")) {
                    $output->writeln("<error>Widget '{$widgetName}' already exists.</error>");
                    return Command::FAILURE;
                }

                //create src/Widgets/{$widgetName} folder
                mkdir("src/Widgets/{$widgetName}", 0755, true);

                //create src/Widgets/{$widgetName}/{$widgetName}.php file with basic widget code
                $widgetCode = "<?php
namespace {$rootNamespace}Widgets\\{$widgetName};

use HoltBosse\Alba\Core\Widget;
class {$widgetName} extends Widget {
    public function render(): string {
        return \"<div class='widget {$widgetName}'>This is the {$widgetName} widget.</div>\";
    }
}
";
                file_put_contents("src/Widgets/{$widgetName}/{$widgetName}.php", $widgetCode);

                $widgetConfig = (object) [
                    "title" => $widgetName,
                    "location" => $widgetName,
                    "id" => "widget_" . strtolower($widgetName) . "_options",
                    "fields" => []
                ];

                //write the widget config to src/Widgets/{$widgetName}/widget_config.json
                file_put_contents("src/Widgets/{$widgetName}/widget_config.json", json_encode($widgetConfig, JSON_PRETTY_PRINT));

                break;
        }

        $output->writeln("<info>{$type} created successfully.</info>");

        return Command::SUCCESS;
    }
}