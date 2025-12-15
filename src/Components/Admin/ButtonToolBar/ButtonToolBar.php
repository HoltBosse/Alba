<?php

namespace HoltBosse\Alba\Components\Admin\ButtonToolBar;

use HoltBosse\Alba\Core\Component;
use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup;

class ButtonToolBar extends Component {
    public StateButtonGroup $stateButtonGroup;
    public Component $leftContent;

    public function display(): void {
        ?>
            <style>
                <?php echo file_get_contents(__DIR__ . "/style.css"); ?>
            </style>
            <script>
                document.addEventListener("adminRowSelected", (e)=>{
                    document.querySelectorAll("#<?php echo $this->stateButtonGroup->id; ?> button").forEach((el)=>{
                        if(e.detail.counter > 0) {
                            el.classList.remove("is-outlined");
                        } else {
                            el.classList.add("is-outlined");
                        }
                    });
                });
            </script>
        <?php

        echo "<div " . $this->renderAttributes() . ">";
            $this->leftContent->display();
            $this->stateButtonGroup->display();
        echo "</div>";
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        if(isset($config->stateButtonGroup)) {
            $this->stateButtonGroup = $config->stateButtonGroup;
        }

        if(isset($config->leftContent)) {
            $this->leftContent = $config->leftContent;
        }

        $this->classList[] = "addon_button_toolbar";

        return $this;
    }
}