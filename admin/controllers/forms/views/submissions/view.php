<?php
defined('CMSPATH') or die; // prevent unauthorized access

echo "<style>";
    echo file_get_contents(CMSPATH . "/admin/controllers/forms/views/submissions/style.css");
echo "</style>";

$titleText = "Form Submissions";
if($_GET["form"]) {
    $titleText = 'All “' . Input::stringHtmlSafe($_GET["form"]) . '” form submissions';
} elseif($searchFieldsObject->fields[0]->select_options[0]->value) {
    $titleText = 'All “' . $searchFieldsObject->fields[0]->select_options[0]->value . '” form submissions';
}

$urlQueryParams = $_GET;
$urlQueryParams["exportcsv"]=1;
$urlPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$exportUrl = $urlPath . "?" . http_build_query($urlQueryParams);

echo "<h1 style='display: flex; justify-content: space-between;' class='title is-1'>$titleText <a href='$exportUrl' class='button'>Export All</a></h1>";

echo "<form>";
    $searchFieldsForm->display_front_end();
echo "</form>";

if($countResults > 0) {
    $rowLength = sizeof($headerFields);
    //CMS::pprint_r($results);

    echo "<style>
        .form_submissions_wrapper {
            display: grid;
            grid-template-columns: repeat($rowLength, minmax(20rem, 1fr));
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
            }
        }
    </style>";

    echo "<div class='form_submissions_wrapper'>";
        echo "<div class='form_submissions_row header'>";
            foreach($headerFields as $header) {
                echo "<div>$header</div>";
            }
        echo "</div>";
        foreach($results as $row) {
            $data = json_decode($row->data);
            $normalizedFields = array_combine(array_column($data, 'name'), array_column($data, 'value'));
            
            echo "<div class='form_submissions_row'>";
                foreach($headerFields as $header) {
                    echo "<div>";
                        echo $normalizedFields[$header];
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
    Component::create_pagination($countResults, $paginationSize, $curPage);
    echo "<br>";
}