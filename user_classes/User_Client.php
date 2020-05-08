<?php
defined('CMSPATH') or die; // prevent unauthorized access

// user space class for Project stuff

class User_Client {
	
	public function get_client_by_id ($id) {
		$query = "select clients.id, clients.state, clients.content_type, clients.title, clients.alias, clients.ordering, clients.start, clients.end, clients.created_by, clients.updated_by, clients.note,
		f_init.content as initials, f_desc.content as description 
		from content clients 
		JOIN content_fields AS f_init ON f_init.content_id=clients.id and f_init.name='initials' 
		LEFT JOIN content_fields AS f_desc ON f_desc.content_id=clients.id and f_desc.name='description' 
		WHERE clients.state >= 0 
		and clients.id = ?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($id));
		$client = $stmt->fetch();
		return ($client);
	}

	public function get_client_by_project_id ($project_id) {
		$query = "select clients.id, clients.state, clients.content_type, clients.title, clients.alias, clients.ordering, clients.start, clients.end, clients.created_by, clients.updated_by, clients.note,
		f_init.content as initials, f_desc.content as description 
		from content clients 
		JOIN content_fields AS f_init ON f_init.content_id=clients.id and f_init.name='initials' 
		LEFT JOIN content_fields AS f_desc ON f_desc.content_id=clients.id and f_desc.name='description' 
		WHERE clients.state >= 0 
		and clients.id = (select content from content_fields where content_id=? and name='client' LIMIT 1)";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($project_id));
		$client = $stmt->fetch();
		return ($client);
	}

	public static function get_all_clients() {
	//  just client info + initials
		$query = "select c.id, c.state, c.content_type, c.title, c.alias, c.ordering, c.start, c.end, c.created_by, c.updated_by, c.note,
		f_initials.content as f_initials 
		from content c,
		content_fields f_initials 
		where f_initials.content_id=c.id and f_initials.name='initials' 
		and c.state >= 0 
		and c.content_type=? 
		ORDER BY c.title ASC";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array(2));
		$clients = $stmt->fetchAll();
		return ($clients);
	}

}