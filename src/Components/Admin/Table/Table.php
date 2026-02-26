<?php

namespace HoltBosse\Alba\Components\Admin\Table;

use HoltBosse\Alba\Core\{Component, Hook, Access, CMS};
Use HoltBosse\Alba\Components\Button\{Button, ButtonType};
Use HoltBosse\Alba\Components\Wrap\Wrap;
Use HoltBosse\Alba\Components\Collection\Collection;
Use HoltBosse\Alba\Components\Html\Html;
Use Respect\Validation\Validator as v;
Use HoltBosse\Form\Input;

class Table extends Component {
    // @phpstan-ignore missingType.iterableValue
    public array $columns;
    // @phpstan-ignore missingType.iterableValue
    public array $rows;
    public string $trClass = "normal_admin_row";

    private function make_sortable_header(string $title): void {
        ?>
            <th>
                <label class="orderablerow">
                    <span><?php echo Input::stringHtmlSafe(ucwords($title)); ?></span>
                    <i class="tableorder fas fa-sort"></i>
                    <i class="tableorder fas fa-sort-up"></i>
                    <i class="tableorder fas fa-sort-down"></i>
                </label>
                <?php $selectedTitle = Input::getVar(str_replace(" ", "_", $title) . "_order", v::StringVal()->in(["asc", "desc", "regular"]), "regular");?>
                <input type="radio" name="<?php echo str_replace(" ", "_", $title); ?>_order" form="orderform" value="regular" <?php echo $selectedTitle=="regular" ? "checked" : ""; ?> />
                <input type="radio" name="<?php echo str_replace(" ", "_", $title); ?>_order" form="orderform" value="asc" <?php echo $selectedTitle=="asc" ? "checked" : ""; ?> />
                <input type="radio" name="<?php echo str_replace(" ", "_", $title); ?>_order" form="orderform" value="desc" <?php echo $selectedTitle=="desc" ? "checked" : ""; ?> />
            </th>
        <?php
    }

    public function display(): void {
        if(Input::getVar("form_search_form", v::AlwaysValid(), null)) {
            echo "<style>
                    .orderablerow{
                        pointer-events: none;

                        i {
                            display: none !important;
                        }
                    }
                </style>";
        }

        echo "<style>";
            echo file_get_contents(__DIR__ . "/style.css");

            echo ".$this->trClass.selected {
                background:rgba(200,255,200,0.3);
            }";

            echo "@media screen and (min-width: 1024px) {";
                $baseGridCount = 0;
                foreach($this->columns as $index=>$column) {
                    $baseGridCount += $column->columnSpan;

                    if($column->columnSpan != 1) {
                        echo "table.table td:nth-of-type(" . ($index + 1) . "), table.table th:nth-of-type(" . ($index + 1) . ") {";
                            echo "grid-column: span {$column->columnSpan};";
                        echo "}";
                    }
                }
                echo "table.table tr {";
                    echo "grid-template-columns: repeat($baseGridCount, 1fr) !important;";  
                echo "}";
            echo "}";
        echo "</style>";

        echo "<table {$this->renderAttributes()}>";
            echo "<thead><tr>";
                foreach($this->columns as $column) {
                    if($column->sortable) {
                        $this->make_sortable_header($column->label);
                    } else {
                        echo "<th>{$column->label}</th>";
                    }
                }
            echo "</tr></thead>";
            echo "<tbody>";
                foreach($this->rows as $row) {
                    echo "
                        <tr
                            class='{$this->trClass}'
                            id='row_id_{$row->id}'
                            data-itemid='{$row->id}'
                            " . (isset($row->ordering) ? "data-ordering='{$row->ordering}'" : "") . "
                        >
                    ";
                        foreach($this->columns as $column) {
                            echo "
                                <td
                                    " . (!isset($column->tdAttributes["dataset-name"]) ? "dataset-name='{$column->label}'" : "") . "
                                    " . (isset($column->tdAttributes) ? $column->renderRowAttributes(): "") . "
                                >";
                                if(!is_null($column->renderer)) {
                                    $column->value = $row->{$column->rowAttribute};
                                    $column->display();
                                } else {
                                    echo $row->{$column->rowAttribute};
                                }
                            echo "</td>";
                        }
                    echo "</tr>";
                }
            echo "</tbody>";
        echo "</table>";

        if(in_array(true, array_column($this->columns, "sortable"))) {
            echo "<script>" . file_get_contents(__DIR__ . "/ordering.js") . "</script>";
        }

        echo "<script type='module'>
            import {handleAdminRows} from '/js/admin_row.js';
            handleAdminRows('.$this->trClass');
        </script>";
    }

    public function loadFromConfig(object $config): self {
        parent::loadFromConfig($config);

        //check that $columns is an array of TableField objects
        if(!isset($config->columns) || !is_array($config->columns)) {
            throw new \Exception("Table requires a columns array to be set.");
        } else {
            foreach($config->columns as $column) {
                if(!$column instanceof TableField) {
                    throw new \Exception("Table columns must be instances of TableField.");
                }
            }
            $this->columns = $config->columns;
        }

        //check that $rows is an array of objects
        if(!isset($config->rows) || !is_array($config->rows)) {
            throw new \Exception("Table requires a rows array to be set.");
        } else {
            foreach($config->rows as $row) {
                if(!is_object($row)) {
                    throw new \Exception("Table rows must be arrays of objects.");
                }
            }
            $this->rows = $config->rows;
        }

        $this->trClass = $config->trClass ?? "normal_admin_row";
        $this->classList[] = "table";
        $this->classList[] = "can-have-ids";

        return $this->hookObjectIntance();
    }
}