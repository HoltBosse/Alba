<?php
defined('CMSPATH') or die; // prevent unauthorized access

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
        echo "<div id='$id' class='pull-right buttons has-addons'>";
            foreach($buttons as $button=>$class) {
                echo    "<button
                            formaction='" . Config::uripath() . "/admin/$location/action/$button'
                            class='button is-$class'
                            type='submit'
                            " . ($button=="delete" ? "onclick='return window.confirm(\"Are you sure?\")'" : "") . "
                        >" . ucwords($button) . "</button>";
            }
        echo "</div>";
    }
}