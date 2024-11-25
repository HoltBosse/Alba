<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Plugin_core_frontend_editbutton extends Plugin {
    public function init() {
        CMS::add_action("on_widget_render",$this,'handle_widget_render'); // label, function, priority 
        CMS::add_action("on_controller_render",$this,'handle_controller_render'); // label, function, priority 
        CMS::add_action("content_ready_frontend",$this,'handle_frontend_render'); // label, function, priority  
    }

    private function get_data() {
        $pluginOptions = array_combine(array_column($this->options, 'name'), array_column($this->options, 'value'));
        $groupOptionsArray = json_decode($pluginOptions["access"] ?? "") ?? [];
        $userGroups = DB::fetchall("SELECT group_id FROM user_groups WHERE user_id=?", CMS::Instance()->user->id, ["mode"=>PDO::FETCH_COLUMN]);

        return (object) [
            "pluginOptions" => $pluginOptions,
            "groupOptionsArray" => $groupOptionsArray,
            "userGroups" => $userGroups,
        ];
    }

    private function validateGroup($groupOptions, $userGroups) {
        foreach($userGroups as $item) {
            if(in_array($item, $groupOptions)) {
                return true;
            }
        }
        return false;
    }

    private function rj($input) {
        echo json_encode($input);
        die;
    }

    public function handle_widget_render($page_contents, $params) {
        $data = $this->get_data();

        if($this->validateGroup($data->groupOptionsArray, $data->userGroups) && !ADMINPATH) {
            if(Config::enable_experimental_frontend_edit() ?? false) {
                $page_contents = "
                    <div class='front_end_edit_wrap' >
                        <a class='cfe_widget_edit' id='editpointid_{$params[0]->id}' data-widgetid='{$params[0]->id}' href='#'>EDIT &ldquo;" . htmlspecialchars($params[0]->title) . "&rdquo;</a>
                    </div>
                " . $page_contents;
            } else {
                $page_contents = "
                    <div class='front_end_edit_wrap' >
                        <a style='' href='/admin/widgets/edit/{$params[0]->id}'>EDIT &ldquo;" . htmlspecialchars($params[0]->title) . "&rdquo;</a>
                    </div>
                " . $page_contents;
            }
            return $page_contents;
        } else {
            return $page_contents;
        }
    }

    public function handle_controller_render($page_contents, $params) {
        $data = $this->get_data();

        if($this->validateGroup($data->groupOptionsArray, $data->userGroups) && (Config::enable_experimental_frontend_edit() ?? false) && ADMINPATH===false){
            $controllerConfig = DB::fetch("SELECT * FROM content_types WHERE controller_location = ?", $params[0]);
            $page_contents = "
                <div class='front_end_edit_wrap' >
                    <a class='cfe_controller_edit' id='editpointid_" . CMS::Instance()->page->id . "' data-pageid='" . CMS::Instance()->page->id . "' href='#'>EDIT &ldquo;" . htmlspecialchars($controllerConfig->title) . "&rdquo;</a>
                </div>
            " . $page_contents;
        }

        return $page_contents;
    }

    private function handle_api_requests($data) {
        //this function assumes that a user is logged in and the feature is enabled

        if(Input::getvar("cfe_content_update")) {
            ob_get_clean();

            header('Content-Type: application/json; charset=utf-8');
            
            $contentid = Input::getvar("cfe_contentid");
            $contenttype = Input::getvar("cfe_contenttype");
            $contentfield = Input::getvar("cfe_contentfield");
            $contentdata = Input::getvar("cfe_contentdata");

            if(!$contentid || !$contenttype || !$contentfield || !$contentdata) {
                http_response_code(500); 
                die;
            }

            $table = Content::get_table_name_for_content_type($contenttype);

            $fieldNames = DB::fetchall("SHOW fields FROM $table");
            $fieldNames = array_column($fieldNames, "Field");

            //TODO: maybe nicely show content over length limit???

            //safety check that we arent getting a mess
            if(!in_array($contentfield, $fieldNames)) {
                http_response_code(500); 
                die;
            }

            //clean it because im not trusting
            $contentfield = str_replace("`","``",$contentfield);

            DB::exec("UPDATE $table SET `$contentfield`=? WHERE id=?", [$contentdata, $contentid]);

            $this->rj([
                "success"=>1,
                "message"=>"Updated",
                "data"=>(object) [],
            ]);
        }

        if(Input::getvar("cfe_widget_fields")) {
            ob_get_clean();

            $widgetid = Input::getvar("cfe_widgetid");

            if(!$widgetid) {
                http_response_code(500);
                die;
            }

            $widget = new Widget();
            $widget->load($widgetid);
            $form = new Form($widget->form_data);

            foreach ($widget->options as $option) {
                //echo "$key => $value\n";
                $field = $form->get_field_by_name($option->name);
                if ($field) {
                    $field->default = $option->value;
                }
                else {
                    // do nothing, leave default from form json
                }
            }

            echo "<html data-theme='light'>";
            require_once(CMSPATH . "/admin/templates/clean/headlibraries.php");

            echo "
                <style>
                    html {
                        /* bulma dumb */
                        overflow-y: auto !important;
                    }
                </style>
                <form method='post' target='_parent'>
                    <input type='hidden' name='cfe_widget_fields_submit' value='true'>
                    <input type='hidden' name='cfe_widgetid' value='$widgetid'>
            ";
                    $form->display_front_end();
            echo "
                    <button class='button is-success'>Submit</button>
                </form>
            ";

            die;

        }

        if(Input::getvar("cfe_widget_fields_submit")) {
            
            $widgetid = Input::getvar("cfe_widgetid");

            if(!$widgetid) {
                http_response_code(500); 
                die;
            }

            $widget = new Widget();
            $widget->load($widgetid);
            $form = new Form($widget->form_data);

            $form->set_from_submit();

            $options = [];
            foreach ($form->fields as $option) {
                $obj = new stdClass();
                $obj->name = $option->name;
                $obj->value = $option->default;
                //$obj->{$option->name} = $option->default;
                $options[] = $obj;
            }

            $options_json = json_encode($options);

            DB::exec("UPDATE widgets set options=? where id=?", [$options_json, $widgetid]);



            header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            die;

        }

        if(Input::getvar("cfe_controller_fields")) {
            ob_get_clean();

            //header('Content-Type: application/json; charset=utf-8');

            $pageid = Input::getvar("cfe_pageid");

            if(!$pageid) {
                http_response_code(500); 
                die;
            }

            $page = new Page();
            $page->load_from_id($pageid);

            if ($page->view>0) {
                $content_loc = Content::get_content_location($page->content_type);
                $view_loc = Content::get_view_location($page->view);
                // OLD method
                //include_once (CMSPATH . "/controllers/" . $content_loc . "/views/" . $view_loc . "/options.php");
                // NEW uses json forms
                $options_form = new Form(CMSPATH . "/controllers/" . $content_loc . "/views/" . $view_loc . "/options_form.json");
                // set options form values from json stored in view_configuration
                $options_form->deserialize_json($page->view_configuration);

                echo "<html data-theme='light'>";
                require_once(CMSPATH . "/admin/templates/clean/headlibraries.php");

                echo "
                    <style>
                        html {
                            /* bulma dumb */
                            overflow-y: auto !important;
                        }
                    </style>
                    <form method='post' target='_parent'>
                        <input type='hidden' name='cfe_controller_fields_submit' value='true'>
                        <input type='hidden' name='cfe_pageid' value='$pageid'>
                ";
                    $options_form->display_front_end();
                echo "
                        <button class='button is-success'>Submit</button>
                    </form>
                ";
            } else {
                die;
            }

            die;

        }

        if(Input::getvar("cfe_controller_fields_submit")) {
            
            $pageid = Input::getvar("cfe_pageid");

            if(!$pageid) {
                http_response_code(500); 
                die;
            }

            $page = new Page();
            $page->load_from_id($pageid);

            if ($page->view>0) {
                $content_loc = Content::get_content_location($page->content_type);
                $view_loc = Content::get_view_location($page->view);

                $options_form = new Form(CMSPATH . "/controllers/" . $content_loc . "/views/" . $view_loc . "/options_form.json");
                $options_form->set_from_submit();
                $page->view_configuration = $options_form->serialize_json();

                $page->save();

            } else {
                die;
            }


            header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            die;

        }
    }

    public function handle_frontend_render($page_contents, $params) {
        $data = $this->get_data();

        if($this->validateGroup($data->groupOptionsArray, $data->userGroups) && (Config::enable_experimental_frontend_edit() ?? false)) {
            ob_start();
                ?>
                    <style>
                        /* TODO: make this indicator better */
                        .cfe_editable_content {
                            /* outline: 1px dashed white; */
                            position: relative;

                            &::after {
                                position: absolute;
                                right: -0.5rem;
                                top: -0.5rem;
                                content: " ";
                                border-radius: 50%;
                                background-color: white;
                                width: 1rem;
                                height: 1rem;
                            }
                        }

                        .cfe_widget_dialog {

                            &::backdrop {
                                background: rgba(0,0,0,0.8);
                            }

                            & .cfe_closeme {
                                float: right;
                                cursor: pointer;
                            }
                        }
                    </style>
                    <script>
                        window.addEventListener("load", (e)=>{
                            document.querySelectorAll(".cfe_editable_content").forEach((el)=>{
                                el.contentEditable = true;
                            });

                            function submitContent(e) {
                                //console.log("hiii");

                                const formData = new FormData();
                                formData.append("cfe_content_update", true);
                                formData.append("cfe_contentid", e.target.dataset.contentid);
                                formData.append("cfe_contenttype", e.target.dataset.contenttype);
                                formData.append("cfe_contentfield", e.target.dataset.contentfield);
                                formData.append("cfe_contentdata", e.target.innerText);

                                fetch(window.location.href, {
                                    method: "POST",
                                    body: formData,
                                }).then((response) => response.json()).then((data) => {
                                    //console.log(data);
                                    //window.location.reload();
                                }).catch((e)=>{
                                    alert("Failed to update content");
                                    window.location.reload();
                                });
                            }

                            function defocus() {
                                let tmp = document.createElement("input");
                                document.body.appendChild(tmp);
                                tmp.focus();
                                document.body.removeChild(tmp);
                            }

                            document.body.addEventListener("focusout", (e)=>{
                                if(e.target.classList.contains("cfe_editable_content")) {
                                    submitContent(e);
                                }
                            });

                            document.body.addEventListener("keydown", (e)=>{
                                if(e.target.classList.contains("cfe_editable_content") && e.keyCode === 13) {
                                    e.preventDefault();
                                    defocus();
                                    //submitContent(e);
                                }
                            });

                            document.body.addEventListener("click", (e)=>{
                                if(e.target.classList.contains("cfe_widget_edit") || e.target.classList.contains("cfe_controller_edit")) {
                                    e.preventDefault();
                                    console.log("controller/widget editable");

                                    let isWidget = e.target.classList.contains("cfe_widget_edit");
                                    let isController = e.target.classList.contains("cfe_controller_edit");

                                    const formData = new FormData();
                                    if(isWidget) {
                                        formData.append("cfe_widget_fields", true);
                                        formData.append("cfe_widgetid", e.target.dataset.widgetid);
                                    } else if(isController) {
                                        formData.append("cfe_controller_fields", true);
                                        formData.append("cfe_pageid", e.target.dataset.pageid);
                                    }

                                    const queryString = new URLSearchParams(formData).toString();
                                    //console.log(queryString);

                                    let dialog = document.createElement("dialog");
                                    document.body.appendChild(dialog);
                                    dialog.classList.add("cfe_widget_dialog");
                                    dialog.innerHTML = `
                                        <h5>${e.target.innerText.replace("EDIT", "EDITING")} <span class="cfe_closeme">X</span></h5>
                                        <iframe style="min-width: 90vw; max-width: 90vw; max-height: 80vh;" src="${window.location.href.includes("?") ? "&"+queryString : "?"+queryString}"></iframe>
                                        <br>
                                    `;

                                    //fix the height hack
                                    dialog.querySelector("iframe").addEventListener("load", (ei)=>{
                                        ei.target.height = ei.target.contentWindow.document.body.scrollHeight+"px";
                                        let link = window.location.href;
                                        if(link.includes("#")) {
                                            link = link.split("#")[0];
                                        }

                                        ei.target.contentWindow.document.querySelector("form").setAttribute("action", link+"#"+e.target.id);
                                    })

                                    dialog.querySelector(".cfe_closeme").addEventListener("click", (e)=>{
                                        let parentDialog = e.target.closest("dialog");
                                        parentDialog.close();
                                        document.body.removeChild(parentDialog);
                                        window.location.reload(); //reloading because readding the js would potentially cause issues
                                    });

                                    dialog.showModal();

                                }
                            });
                        })
                    </script>
                <?php
            $contents = ob_get_clean();

            CMS::Instance()->head_entries[] = $contents;

            $this->handle_api_requests($data);
        }

        return $page_contents;
    }
}




