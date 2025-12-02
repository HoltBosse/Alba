<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;

class Component {
    public static function create_pagination($item_count, $pagination_size, $cur_page) {
        $num_pages = ceil($item_count/$pagination_size);

        //$url_query_params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        $url_query_params = $_GET;
        $url_path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        if ($cur_page) {
            // not ordering view and page url is either 1 or no passed and assumed to be 1 in model
            $url_query_params['page'] = $cur_page+1;
            $next_url_params = http_build_query($url_query_params);
            $url_query_params['page'] = $cur_page-1;
            $prev_url_params = http_build_query($url_query_params);
        }
        ?>

        <?php if ($item_count>$pagination_size):?>
            <style>
                .small-pagination-list a {
                    margin: 0;
                }
                .small-pagination-list li:not(:last-child) a {
                    border-right: 0px solid transparent;
                    border-top-right-radius: 0px;
                    border-bottom-right-radius: 0px;
                }
                .small-pagination-list li:not(:first-child) a {
                    border-top-left-radius: 0px;
                    border-bottom-left-radius: 0px;
                }
                .small-pagination-list li a.is-current {
                    font-weight: bold;
                    font-size: 1.075em;
                }
            </style>
            <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                <ul class="pagination-list small-pagination-list">
                    <?php
                        $url_query_params['page'] = 1;
                        $page_one_url_params = http_build_query($url_query_params);

                        $url_query_params['page'] = $num_pages;
                        $page_last_url_params = http_build_query($url_query_params);
                    ?>
                    <li> 
                        <a class='pagination-link' href='<?=$url_path . "?" . $page_one_url_params?>'><<</a>
                    </li>
                    <li> 
                        <a class='pagination-link' href='<?=$url_path . "?" . ($cur_page!=1 ? $prev_url_params : $page_one_url_params)?>'><</a>
                    </li>
                    <?php for ($n=($cur_page-2>0 ? $cur_page-2 : 1); $n<=$num_pages && $n<=$cur_page+2; $n++):?>
                        <?php 
                            $url_query_params['page'] = $n;
                            $url_params = http_build_query($url_query_params);
                        ?>
                        <li> 
                            <a class='pagination-link <?php if ($n==$cur_page) {echo "is-current";}?>' href='<?=$url_path . "?" . $url_params?>'><?php echo $n;?></a>
                        </li>
                    <?php endfor; ?>
                    <li> 
                        <a class='pagination-link' href='<?=$url_path . "?" . ($cur_page!=$num_pages ? $next_url_params : $page_last_url_params)?>'>></a>
                    </li>
                    <li> 
                        <a class='pagination-link' href='<?=$url_path . "?" . $page_last_url_params?>'>>></a>
                    </li>
                </ul>
            </nav>
        <?php endif;
    }

