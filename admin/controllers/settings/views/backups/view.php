<?php
defined('CMSPATH') or die; // prevent unauthorized access

Component::addon_page_title("Backups");
?>

<?php if (class_exists("ZipArchive",false)):?>
	<form method="POST">
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
				<td><a href='<?php echo Config::uripath();?>/backups/<?php echo $backup_file;?>'><?php echo $backup_file;?></a></td>
				<td><?php echo human_filesize(filesize(CMSPATH . "/backups/" . $backup_file));?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>