<?php

Use HoltBosse\Alba\Core\{CMS, Component};
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;
Use HoltBosse\Alba\Components\Pagination\Pagination;
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;

echo "<style>";
    echo file_get_contents(__DIR__ . "/style.css");
echo "</style>";

$currentForm = Input::getVar("form", v::StringVal()->length(1, null), null) ?? $searchFieldsObject->fields[0]->select_options[0]->value ?? null;

$titleText = "Form Submissions";
if($currentForm) {
    $formTitleLookup = array_combine(array_column($formSelectOptions, 'value'), array_column($formSelectOptions, 'text'));
    $currentFormTitle = $formTitleLookup[$currentForm] ?? $currentForm;
    $titleText = 'All “' . Input::stringHtmlSafe($currentFormTitle) . '” form submissions';
}

$urlQueryParams = $_GET;
$urlQueryParams["exportcsv"]=1;
$urlPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$exportUrl = $urlPath . "?" . http_build_query($urlQueryParams);

$rightContent = "<a href='$exportUrl' class='button'>Export All</a>";

(new TitleHeader())->loadFromConfig((object)[
    "header"=>html_entity_decode($titleText),
    "rightContent"=>(new Html())->loadFromConfig((object)[
        "html"=>"<div>" . $rightContent . "</div>",
        "wrap"=>false
    ])
])->display();

echo "<form>";
    $searchFieldsForm->display();
echo "</form>";

if($countResults > 0) {
    $rowLength = sizeof($headerFields);
    //CMS::pprint_r($results);

    $rowLength+=2; //add submission date column

    echo "<style>
        .form_submissions_wrapper {
            display: grid;
            grid-template-columns: auto repeat(" . ($rowLength - 1) . ", minmax(20rem, 1fr)); /* first column fit to content for dialog open */
            overflow: auto;

            .form_submissions_row {
                display: grid;
                grid-template-columns: subgrid;
                grid-column: span $rowLength;
                border-bottom: 1px solid grey;

                &.header {
                    font-weight: bold;
                    font-size: 1.2em;
                }
                
                > div {
                    padding: 0.5rem;
                }

                > div:first-of-type a {
                    color: unset;

                    * {
                        pointer-events: none;
                    }
                }
            }
        }
    </style>";

    echo "<div class='form_submissions_wrapper'>";
        echo "<div class='form_submissions_row header'>";
            echo "<div>View</div>"; //empty cell for checkbox column
            echo "<div>Submission Date</div>";
            foreach($headerFields as $header) {
                $field = $currentSelectedForm->getFieldByName($header);
                $displayLabel = $field->label ?? $header;
                echo "<div>$displayLabel</div>";
            }
        echo "</div>";
        foreach($results as $row) {
            $currentSelectedForm->deserializeJson($row->data);
            $data = json_decode($row->data);
            $normalizedFields = array_combine(array_column($data, 'name'), array_column($data, 'value'));
            
            echo "<div class='form_submissions_row'>";
                echo "<div><a class='form_row_open'><i class='fa-solid fa-arrow-up-right-from-square'></i></a></div>";
                echo "<div>" . Date("m/d/Y g:i a", strtotime($row->created)) . "</div>";
                foreach($headerFields as $header) {
                    echo "<div>";
                        $field = $currentSelectedForm->getFieldByName($header);
                        echo $field->getFriendlyValue((object)["return_in_text_html_form"=>true]);
                        //echo Input::stringHtmlSafe($normalizedFields[$header]);
                    echo "</div>";
                }
            echo "</div>";
        }
    echo "</div>";
} else {
    echo "<p>No Results</p>";
}


if($countResults > 0) {
    echo "<br>";
    (new Pagination())->loadFromConfig((object)[
		"id"=>"pagination_component",
		"itemCount"=>$countResults,
		"itemsPerPage"=>$paginationSize,
		"currentPage"=>$curPage
	])->display();
    echo "<br>";
}

echo "<script>";
    echo file_get_contents(__DIR__ . "/script.js");
echo "</script>";