    public static function addon_button_group($id, $location, $buttons=["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]) {
        echo "<div id='$id' class='buttons has-addons'>";
            foreach($buttons as $button=>$class) {
                echo    "<button
                            formaction='" . $_ENV["uripath"] . "/admin/$location/action/$button'
                            class='button is-$class is-small is-outlined'
                            type='submit'
                            " . ($button=="delete" ? "onclick='return window.confirm(\"Are you sure?\")'" : "") . "
                        >" . ucwords($button) . "</button>";
            }
        echo "</div>";
    }

    public static function addon_button_toolbar($addonButtonGroupArgs, $leftContent="") {
        ?>
            <style>
                .addon_button_toolbar {
                    position: sticky;
                    top: 0;
                    display: flex;
                    gap: 1rem;
                    justify-content: space-between;
                    padding: 1rem 0;
                    z-index: 1;
                    background-color: var(--bulma-body-background-color);
                }

                @media only screen and (max-width: 1024px) {
                    .addon_button_toolbar {
                        top: calc(var(--bulma-navbar-height) + 1.4rem);
                    }
                }
            </style>
            <script>
                document.addEventListener("adminRowSelected", (e)=>{
                    document.querySelectorAll("#<?php echo $addonButtonGroupArgs[0]; ?> button").forEach((el)=>{
                        if(e.detail.counter > 0) {
                            el.classList.remove("is-outlined");
                        } else {
                            el.classList.add("is-outlined");
                        }
                    });
                });
            </script>
        <?php

        echo "<div class='addon_button_toolbar'>";
            echo "<div>";
                echo $leftContent;
            echo "</div>";
            Component::addon_button_group(...$addonButtonGroupArgs);
        echo "</div>";
    }

    public static function addon_page_title($header, $byline=null, $rightContent=null) {
        ?>
            <style>
                .addon_page_title_wrapper {
                    display: flex;
                    gap: 1rem;
                    justify-content: space-between;

                    &:has(.subheading) h1 {
                        margin-bottom: 0.5rem; 
                    }

                    &:not(.subheading) {
                        margin-bottom: var(--bulma-block-spacing);
                    }

                    .subheading {
                        display: block;
                        font-size: 1.5rem;
                        font-weight: normal;
                    }
                }

                @media only screen and (max-width: 1024px) {
                    .addon_page_title_wrapper {
                        flex-direction: column;
                    }
                }
            </style>
            <div class="addon_page_title_wrapper">
                <div>
                    <h1 class="title is-1"><?php echo $header; ?></h1>
                    <?php
                        if($byline) {
                            echo "<span class='subheading'>$byline</span>";
                        }
                    ?>
                </div>
                <?php
                    if($rightContent) {
                        echo "<div>$rightContent</div>";
                    }
                ?>
            </div>
        <?php
    }

    public static function render_admin_nav($navigation, $enable_overrides=true) {
        //plugin hook if the config isnt powerful enough
        $navigation = Hook::execute_hook_filters('render_admin_nav', $navigation);

        //render the nav
        foreach($navigation as $label=>$config) {
            //setting this explicitly to null to solve the following warning spam: Undefined array key "$key" in $filepath
            if (Access::can_access(Access::getAdminAccessRule($label) ?? null)) {
                if($config["type"]=="addition_menu") {
                    Component::render_admin_nav_menu($config["menu"]);
                } elseif($config["type"]=="addition_link") {
                    Component::render_admin_nav_link($config["link"]);
                }
            }
        }
    }

    public static function render_admin_nav_menu($menu) {
        ?>
            <div class="navbar-item has-dropdown is-hoverable">
                <a class="navbar-link"><?php echo ucwords($menu["label"]); ?></a>
                <div class="navbar-dropdown">
                    <?php
                        foreach($menu["links"] as $label=>$url) {
                            if($label=="hr" || $url=="hr") {
                                echo "<hr class='dropdown-divider'>";
                            } else {
                                //echo $label . "<br>";
                                echo "<a class='navbar-item' href='" . $_ENV["uripath"] . "$url'>" . ucwords($label) . "</a>";
                            }
                        }
                    ?>
                </div>
            </div>
        <?php
    }

    public static function render_admin_nav_link($link) {
        echo "<a class='navbar-item' href='" . $_ENV["uripath"] . "{$link['url']}'>" . ucwords($link["label"]) . "</a>";
    }

    public static function create_fixed_control_bar($middleButtonHtml="", $endBarHtml="") {
        ?>
            <style>
                .fixed-control-bar {
                    position:fixed;
                    overflow:hidden;
                    width:100vw;
                    padding:1rem;
                    border-bottom:1px solid #aaa;
                    box-shadow:0 0 10px rgba(0,0,0,0.5);
                    top:0;
                    left:0;
                    background-color: var(--bulma-body-background-color) !important;
                    z-index:40;
                }
            </style>
            <script>
                function fixedControllerBarGoBack() {
                    if(window.history.length <= 1) {
                        if(document.referrer!=='' && (new URL(document.referrer)).origin == window.location.origin) {
                            window.location.href = document.referrer;
                            return;
                        }
                        window.location.href = window.location.origin + window.uripath + "/admin";
                    } else {
                        window.history.back();
                    }
                }
            </script>
            <div class="fixed-control-bar">
                <button title="Save and exit" class="button is-primary" type="submit">Save</button>
                <?php echo $middleButtonHtml; ?>
                <button class="button is-warning" type="button" onclick="fixedControllerBarGoBack();">Cancel</button>
                <?php echo $endBarHtml; ?>
            </div>
        <?php
    }

    public static function state_toggle($id, $state, $folder, $states, $type) {
        ?>
            <style>
                .state_button {
                    padding: 0 !important;
                }

                .state_button button {
                    height: 100%;
                    padding: 1em 1em;
                    border: 1px solid transparent;
                    width: 100%;
                    background-color: #fff;
                }

                .state_button button:hover {
                    background-color: #f5f5f5;
                    cursor: pointer;
                }

                .state_button hr {
                    width: 1px;
                    height: 70%;
                }

                .state_button .navbar-link:not(.is-arrowless)::after {
                    right: auto;
                }

                .state_button .navbar-link:not(.is-arrowless) {
                    padding-right: 1.5em;
                }

                .state_button .navbar-item.has-dropdown {
                    height: 100%;
                }

                .state_button .navbar-item {
                    display: flex;
                    gap: 1em;
                }

                .state_button .navbar {
                    z-index: auto;
                }

                .state_button:hover button.navbar-item:not(:hover) {
                    background-color: hsl(var(--bulma-navbar-dropdown-item-h),var(--bulma-navbar-dropdown-item-s),calc(var(--bulma-navbar-dropdown-item-background-l) + var(--bulma-navbar-item-background-l-delta))) !important;
                }

                @media screen and (max-width: 1024px) {
                    /* disabled on mobile due to bulma lack of support */
                    .state_button .navbar-item.has-dropdown, .state_button hr {
                        display: none;
                    }
                }
            </style>
            <div class="center_state">
                <input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $id; ?>'/>
                <div class='<?php echo $states===NULL ? "" : "button state_button";?>'>
                    <button <?php if($state==0 || $state==1) { echo "type='submit' formaction='" . $_ENV["uripath"] . "/admin/" . $folder . "/action/toggle' name='id[]' value='$id'"; } else { echo "style='pointer-events: none;'"; } ?> class="<?php echo $states===NULL ? "button" : "";?>">
                        <?php 
                            if ($state==1) { 
                                echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
                            }
                            elseif ($state==0) {
                                echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
                            } else {
                                $ok = false;
                                foreach($states as $stateDetails) {
                                    if($state==$stateDetails->state) {
                                        echo "<i style='color:$stateDetails->color' class='fas fa-times-circle' aria-hidden='true'></i>";
                                        $ok = true;
                                    }
                                }
                                if(!$ok) {
                                    echo "<i class='fas fa-times-circle' aria-hidden='true'></i>"; //default grey color if state not found
                                }
                            }
                        ?>
                    </button>
                    <?php if($states!==NULL) { ?>
                        <hr>
                        <div class="navbar navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"></a>
                            <div class="navbar-dropdown">
                                <form action='<?php echo $_ENV["uripath"];?>/admin/<?php echo $folder; ?>/action/togglestate' method="post">
                                    <input type='hidden' name='content_type' value='<?= $type;?>'/>
                                    <input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $id; ?>'/>
                                    <button type='submit' formaction='<?php echo $_ENV["uripath"];?>/admin/<?php echo $folder; ?>/action/togglestate' name='togglestate[]' value='0' class="navbar-item">
                                        <i class="state0 fas fa-times-circle" aria-hidden="true"></i>Unpublished
                                    </button>
                                </form>
                                <form action='<?php echo $_ENV["uripath"];?>/admin/<?php echo $folder; ?>/action/togglestate' method="post">
                                    <input type='hidden' name='content_type' value='<?= $type;?>'/>
                                    <input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $id; ?>'/>
                                    <button type='submit' formaction='<?php echo $_ENV["uripath"];?>/admin/<?php echo $folder; ?>/action/togglestate' name='togglestate[]' value='1' class="navbar-item">
                                        <i class="state1 is-success fas fa-times-circle" aria-hidden="true"></i>Published
                                    </button>
                                </form>
                                
                                <hr class="dropdown-divider">
                                <?php foreach($states as $stateDetails) { ?>
                                    <form action='<?php echo $_ENV["uripath"];?>/admin/<?php echo $folder; ?>/action/togglestate' method="post">
                                        <input type='hidden' name='content_type' value='<?= $type;?>'/>
                                        <input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $id; ?>'/>
                                        <button type='submit' formaction='<?php echo $_ENV["uripath"];?>/admin/<?php echo $folder; ?>/action/togglestate' name='togglestate[]' value='<?php echo $stateDetails->state; ?>' class="navbar-item">
                                            <i style="color:<?php echo $stateDetails->color; ?>" class="fas fa-times-circle" aria-hidden="true"></i><?php echo $stateDetails->name; ?>
                                        </button>
                                    </form>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php
    }
}