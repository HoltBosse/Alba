<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Actions, Component};
Use HoltBosse\Form\Form;
Use HoltBosse\DB\DB;

echo "<style>";
    echo file_get_contents(__DIR__ . "/style.css");
echo "</style>";

Component::addon_page_title("Audit Log");
?>

<br>

<form>
	<?php $search_form->display(); ?>
</form>

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
				$className = Actions::getActionClass($item->type);
				if($className) {
					$actionInstance = new $className($item);
					$actionInstance->display();
				}
			}
		?>
	</tbody>
</table>

<br><br>

<?php Component::create_pagination($item_count, $pagination_size, $cur_page); ?>