<?php
defined('CMSPATH') or die; // prevent unauthorized access

$action = CMS::Instance()->uri_segments[2];
if (!$action) {
	CMS::Instance()->queue_message('Unknown action','danger', $_SERVER['HTTP_REFERER']);
}


$id = Input::getvar('id','ARRAYOFINT');
if (!$id) {
	CMS::Instance()->queue_message('Cannot perform action on unknown items','danger', $_SERVER['HTTP_REFERER']);
}

if ($action=='toggle') {
	Actions::add_action("pageupdate", (object) [
		"affected_page"=>$id[0],
	]);
	$result = DB::exec("UPDATE pages SET state = (CASE state WHEN 1 THEN 0 ELSE 1 END) where id=?", [$id[0]]); // id always array even with single id being passed
	if ($result) {
		$page = DB::fetch('SELECT * FROM pages WHERE id=?', [$id[0]]);
		$msg = "Page <a href='" . Config::uripath() . "/admin/pages/edit/{$id[0]}/{$page->content_type}/{$page->view}'>{$page->title}</a> state toggled";
		CMS::Instance()->queue_message($msg,'success', $_SERVER['HTTP_REFERER']);
	}
	else {
		CMS::Instance()->queue_message('Failed to toggle state of page','danger', $_SERVER['HTTP_REFERER']);
	}
}

function updatePagesState($id, $state, $action_text) {
    foreach ($id as $item) {
        Actions::add_action("pageupdate", (object) [
            "affected_page" => $item,
        ]);
    }
    $idlist = implode(',', $id);
    $result = DB::exec("UPDATE pages SET state = ? WHERE id IN ({$idlist})", [$state]);
    
    if ($result) {
        $page_links = [];
        foreach ($id as $page_id) {
            $page = DB::fetch('SELECT * FROM pages WHERE id=?', [$page_id]);
            if ($page) {
                $link = "<a target='_blank' href='" . Config::uripath() . "/admin/pages/edit/{$page_id}/{$page->content_type}/{$page->view}'>{$page->title}</a>";
                $page_links[] = $link;
            }
        }
        $messages = "Page(s) " . implode(', ', $page_links) . " {$action_text}";
        CMS::Instance()->queue_message($messages, 'success', $_SERVER['HTTP_REFERER']);
    } else {
        CMS::Instance()->queue_message("Failed to {$action_text} pages", 'danger', $_SERVER['HTTP_REFERER']);
    }
}

if ($action == 'publish') {
    updatePagesState($id, 1, 'published');
}

if ($action == 'unpublish') {
    updatePagesState($id, 0, 'unpublished');
}

if ($action=='delete') {
	foreach($id as $item) {
		Actions::add_action("pagedelete", (object) [
			"affected_page"=>$item,
		]);
	}
	$idlist = implode(',',$id);
	$query = "select count(parent) as c from pages where parent in ({$idlist})";
	$stmt = CMS::Instance()->pdo->prepare($query);
	$result = $stmt->execute(array());
	if ($result) {
		$c = $stmt->fetch()->c;
		if ($c>0) {
			CMS::Instance()->queue_message('Cannot delete page(s) with children','danger', $_SERVER['HTTP_REFERER']);
		}
		else {
			$result = DB::exec("UPDATE pages SET alias = CONCAT(alias,'_DELETED'), parent=-1, state = -1 where id in ({$idlist})"); 
			if ($result) {
				CMS::Instance()->queue_message('Deleted pages','success', $_SERVER['HTTP_REFERER']);
			}
			else {
				CMS::Instance()->queue_message('Failed to delete pages','danger', $_SERVER['HTTP_REFERER']);
			}
		}
	}
	else {
		CMS::Instance()->queue_message('Error checking for child pages','danger', $_SERVER['HTTP_REFERER']);
	}
}

