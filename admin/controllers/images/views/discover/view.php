<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<h1 class='title'>Discover Unprocessed Images</h1>

<?php

//CMS::pprint_r ($all_image_files);

$unprocessed = array();

foreach ($all_image_files as $image_file) {
	if (!image_in_db($image_file)) {
		$unprocessed[] = $image_file;
	}
	// TODO - remove already processed orphans if any exist
}
?>
<?php if ($unprocessed):?>
	<p>Found <?php echo sizeof($unprocessed);?> unprocessed images.</p>
	<p class='help'>Only files with both a title and an alternative text will be processed.</p>
	<p class='help'>By default all files will be processed as 'web-friendly' - maximum width of 1920px. Original sizing can be maintained by selecting No in the Web Friendly column.</p>

	<form method="POST" id='import_ftp_images'>
		<table class='table'>
			<thead>
				<tr>
					<th>Preview</th><th>Filename</th><th>W/H</th><th>Type</th><th>Title</th><th>Alternative Text</th><th>Web Friendly</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($all_image_files as $image_filename):?>
				<?php 
				$file = new File(CMSPATH . '/images/upload/' . $image_filename);
				//CMS::pprint_r ($file);
				?>
				<tr>
					<td><img src='<?php echo Config::$uripath . "/images/upload/" . $image_filename?>' style='max-width:250px;'></td>
					<td style="word-break:break-all;"><?php echo $file->filename; ?></td>
					<td><?php echo $file->width . "/" . $file->height;?></td>
					<td><?php echo $file->mimetype;?></td>
					<td><input type='text'  name='title[]'></td>
					<td><input type='text'  name='alt[]'></td>
					<td>
						<select name='web_friendly[]'>
							<option selected value="1">Yes</option>
							<option value="0">No</option>
						</select>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<button  type='submit' class='button is-primary'>Process</button>
	</form>
<?php else: ?>
	<h2 class='title'>No unprocessed FTP images found.</h2>
<?php endif; ?>