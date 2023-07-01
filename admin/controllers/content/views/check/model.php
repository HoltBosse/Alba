<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$all_content_types = Content::get_all_content_types();

$response="";

foreach ($all_content_types as $content_type) {

    // check for flat method and create table/columns as necessary
    $location = Content::get_content_location($content_type->id);
    $custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
    $method = $custom_fields->method ?? null; // flat or null

    $response.="<hr><p>Checking table &ldquo;{$content_type->title}&rdquo;</p>";

    if ($method=="flat") {
        $results[] = "<p>Table is flat - checking content</p>";
        $table_name = $custom_fields->id . "_flat";
        // check for table
        $exists = DB::fetch("
            SELECT table_name FROM 
                information_schema.tables 
            WHERE 
                table_name = ?
            ",$table_name);
        if (!$exists) {
            // create table
            DB::exec("create table " . $table_name . 
            " (
                content_id INT NOT NULL, 
                PRIMARY KEY (`content_id`)
            )");

            $response.="<p>Created new flat table: {$table_name}</p>";

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
                    // create column
                    DB::exec('ALTER TABLE ' . $table_name . ' ADD COLUMN ' . $f->name . ' mediumtext');
                    $response.="<p>Created column: {$f->name}</p>";
                }
            }
            
            

            // check non-flat data exists in flat table
            $og_count = DB::fetch("select count(id) as c from content where content_type=?",$content_type->id)->c;
            $flat_count = DB::fetch("select count(content_id) as c from " . $table_name)->c;
            if ($og_count > $flat_count) {
                // should get here since we truncate earlier - may change
                $all_og_ids = DB::fetchAll('select id from content where content_type=?',$content_type->id);
                // brute force synch
                // get all saveable field names
                $field_names = ["content_id"];
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
                    $og_data = DB::fetchAll('select name,content from content_fields where content_id=?',[$og_id->id]);
                    // create key array based on names
                    $named_og_data = [];
                    $named_og_data['content_id'] = $og_id->id;
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

                    $query = 'insert ignore into ' . $table_name . ' (' . $col_string . ') values (' . $params_string . ')';

                    $count++;
                    DB::exec($query, $data);
                }
                $response.="<p><strong>All {$count} saveable data itmes moved to flat table</strong></p>";
            }
        }
        else {
            $response.="<p>Flat table already exists</p>";
        }
    }
    else {
        $response.="<p>Table is in standard format</p>";
    }
}


