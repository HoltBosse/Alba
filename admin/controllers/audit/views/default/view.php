<?php
defined('CMSPATH') or die; // prevent unauthorized access

?>

<table class="table is-striped is-fullwidth">
	<thead>
		<tr>
			<th>User</th>
			<th>Message</th>
			<th>See More</th>
			<th>Time</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($results as $item) {
				$className = "Action_" . $item->type;
				if(file_exists(CMSPATH . "/core/actions/$className.php")) {
					$actionInstance = new $className($item);
					$actionInstance->display();
				}
			}
		?>
	</tbody>
</table>

<br><br>