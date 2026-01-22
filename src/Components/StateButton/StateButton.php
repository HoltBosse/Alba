<?php

namespace HoltBosse\Alba\Components\StateButton;

use HoltBosse\Alba\Core\Component;

class StateButton extends Component {
    public ?string $itemId;
    public int $state;
    public string $multiStateFormAction;
    public string $dualStateFormAction;
    public ?array $states;
    public ?int $contentType;

    public function display(): void {
        ?>
            <style>
                <?php echo file_get_contents(__DIR__ . "/style.css"); ?>
            </style>
            <div class="center_state">
                <input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $this->itemId; ?>'/>
                <div class='<?php echo $this->states===NULL ? "" : "button state_button";?>'>
                    <button
                        <?php
                            if($this->state==0 || $this->state==1) {
                                echo "type='submit' formaction='$this->dualStateFormAction' name='id[]' value='$this->itemId'";
                            } else {
                                echo "disabled";
                            }
                        ?>
                        class="<?php echo $this->states===NULL ? "button" : "";?>"
                        <?php
                            if($this->state==0) {
                                echo "title='Unpublished - Click to publish'";
                            } elseif($this->state==1) {
                                echo "title='Published - Click to unpublish'";
                            } else {
                                $stateName = "Unknown state";
                                foreach($this->states as $stateDetails) {
                                    if($this->state==$stateDetails->state) {
                                        $stateName = $stateDetails->name;
                                    }
                                }
                                echo "title='State: $stateName'";
                            }
                        ?>
                    >
                        <?php 
                            if ($this->state==1) { 
                                echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
                            }
                            elseif ($this->state==0) {
                                echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
                            } else {
                                $ok = false;
                                foreach($this->states as $stateDetails) {
                                    if($this->state==$stateDetails->state) {
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
                    <?php if($this->states!==NULL) { ?>
                        <hr>
                        <div class="navbar navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link"></a>
                            <div class="navbar-dropdown">
                                <form action='<?php echo $this->multiStateFormAction;?>' method="post">
                                    <input type='hidden' name='content_type' value='<?= $this->contentType;?>'/>
                                    <input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $this->itemId; ?>'/>
                                    <button type='submit' formaction='<?php echo $this->multiStateFormAction;?>' name='togglestate[]' value='0' class="navbar-item">
                                        <i class="state0 fas fa-times-circle" aria-hidden="true"></i>Unpublished
                                    </button>
                                </form>
                                <form action='<?php echo $this->multiStateFormAction;?>' method="post">
                                    <input type='hidden' name='content_type' value='<?= $this->contentType;?>'/>
                                    <input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $this->itemId; ?>'/>
                                    <button type='submit' formaction='<?php echo $this->multiStateFormAction;?>' name='togglestate[]' value='1' class="navbar-item">
                                        <i class="state1 is-success fas fa-times-circle" aria-hidden="true"></i>Published
                                    </button>
                                </form>
                                
                                <hr class="dropdown-divider">
                                <?php foreach($this->states as $stateDetails) { ?>
                                    <form action='<?php echo $this->multiStateFormAction;?>' method="post">
                                        <input type='hidden' name='content_type' value='<?= $this->contentType;?>'/>
                                        <input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $this->itemId; ?>'/>
                                        <button type='submit' formaction='<?php echo $this->multiStateFormAction;?>' name='togglestate[]' value='<?php echo $stateDetails->state; ?>' class="navbar-item">
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

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        $this->itemId = $config->itemId ?? null;
        $this->state = $config->state ?? 0;
        $this->multiStateFormAction = $config->multiStateFormAction ?? "";
        $this->dualStateFormAction = $config->dualStateFormAction ?? "";
        $this->states = $config->states ?? null;
        $this->contentType = $config->contentType ?? null;

        return $this->hookObjectIntance();
    }
}