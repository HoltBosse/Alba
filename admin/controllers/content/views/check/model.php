<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$all_content_types = Content::get_all_content_types();

$response="";

foreach ($all_content_types as $content_type) {

    // create table/columns as necessary
    $location = Content::get_content_location($content_type->id);
    $custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
    $required_details_form = new Form(ADMINPATH . '/controllers/content/views/edit/required_fields_form.json');


    $response.="<hr><p>Checking table &ldquo;{$content_type->title}&rdquo;</p>";

    
    $results[] = "<p>Table is flat - checking content</p>";
    $table_name = "controller_" . $custom_fields->id ;
    // check for table
    $exists = DB::fetch("
        SELECT table_name FROM 
            information_schema.tables 
        WHERE 
            table_name = ?
        ",$table_name);

    if (!$exists) {
        // create table
        $required_fields_query = "  
        `id` int primary key NOT NULL AUTO_INCREMENT,
        `state` tinyint NOT NULL DEFAULT '1',
        `ordering` int NOT NULL DEFAULT '1',
        `title` varchar(255) NOT NULL,
        `alias` varchar(255) NOT NULL,
        `content_type` int NOT NULL COMMENT 'content_types table',
        `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `end` timestamp NULL DEFAULT NULL,
        `created_by` int NOT NULL,
        `updated_by` int NOT NULL,
        `note` varchar(255) DEFAULT NULL,
        `created` timestamp NOT NULL DEFAULT current_timestamp(),
        `category` int(11) NOT NULL DEFAULT 0,
        `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()";
        
        DB::exec("create table `" . $table_name . 
        "` (
            {$required_fields_query} 
        )");

        $response.="<p>Created new flat table: {$table_name}</p>";
    }

    // check fields and add cols as necessary
    foreach ($custom_fields->fields as $f) {
        if (property_exists($f, 'save')) {
            if ($f->save===false) {
                // skip, not saveable
                continue;
            }
        }
        // check if column exists
        $col_exists = DB::fetch("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?",[$table_name, $f->name]);
        if (!$col_exists) {
            // create column - use mediumtext if not defined in json
            $coltype = $f->coltype ?? null ? $f->coltype : " mediumtext " ;
            DB::exec('ALTER TABLE `' . $table_name . '` ADD COLUMN `' . $f->name . '` ' . $coltype);
            $response.="<p>Created column: {$f->name}</p>";
        }
        // todo: alter column checks or just leave to end-user/admin to manually change if different from column creation?
    }
    
    

    // check non-flat data exists in flat table
    $og_count = DB::fetch("select count(id) as c from content where content_type=?",$content_type->id)->c;
    $flat_count = DB::fetch("select count(id) as c from `" . $table_name . "`")->c;
    if ($og_count > $flat_count) {
        $all_og_ids = DB::fetchAll('select id from content where content_type=?',$content_type->id);
        // brute force synch
        // get all required + saveable field names
        $field_names = ["id","state","ordering","title","alias","content_type","start","end","created_by","created","updated_by","note","category"];
        foreach ($custom_fields->fields as $f) {
            if (property_exists($f, 'save')) {
                if ($f->save===false) {
                    // skip, not saveable
                    continue;
                }
            }
            $field_names[] = $f->name ;
        }
        // create column string for query
        $col_string = "`" . implode ( "`, `", $field_names ) . "`";
        $count=0;
        foreach ($all_og_ids as $og_id) {
            // loop over every original content item id
            // get list of wrapped field / col names
            $required_data = DB::fetch('select * from content where id=?',[$og_id->id]);
            $og_data = DB::fetchAll('select name,content from content_fields where content_id=?',[$og_id->id]);
            // create key array based on names
            $named_og_data = [];
            
            // required fields
            foreach ($required_data as $key => $value) {
                $named_og_data[$key] = $value;
            }
            // custom fields
            foreach ($og_data as $og) {
                $named_og_data[$og->name] = $og->content;
            }
            // build list of ? marks for params
            $params_list_arr = [];
            for($n=0; $n<sizeof($field_names); $n++) {
                $params_list_arr[] = "?";
            }
            // create array of data in same order as param list
            $data = [];
            foreach ($field_names as $field_name) {
                $data[] = $named_og_data[$field_name];
            }
            // create params list string
            $params_string = implode(",",$params_list_arr);

            $query = 'insert ignore into `' . $table_name . '` (' . $col_string . ') values (' . $params_string . ')';

            $count++;
            DB::exec($query, $data);
        }
        $response.="<p><strong>All {$count} saveable data items moved to flat table</strong></p>";
    }
    else {
        $response.="<p>All data already in new flat table</p>";
    }
}


