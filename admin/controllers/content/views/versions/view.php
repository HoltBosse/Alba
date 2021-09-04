<?php
defined('CMSPATH') or die; // prevent unauthorized access
//CMS::pprint_r ($content);

function preview_field($field) {
	?>
	<tr>
		<td><?php echo $field->label; ?></td>
		<td><?php CMS::pprint_r ( $field->default );?></td>
		<td>TODO</td>
	</tr>
	<?php
}

?>



<?php echo "<script>var content_id=" . $content_id . "</script>"; ?>
<h1 class='title'>Versions of &ldquo;<?php echo $content->title; ?>&rdquo; - <?php echo Content::get_content_type_title($content->content_type);?></h1>


<hr>
<?php CMS::pprint_r ($cur_content_fields); ?>
<hr>

<section id='version_comparisons'>
	<?php foreach ($versions as $version):?>
		<aside class='version_overview'>
			<div id='version_date'><?php echo $version->created;?></div>
			<p class='help' id='version_created_by'><?php echo $version->username; ?></p>
			<div class='version_comparison_wrap'>
				<?php $fields = json_decode($version->fields_json);?>
				
				<table class='table version_comp_table'>
					<thead>
						<tr>
							<th>Field</th>
							<th>Contents</th>
							<th>Compare</th>
						</tr>
					</thead>
					<tbody>
						<?php  foreach ($fields as $field):?>
							<?php preview_field($field); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<br><br>
			<div><a class='btn button cta' href='<?php Config::$uripath;?>/admin/content/versions/restore/<?php echo $version->id;?>'>Restore</a></div>
		</aside>
	<?php endforeach; ?>
</section>

