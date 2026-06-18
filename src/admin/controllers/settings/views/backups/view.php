<?php

Use HoltBosse\Alba\Core\{CMS, Component};
Use HoltBosse\Form\{Input, Form};
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;

(new TitleHeader())->loadFromConfig((object)[
	"header"=>"Backups",
])->display();
?>

<?php if (class_exists("ZipArchive",false)):?>
	<?php
		$_csrf = new Form((object)['id' => 'admin_backups', 'fields' => []]);
		$_csrfToken = Input::stringHtmlSafe($_csrf->getCsrfToken());
		$_csrfFieldName = $_csrf->getCsrfFieldName();
	?>
	<form method="POST">
		<input type='hidden' name='<?= $_csrfFieldName ?>' value='<?= $_csrfToken ?>'>
		<input type='hidden' value='ohyesplease' name='backup_please'/>
		<button class='btn button is-success' type='submit'>Perform Backup</button>
	</form>
<?php else: ?>
	<p><em>Native ZIP archive handling not available on this server. Backups must be performed manually.</em></p>
<?php endif; ?>
	

<hr>
<h5 class='is-5 title is-title'>Previous Backups</h5>

<?php if (!$backup_files):?>
	<p>No backups</h5>
<?php else:?>

<table class='table'>
	<thead>
		<tr>
			<th>Name</th><th>Size</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($backup_files as $backup_file):?>
			<tr>
				<td><a href='<?php echo $_ENV["uripath"];?>/backups/<?php echo Input::stringHtmlSafe($backup_file);?>'><?php echo Input::stringHtmlSafe($backup_file);?></a></td>
				<td><?php echo human_filesize((int) filesize($_ENV["backup_directory"] . "/backups/" . $backup_file));?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>