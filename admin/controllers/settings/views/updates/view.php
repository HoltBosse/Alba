<?php
defined('CMSPATH') or die; // prevent unauthorized access

function show_message ($heading, $text, $class) {
	echo "<article class=\"message $class\">
	<div class=\"message-header\">
		<p>$heading</p>
		<button class=\"delete\" aria-label=\"delete\"></button>
	</div>
	<div class=\"message-body\">
		$text
	</div>
</article>";
}

?>
<h1 class='title is-1'>System Version and Updates</h1>
<h4 class='title is-4'>Current version: <?php echo CMS::Instance()->version;?></h4>
<?php if ($latest->version == CMS::Instance()->version):?>
	<h5 class='title is-5'>You are on the latest version!</h4>
<?php else:?>
	<?php if ($latest->version!==null):?>
		<?php if ($latest->version < CMS::Instance()->version):?>
			<h4 class='title is-4'>YOU ARE FROM THE FUTURE HOW IS THIS POSSIBLE - Latest version: <?php echo $latest->version;?></h4>
		<?php else:?>
			<h4 class='title is-4'>Latest version: <?php echo $latest->version;?></h4>
			<?php if (class_exists("ZipArchive",false)):?>
				<form method="POST">
					<input type='hidden' value='ohyesplease' name='update_please'/>
					<button class='btn button is-success' type='submit'>Perform Update</button>
				</form>
			<?php else: ?>
				<p><em>Native ZIP archive handling not available on this server. Updates must be performed manually.</em></p>
			<?php endif; ?>
		<?php endif;?>
	<?php else:?>
		<h4 class='title is-4'>Unable to determine latest version</h4>
	<?php endif; ?>
<?php endif; ?>

<hr>
<h5 class='is-5 title is-title'>Legacy DB Checks/Fixes</h5>

<?php 
if ($page_options_ok) {
	show_message ('Pages Table','Pages table OK.','is-success');
}
else {
	show_message ('Pages Table','Missing pages page_options column - FIXED.','is-warning');
}
