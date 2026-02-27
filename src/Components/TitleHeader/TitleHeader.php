<?php

namespace HoltBosse\Alba\Components\TitleHeader;

use HoltBosse\Alba\Core\Component;
use HoltBosse\Form\Input;
use HoltBosse\Alba\Components\CssFile\CssFile;

class TitleHeader extends Component {
    public string $header;
    public ?string $byline;
    public ?Component $rightContent;

    public function display(): void {
        (new CssFile())->loadFromConfig((object)[
            "filePath"=>__DIR__ . "/style.css",
            "injectIntoHead"=>false,
            "renderOnce" => true,
        ])->display();
        ?>
            <div <?php echo $this->renderAttributes(); ?>>
                <div>
                    <h1 class="title is-1"><?php echo Input::stringHtmlSafe($this->header); ?></h1>
                    <?php
                        if($this->byline) {
                            echo "<span class='subheading'>" . Input::stringHtmlSafe($this->byline) . "</span>";
                        }
                    ?>
                </div>
                <?php
                    if($this->rightContent) {
                        $this->rightContent->display();
                    }
                ?>
            </div>
        <?php
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        $this->header = $config->header ?? "";
        $this->byline = $config->byline ?? null;
        $this->rightContent = isset($config->rightContent) ? $config->rightContent : null;

        $this->classList[] = "addon_page_title_wrapper";

        return $this->hookObjectIntance();
    }
}