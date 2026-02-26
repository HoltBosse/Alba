<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Actions, Component, File};
Use HoltBosse\Form\Form;
Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Components\Pagination\Pagination;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;

echo "<style>";
    echo File::getContents(__DIR__ . "/style.css");
echo "</style>";

(new TitleHeader())->loadFromConfig((object)[
	"header"=>"Audit Log",
])->display();
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
					if($actionInstance instanceof Actions) {
						$actionInstance->display();
					}
				}
			}
		?>
	</tbody>
</table>

<br><br>

<?php
	(new Pagination())->loadFromConfig((object)[
		"id"=>"pagination_component",
		"itemCount"=>$item_count,
		"itemsPerPage"=>$pagination_size,
		"currentPage"=>$cur_page
	])->display();
?>