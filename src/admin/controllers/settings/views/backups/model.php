<?php

Use HoltBosse\Alba\Core\{CMS, Component};
Use HoltBosse\Form\{Form, Input};
Use \Exception;

// any variables created here will be available to the view

function human_filesize(int $bytes, int $decimals = 2): string {
	$sz = 'BKMGTP';
	$factor = (int) floor((strlen((string) $bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

$segments = CMS::Instance()->uri_segments;

$submitted = Input::getvar('backup_please');

// Perform backup if required

if(!isset($_ENV["backup_directory"])) {
	throw new Exception('No backup directory defined!');
}

if (!is_dir($_ENV["backup_directory"] . "/backups")) {
	mkdir($_ENV["backup_directory"] . "/backups", 0755);
}
if (!is_dir($_ENV["backup_directory"] . "/temp")) {
	mkdir($_ENV["backup_directory"] . "/temp", 0755);
}
$backup_files = scandir($_ENV["backup_directory"] . "/backups");
$backup_files = array_diff($backup_files, ['.', '..']);
$db_backup_file = $_ENV["backup_directory"] . "/temp/" . "db.sql";

if ($submitted) { 
	if (!function_exists('exec')) {
		CMS::Instance()->queue_message('Error creating backup - exec not available','danger',$_ENV["uripath"]."/admin");
	}
	if (!shell_exec("which mysqldump")) {
		CMS::Instance()->queue_message('Error creating backup - mysqldump not available','danger',$_ENV["uripath"]."/admin");
	}
	if (!class_exists('ZipArchive',false)) {
		CMS::Instance()->queue_message('Error creating backup - native PHP ZIP not available','danger',$_ENV["uripath"]."/admin");
	}

	// DO BACKUP
	$backup_ok = false;
	$db_backup_ok = false;
	
	// DB BACKUP
	$command='mysqldump --opt -h ' . $_ENV["dbhost"] .' -u' .$_ENV["dbuser"] ." -p'" .$_ENV["dbpass"] ."' " .$_ENV["dbname"] .' > ' . $db_backup_file ;
	exec($command,$output,$db_backup_error);
	/* CMS::pprint_r ($command);
	CMS::pprint_r ($output);
	CMS::pprint_r ($db_backup_ok);
	exit(0); */
	if ($db_backup_error) {
		CMS::Instance()->queue_message('Error creating db backup','danger',$_ENV["uripath"]."/admin/settings/backups");
	}

	// create zip
	// Initialize archive object
	$zip = new ZipArchive();
	$date = date('d_m_Y_H_i', time());
	$zip->open($_ENV["backup_directory"] . "/backups/backup_" . $date . ".zip", ZipArchive::CREATE | ZipArchive::OVERWRITE );
	
	function add_folder(ZipArchive $zip, string $folder): void {
		$rootPath = $_ENV["backup_directory"] . "/" . $folder;
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);
				// Add current file to archive
				$zip->addFile($filePath, $folder . "/" . $relativePath);
			}
		}
	}

	// add folders and files
	add_folder($zip, 'src');
	add_folder($zip, 'images');
	$zip->addFile($_ENV["backup_directory"] . "/index.php", "index.php");
	$zip->addFile($_ENV["backup_directory"] . "/.htaccess", ".htaccess");
	$zip->addFile($_ENV["backup_directory"] . "/cmslog.txt", "cmslog.txt");
	$zip->addFile($_ENV["backup_directory"] . "/config.php", "config.php");
	$zip->addFile($_ENV["backup_directory"] . "/temp/db.sql", "db.sql");
	if(isset($_ENV["custom_404_file_path"])) {
		$zip->addFile($_ENV["custom_404_file_path"], basename($_ENV["custom_404_file_path"]));
	}
	$zip->close();

	// delete temp db file
	unlink($db_backup_file);

	CMS::Instance()->queue_message('Backup created','success', $_ENV["uripath"]."/admin/settings/backups");
}
