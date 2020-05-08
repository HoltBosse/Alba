<?php
defined('CMSPATH') or die; // prevent unauthorized access

// user space class for Project stuff

class User_Task {
	public function hello() {
		echo "<h1>HELLO!</h1>";
	}

	public static function get_project_for_task($task_id) {
		$query = "select * from content where content_type=3 
		and id=(select content from content_fields where name='project' and content_id=? LIMIT 1)";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($task_id));
		$project = $stmt->fetch();
		return $project;
	}

	public function get_tasks_by_project ($project_id) { 
		// IN: project_id
		$query = "select tasks.id, tasks.state, dep.content as parent_task, dur.content as duration, tasks.content_type, tasks.title, tasks.alias, tasks.ordering, tasks.start, tasks.end, tasks.created_by, tasks.updated_by, tasks.note 
		from content tasks, content_fields f, content_fields dep, content_fields dur 
		where tasks.id=f.content_id and f.name='project' and f.content = ? 
		and dur.content_id = tasks.id and dur.name='duration' 
		and tasks.id=dep.content_id and dep.name='parent_task' 
		and tasks.state >= 0 
		ORDER BY tasks.start ASC";
		$query = "select tasks.id, tasks.state, tasks.content_type, tasks.title, tasks.alias, tasks.ordering, tasks.start, tasks.end, tasks.created_by, tasks.updated_by, tasks.note, 
		dep.content as parent_task, 
		dur.content as duration,
		status.content as status 
		FROM content tasks 
		JOIN content_fields f ON tasks.id=f.content_id and f.name='project' and f.content = ? 
		JOIN content_fields dep ON tasks.id=dep.content_id and dep.name='parent_task' 
		JOIN content_fields dur ON dur.content_id = tasks.id and dur.name='duration' 
		JOIN content_fields status ON status.content_id = tasks.id and status.name='status' 
		where tasks.state >= 0 
		ORDER BY tasks.start ASC";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($project_id));
		$tasks = $stmt->fetchAll();
		return ($tasks);
	}

	function find_task_in_tree ($tree, $task_id) {
		$found_task = null;
		foreach ($tree as $task) {
			if ($task->id == $task_id) {
				return $task;
			}
			else {
				if ($task->children) {
					$found_task = User_Task::find_task_in_tree ($task->children, $task_id);
				}
			}
		}
		return $found_task;
	}

	


	public function make_task_tree ($tasks) {
		// IN: array of tasks from e.g. get_tasks_by_project
		// NOTE
		$tree = array();
		$task_count = sizeof($tasks);
		$processed = 0;
		$max_loops = $task_count*$task_count;
		$loop_count = 0;

		while ($processed < $task_count && $loop_count<$max_loops) {
			
			foreach ($tasks as $task) {
				if (!property_exists($task,'processed')) {
					if (!$task->parent_task) {
						$task->children = [];
						$task->processed = true;
						$task->realstart = $task->start;
						$tree[] = $task;
						$processed++;
					}
					else {
						$parent_task = User_Task::find_task_in_tree ($tree, $task->parent_task);
						if ($parent_task===null) {
							// parent not added yet, find later
						}
						else {
							$task->processed = true;
							$task->children = [];
							$parent_realstart = strtotime($parent_task->realstart);
							$my_realstart = strtotime("+". $parent_task->duration . " day", $parent_realstart);
							$my_realstart_txt = date('Y-m-d H:i:s',$my_realstart);
							$task->realstart = $my_realstart_txt;
							$parent_task->children[] = $task;
							$processed++;
						}
					}
				}
			}

			$loop_count++;

		}

		if ($processed<$task_count) {
			// broken task tree
			CMS::pprint_r ("Warning - project tasks without parent found!");
		}

		return $tree;
	}

}