<?php
defined('CMSPATH') or die; // prevent unauthorized access

// user space class for Project stuff

class User_Project {
	public function hello() {
		echo "<h1>HELLO!</h1>";
	}

	public static function render_project_list_project ($project) {
		?>
			<a href='<?php echo Config::$uripath . "/projects/" . $project->id;?>'>
				<div class='project_wrap <?php if ($project->f_description==true) {echo "wider";}?>'>
					<?php if (property_exists($project,'client_title')):?>
						<h2 class='project-title'><?php echo $project->client_title . " - " . $project->title; ?></h2>
					<?php else: ?>
						<h2 class='project-title'><?php echo $project->title; ?></h2>
					<?php endif; ?>
					<div class='description_wrap'>
						<?php if (property_exists($project,'f_description')):?>
							<p><?php echo $project->f_description;?></p>
						<?php endif; ?>
					</div>
				</div>
			</a>
		<?php
	}

	public function get_all_projects() {
		$query = "select projects.id, projects.state, projects.content_type, projects.title, projects.alias, projects.ordering, projects.start, projects.end, projects.created_by, projects.updated_by, projects.note, 
		clients.title as client_title 
		from content projects, content clients, content_fields f
		where clients.state >= 0 and f.content=clients.id and projects.id=f.content_id and f.name='client' 
		and projects.state >= 0 
		and projects.content_type=? 
		ORDER BY client_title, projects.title ASC";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array(3));
		$projects = $stmt->fetchAll();
		return ($projects);
	}

	public static function get_status_title($id, $statuses) {
		foreach ($statuses as $status) {
			if ($status->id==$id) {
				return $status->title;
			}
		}
		return ('Unknown Status');
	}

	public static function get_project_statuses() {
		$query = "select c.title as title, c.id as id 
		from content c 
		where c.content_type=7 
		and state>0 
		";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array());
		$statuses = $stmt->fetchAll();
		return ($statuses);
	}

	public function get_project_by_id ($id) {
		$query = "select projects.id, projects.state, projects.content_type, projects.title, projects.alias, projects.ordering, projects.start, projects.end, projects.created_by, projects.updated_by, projects.note, 
		clients.title as client_title, clients.id as client_id 
		from content projects, content clients, content_fields f
		where clients.state >= 0 and f.content=clients.id and projects.id=f.content_id and f.name='client' 
		and projects.state >= 0 
		and projects.id = ?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($id));
		$project = $stmt->fetch();
		return ($project);
	}



	public static function get_projects_by_client($client_id) {
		// only published and not 'complete'
		$query = "select projects.id, projects.state, projects.content_type, 
		projects.title, projects.alias, projects.ordering, projects.start, projects.end, 
		projects.created_by, projects.updated_by, projects.note, num.content as number, status.content as status 
		from content projects, content_fields f, content_fields num, content_fields status 
		where f.content=? and projects.id=f.content_id and f.name='client' 
		and num.content_id=projects.id and num.name='number' 
		and status.content_id=projects.id and status.name='status' and status.content!=20  
		and projects.state >= 0 
		and projects.content_type=3 
		ORDER BY projects.title ASC";
		//CMS::pprint_r ($query);
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($client_id));
		$projects = $stmt->fetchAll();
		return ($projects);
	}

	public static function get_projects_by_client_with_counts ($client_id) {
		
		$query = "select projects.id, projects.state, projects.content_type, 
		projects.title, projects.alias, projects.ordering, projects.start, projects.end, 
		projects.created_by, projects.updated_by, projects.note, f_num.content as number,
		f_description.content as f_description, 
		(select count(*) from content where content_type=4 and id in (select content_id from content_fields where name='project' and content=projects.id)) as task_count, 
		(select count(*) from content where content_type=5 and id in (select content_id from content_fields where name='target_content' and content=projects.id)) as project_todo_count,
		(select count(*) from content where content_type=5 and id in (select content_id from content_fields where name='target_content' and content in (select content_id from content_fields where name='project' and content=projects.id))) as task_todo_count 
		from content AS projects
		JOIN content_fields f_client ON projects.id=f_client.content_id 
		JOIN content_fields f_num ON projects.id=f_num.content_id
		LEFT JOIN content_fields f_description ON f_description.content_id=projects.id and f_description.name='description' 
		where f_client.content=? and f_client.name='client' 
        and f_num.name='number' 
		and projects.state >= 0 
		and projects.content_type=3 
		ORDER BY projects.title ASC";
		//CMS::pprint_r ($query);
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($client_id));
		$projects = $stmt->fetchAll();
		return ($projects);
	}

	public static function get_project_tree_depth($task_tree, $depth=0) {
		$maxdepth = $depth;
		foreach ($task_tree as $task) {
			if ($task->children) {
				$depth = User_Project::get_project_tree_depth($task->children, $depth+1);
			}
			if ($depth>$maxdepth) {
				$maxdepth = $depth;
			}
		}
		return $maxdepth;
	}

	public static function get_project_end_date ($task_tree, $curmax=0) {
		// accepts task tree generated by User_Task class function 
		// which has already created tree of tasks for a given projects
		$end_date = $curmax;
		foreach ($task_tree as $task) {
			$realstart = strtotime($task->realstart);
			$realend = strtotime("+". $task->duration . " day", $realstart);
			//$realstart_txt = date('Y-m-d H:i:s',$realstart);
			//$realend_txt = date('Y-m-d H:i:s',$realend);
			//echo "<p>cmp {$realstart} to {$realend} - {$realstart_txt} to {$realend_txt} [ start + duration ]</p>";
			if ($realend > $end_date) {
				//echo "New biggest: " . date('Y-m-d H:i:s',$realend);
				$end_date = $realend;
			}
			if ($task->children) {
				$max_children_end_date = User_Project::get_project_end_date ($task->children, $end_date);
				if ($max_children_end_date > $end_date) {
					$end_date = $max_children_end_date;
				}
			}
		}
		return  $end_date;
	}

	
}