<?php

namespace HoltBosse\Alba\Components\Pagination;

use HoltBosse\Alba\Core\Component;
use HoltBosse\Alba\Core\CMS;

class Pagination extends Component {
    public int $itemCount = 0;
    public int $itemsPerPage = 10;
    public int $currentPage = 1;

    public function display(): void {
        $num_pages = ceil($this->itemCount/$this->itemsPerPage);

        //$url_query_params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        $url_query_params = $_GET;
        $url_path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

        if ($this->currentPage) {
            // not ordering view and page url is either 1 or no passed and assumed to be 1 in model
            $url_query_params['page'] = $this->currentPage+1;
            $next_url_params = http_build_query($url_query_params);
            $url_query_params['page'] = $this->currentPage-1;
            $prev_url_params = http_build_query($url_query_params);
        }
        ?>

        <?php if ($this->itemCount>$this->itemsPerPage):?>
            <style>
                <?php echo file_get_contents(__DIR__ . "/style.css"); ?>
            </style>
            <nav <?php echo $this->renderAttributes(); ?>>
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
                        <a class='pagination-link' href='<?=$url_path . "?" . ($this->currentPage!=1 ? $prev_url_params : $page_one_url_params)?>'><</a>
                    </li>
                    <?php for ($n=($this->currentPage-2>0 ? $this->currentPage-2 : 1); $n<=$num_pages && $n<=$this->currentPage+2; $n++):?>
                        <?php 
                            $url_query_params['page'] = $n;
                            $url_params = http_build_query($url_query_params);
                        ?>
                        <li> 
                            <a class='pagination-link <?php if ($n==$this->currentPage) {echo "is-current";}?>' href='<?=$url_path . "?" . $url_params?>'><?php echo $n;?></a>
                        </li>
                    <?php endfor; ?>
                    <li> 
                        <a class='pagination-link' href='<?=$url_path . "?" . ($this->currentPage!=$num_pages ? $next_url_params : $page_last_url_params)?>'>></a>
                    </li>
                    <li> 
                        <a class='pagination-link' href='<?=$url_path . "?" . $page_last_url_params?>'>>></a>
                    </li>
                </ul>
            </nav>
        <?php endif;
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        $this->classList = array_merge($this->classList, ["pagination", "is-centered"]);
        $this->attributes["role"] = "navigation";
        $this->attributes["aria-label"] = "pagination";

        if (isset($config->itemCount)) {
            $this->itemCount = $config->itemCount;
        }
        if (isset($config->itemsPerPage)) {
            $this->itemsPerPage = $config->itemsPerPage;
        }
        if (isset($config->currentPage)) {
            $this->currentPage = $config->currentPage;
        }

        return $this->hookObjectIntance();
    }
}