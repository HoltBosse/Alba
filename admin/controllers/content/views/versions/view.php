<?php
defined('CMSPATH') or die; // prevent unauthorized access
//CMS::pprint_r ($content);



?>



<?php echo "<script>var content_id=" . $content_id . "</script>"; ?>
<h1 class='title'>Versions of &ldquo;<?php echo $content->title; ?>&rdquo; - <?php echo Content::get_content_type_title($content->content_type);?></h1>


<hr>
<p>TODO: nice preview version of current content - will require loading form json etc. previews below work by virtue of label being stored in versions db</p>
<hr>

<section id='version_comparisons'>
	<?php foreach ($versions as $version):?>
		<aside class='version_overview'>
			<div id='version_date'><?php echo $version->created;?></div>
			<p class='help' id='version_created_by'><?php echo $version->username; ?></p>
			<div class='version_comparison_wrap'>
				<?php $fields = json_decode($version->fields_json);?>
				
				<table style='width:95%; min-width:60vw; max-width:100%; table-layout:fixed;' class='table version_comp_table'>
					<thead>
						<tr>
							<th>Field</th>
							<th>Version</th>
							<th>Current</th>
						</tr>
					</thead>
					<tbody>
						<?php  foreach ($fields as $field):?>
							<?php 
								//CMS::pprint_r ($field);
								$cur_field = get_field_by_name ($cur_content_fields, $field->name);
								//CMS::pprint_r ($cur_field);
								if ($cur_field) {
									if ($cur_field->value !=$field->value) {
										// not the same - show diff
										preview_field($field, $cur_field); 
									}
								}
								// preview_field($field); 
							?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<br><br>
			<div><a class='btn button cta' href='<?php Config::$uripath;?>/admin/content/versions/restore/<?php echo $version->id;?>'>Restore</a></div>
		</aside>
	<?php endforeach; ?>
</section>

