<?php
namespace HoltBosse\Alba\Fields\ToggleablePassword;

use HoltBosse\Form\Fields\Input\Input as Text;

class ToggleablePassword extends Text {
    public function display(): void {
        parent::display();
        ?>
            <style>
                .bind_field_<?php echo $this->id; ?> span.is-right {
                    pointer-events: auto !important;
                    cursor: pointer;

                    * {
                        pointer-events: none !important;
                    }
                }
            </style>
            <script type='module'>
                document.querySelector('.bind_field_<?php echo $this->id; ?> span.is-right').addEventListener('click', (e)=>{
                    const input = document.getElementById('<?php echo $this->id; ?>');
                    if (input.type === 'password') {
                        input.type = 'text';
                    } else {
                        input.type = 'password';
                    }

                    const icon = e.target.querySelector("i");
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            </script>
        <?php
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);
        
        $this->input_type = $config->input_type ?? 'password';
        $this->icon_status = $config->icon_status ?? true;

        $this->icon_parent_class = $config->icon_parent_class ?? "";
        $this->icon_parent_class = $this->icon_parent_class . " has-icons-right bind_field_" . $this->id;

        $this->icon_markup = $config->icon_markup ?? "";
        $this->icon_markup = $this->icon_markup . "<span class='icon is-small is-right'><i class='fas fa-eye'></i></span>";

        return $this;
    }
}