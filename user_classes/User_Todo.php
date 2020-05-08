<?php
defined('CMSPATH') or die; // prevent unauthorized access

// user space class for Project stuff

class User_Todo {
	public function hello() {
		echo "<h1>HELLO!</h1>";
	}

	public static function render_todo($todo) {
		?>
		<a href='<?php echo Config::$uripath . "/todos/" . $todo->id;?>'>
				<div class='card'>
					
					<div class='card_content'>
						<div class='card_heading'>
							<h1>
								
								<?php echo $todo->title;?>
							</h1>
							<p class='assignee'>Assigned to: <?php echo $todo->username;?></p>
						</div>
					</div>
				</div>
			</a>
		<?php 
	}

	public static function get_todos_for_content_item ($content_id) { 
		$query = "select todos.id, todos.state, todos.content_type, todos.title, todos.alias, todos.ordering, todos.start, todos.end, todos.created_by, todos.updated_by, todos.note, u.username 
		FROM (content as todos, users as u) 
		JOIN content_fields as target ON todos.id = target.content_id AND target.content=? AND target.name='target_content' 
		WHERE todos.state >= 0 and content_type=5 
		AND u.id in (select uf.content from content_fields uf where uf.name='assignee' and uf.content_id in (select content_id from content_fields where name='target_content' and content=?)) 
		ORDER BY todos.start ASC";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($content_id,$content_id));
		$todos = $stmt->fetchAll();
		return ($todos);
	}

}