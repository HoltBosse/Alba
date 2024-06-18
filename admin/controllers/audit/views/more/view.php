<?php
defined('CMSPATH') or die; // prevent unauthorized access

?>

<h1 class="title is-1">TODO: WHAT IM EDITING</h1>

<br>

<table class="table is-striped is-fullwidth">
	<thead>
		<tr>
			<th>Field</th>
			<th>Before</th>
			<th>After</th>
		</tr>
	</thead>
	<tbody>
		<?php
            echo $actionInstance->display_diff($viewmore);
		?>
	</tbody>
</table>

<br><br>