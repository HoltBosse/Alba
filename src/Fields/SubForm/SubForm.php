<?php

namespace HoltBosse\Alba\Fields\SubForm;

use HoltBosse\Form\Fields\SubForm\SubForm as BaseSubForm;
use HoltBosse\Form\{Field, Form, Input};
use \stdClass;

class SubForm extends BaseSubForm {

    public function loadFromConfig($config) {
        parent::loadFromConfig($config);
        $this->form_base_path = $config->form_base_path ?? $_ENV["root_path_to_forms"];

        return $this;
    }
}