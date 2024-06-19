<?php
defined('CMSPATH') or die; // prevent unauthorized access

?>

<h1 class="title is-1">Audit Log</h1>

<br>

<table class="table is-striped is-fullwidth">
	<thead>
		<tr>
			<th>User</th>
			<th>Item</th>
			<th>Log</th>
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

<?php Element::create_pagination($item_count, $pagination_size, $cur_page); ?>