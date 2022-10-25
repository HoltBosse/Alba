<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>
<h1 class='title is-1'>
	All Content Types
	<a href='<?php echo Config::uripath() . "/admin/content/new_type"?>' class="button is-primary pull-right">
		<span class="icon is-small">
      		<i class="fas fa-check"></i>
    	</span>
		<span>Design New Content Type</span>
	</a>
</h1>
<table class="table">
	<thead>
		<th>Status</th>
		<th>Title</th>
		<th>Description</th>
		<th>Updated</th>
		<th>ID</th>
	</thead>
	<tbody>
		<?php foreach($all_content_types as $content_type):?>
		<tr>
			<td>
				<?php echo $content_type->state; ?>
			</td>
			<td>
				<?php echo $content_type->title; ?>
			</td>
			<td>
				<?php echo $content_type->description; ?>
			</td>
			<td>
				<?php echo $content_type->updated; ?>
			</td>
			<td>
				<?php echo $content_type->id; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>