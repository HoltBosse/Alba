<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

function human_filesize($bytes, $decimals = 2) {
	$sz = 'BKMGTP';
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
  }

$segments = CMS::Instance()->uri_segments;

$submitted = Input::getvar('backup_please');

// Perform backup if required

if (!is_dir(CMSPATH . "/backups")) {
	mkdir(CMSPATH . "/backups", 0755);
}
if (!is_dir(CMSPATH . "/temp")) {
	mkdir(CMSPATH . "/temp", 0755);
}
$backup_files = scandir(CMSPATH . "/backups");
$backup_files = array_diff($backup_files, array('.', '..'));
$db_backup_file = CMSPATH . "/temp/" . "db.sql";

if ($submitted) { 
	if (!function_exists('exec')) {
		CMS::Instance()->queue_message('Error creating backup - exec not available','danger',Config::$uripath."/admin");
	}
	if (!`which mysqldump`) {
		CMS::Instance()->queue_message('Error creating backup - mysqldump not available','danger',Config::$uripath."/admin");
	}
	if (!class_exists('ZipArchive',false)) {
		CMS::Instance()->queue_message('Error creating backup - native PHP ZIP not available','danger',Config::$uripath."/admin");
	}

	// DO BACKUP
	$backup_ok = false;
	$db_backup_ok = false;
	
	// DB BACKUP
	$command='mysqldump --opt -h ' .Config::$dbhost .' -u' .Config::$dbuser .' -p' .Config::$dbpass .' ' .Config::$dbname .' > ' . $db_backup_file ;
	exec($command,$output,$db_backup_error);
	/* CMS::pprint_r ($command);
	CMS::pprint_r ($output);
	CMS::pprint_r ($db_backup_ok);
	exit(0); */
	if ($db_backup_error) {
		CMS::Instance()->queue_message('Error creating db backup','danger',Config::$uripath."/admin/settings/backups");
	}

	// create zip
	// Initialize archive object
	$zip = new ZipArchive();
	$date = date('d_m_Y_H_i', time());
	$zip->open(CMSPATH . "/backups/backup_" . $date . ".zip", ZipArchive::CREATE | ZipArchive::OVERWRITE );
	
	function add_folder($zip, $folder) {
		$rootPath = CMSPATH . "/" . $folder;
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
	add_folder($zip, 'admin');
	add_folder($zip, 'controllers');
	add_folder($zip, 'core');
	add_folder($zip, 'images');
	add_folder($zip, 'plugins');	
	add_folder($zip, 'templates');
	add_folder($zip, 'user_classes');
	add_folder($zip, 'widgets');
	$zip->addFile(CMSPATH . "/index.php", "index.php");
	$zip->addFile(CMSPATH . "/.htaccess", ".htaccess");
	$zip->addFile(CMSPATH . "/cmslog.txt", "cmslog.txt");
	$zip->addFile(CMSPATH . "/config.php", "config.php");
	$zip->addFile(CMSPATH . "/temp/db.sql", "db.sql");
	if (file_exists(CMSPATH . "/my_404.php")) {
		$zip->addFile(CMSPATH . "/my_404.php", "my_404.php");
	}
	$zip->close();

	// delete temp db file
	unlink($db_backup_file);

	CMS::Instance()->queue_message('Backup created','success', Config::$uripath."/admin/settings/backups");
}
