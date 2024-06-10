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
<h4 class='title is-4'>Current <?=$channel;?> version: <?php echo CMS::Instance()->version;?></h4>
<?php if ( $latest_version_current_channel == CMS::Instance()->version ):?>
	<h5 class='title is-5'>You are on the latest version!</h4>
<?php else:?>
	<?php if ($latest_version_current_channel!==null):?>
		<?php if ($latest_version_current_channel < CMS::Instance()->version):?>
			<h4 class='title is-4'>YOU ARE FROM THE FUTURE HOW IS THIS POSSIBLE - Latest version: <?php echo $latest->version;?></h4>
		<?php else:?>
			<h4 class='title is-4'>Latest version: <?php echo $latest_version_current_channel;?></h4>
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

if ($plugins_table_ok) {
	show_message ('Plugins Table','Plugins table OK.','is-success');
}
else {
	show_message ('Plugins Table','Plugins table created.','is-warning');
}

if ($tags_table_ok) {
	show_message ('Tags Table Parent Column','Tags table OK.','is-success');
}
else {
	show_message ('Tags Table Parent Column','Tags table updated.','is-warning');
}

if ($content_category_ok) {
	show_message ('Content Table Category Column','Content table OK.','is-success');
}
else {
	show_message ('Tags Table Category Column','Content table updated.','is-warning');
}

if ($tag_category_ok) {
	show_message ('Tags Table Category Column','Tags table OK.','is-success');
}
else {
	show_message ('Tags Table Category Column','Tags table updated.','is-warning');
}

if ($custom_fields_category_ok) {
	show_message ('Category Custom Fields Column','Category table OK.','is-success');
}
else {
	show_message ('Category Custom Fields Column','Category table updated.','is-warning');
}

if ($redirects_table_ok) {
	show_message ('Redirects Table','Redirects table OK.','is-success');
}
else {
	show_message ('Redirects Table','Redirects table created.','is-warning');
}


