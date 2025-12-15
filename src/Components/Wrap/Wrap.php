<?php

namespace HoltBosse\Alba\Components\Wrap;

use HoltBosse\Alba\Core\Component;

class Wrap extends Component {
    public Component $innerComponent;

    public function display(): void {
        ?>
            <div <?php echo $this->renderAttributes(); ?>>
                <?php $this->innerComponent->display(); ?>
            </div>
        <?php
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        if(isset($config->innerComponent)) {
            $this->innerComponent = $config->innerComponent;
        }

        return $this;
    }
}