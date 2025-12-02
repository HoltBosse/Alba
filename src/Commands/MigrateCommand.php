<?php
namespace HoltBosse\Alba\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use HoltBosse\Alba\Migrations\{RunAllMigration};
Use Symfony\Component\Dotenv\Dotenv;
Use \Exception;
Use \ErrorException;
Use HoltBosse\DB\DB;

//we try catch this, since the cms debug code needs env vars to run

#[AsCommand(
    name: 'migrate',
    description: 'Run Migrations for Alba CMS.',
    help: 'This command runs all the necessary database migrations to keep your Alba CMS installation up to date.',
)]
class MigrateCommand extends Command {
    public function __invoke(OutputInterface $output): int {
        $output->writeln("Loading environment variables...");

        try {
            $dotenv = new Dotenv();
            $dotenv->load(getcwd().'/../.env');
        } catch (Exception $e) {
            $output->writeln(" - Could not load .env file, please ensure you are in an Alba project folder.");
            return Command::FAILURE;
        }

        $output->writeln("Connecting to database...");

        DB::createInstance(
            "mysql:host=" . $_ENV["dbhost"] .";dbname=" . $_ENV["dbname"] .";charset=" . $_ENV["dbchar"],
            $_ENV["dbuser"],
            $_ENV["dbpass"]
        );

        $output->writeln("Beginning running Alba migrations...");

        foreach(RunAllMigration::getAllMigrations() as $migrationDetails) {
            $migrationInstance = $migrationDetails[1];

            if($migrationInstance->isTerminalSafe()===false) {
                $output->writeln("<error> - " . $migrationDetails[0] . ": Skipped (not terminal safe) - visit admin to run this migration.</error>");
                continue;
            } else {
                $migrationStatus = $migrationInstance->run($output);
                $output->writeln("<info> - " . $migrationDetails[0] . ": " . $migrationStatus->message . "</info>");
            }
        }

        return Command::SUCCESS;
    }
}