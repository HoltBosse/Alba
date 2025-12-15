<?php

namespace HoltBosse\Alba\Components\Html;

use HoltBosse\Alba\Core\Component;

class Html extends Component {
    public string $html;
    public bool $wrap;

    public function display(): void {
        if ($this->wrap) {
            ?>
                <div <?php echo $this->renderAttributes(); ?>>
                    <?php echo $this->html; ?>
                </div>
            <?php
        } else {
            echo $this->html;
        }
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        $this->html = $config->html ?? "";
        $this->wrap = $config->wrap ?? true;

        return $this;
    }
}