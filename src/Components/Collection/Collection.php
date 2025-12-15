<?php

namespace HoltBosse\Alba\Components\Collection;

use HoltBosse\Alba\Core\Component;
use \Exception;

class Collection extends Component {
    public array $items = [];

    public function display(): void {
        ?>
            <?php
                foreach($this->items as $item) {
                    if($item instanceof Component) {
                        $item->display();
                    } else {
                        throw new \Exception("Collection items must be instances of Component");
                    }
                }
            ?>
        <?php
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        if(isset($config->items) && is_array($config->items)) {
            $this->items = $config->items;
        }

        return $this;
    }
}