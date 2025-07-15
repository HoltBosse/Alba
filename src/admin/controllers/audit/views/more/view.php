<?php


?>

<h1 class="title is-1">Edited Fields</h1>

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