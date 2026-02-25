<?php
namespace HoltBosse\Alba\Core;

use \Exception;
use \SimpleXMLElement;

class DataExport {
    public array $data;
    public bool $terminateOnFinish = true;
    public bool $cleanBuffers = true;
    public string $format = "json";
    public bool $stripNewLines = true;
    public bool $outputHeaders = true;
    public ?string $filename; //set what you want without .format

    public static function getSupportedFormats(): array {
        return [
            "json"=>".json",
            "csv"=>".csv",
            "xml"=>".xml",
        ];
    }

    public function exec(): void {
        if(empty($this->data)) {
            throw new Exception("no data to export");
        }

        if(!in_array($this->format, array_keys($this::getSupportedFormats()))) {
            throw new Exception("invalid export format");
        }

        if($this->stripNewLines) {
            foreach($this->data as &$row) {
                foreach($row as &$value) {
                    $value = str_replace("\n", " ", ($value));
                    $value = str_replace("\r", " ", ($value));
                    $value = str_replace("\r\n", " ", ($value));
                    $value = str_replace("\n\r", " ", ($value));

                    unset($value);
                }

                unset($row);
            }
        }

        if($this->cleanBuffers == true) {
            while (ob_get_level() > 0) {
                ob_get_clean();
            }
        }

        if(!empty($this->filename) && $this->outputHeaders) {
            header('Content-Disposition: attachment; filename="' . $this->filename . $this->getSupportedFormats()[$this->format] . '"');
        }

        if($this->format == "json") {
            if($this->outputHeaders==true) {
                header('Content-Type: application/json');
            }

            echo json_encode($this->data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } elseif($this->format == "csv") {
            if($this->outputHeaders==true) {
                header('Content-Type: text/csv');
            }

            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($this->data[0]), escape: "");
            foreach($this->data as $row) {
                fputcsv($output, $row, escape: "");
            }
            fclose($output);
        } elseif($this->format == "xml") {
            if($this->outputHeaders==true) {
                header('Content-Type: text/xml');
            }

            $xml = new SimpleXMLElement('<root/>');

            foreach ($this->data as $item) {
                $entry = $xml->addChild('item');
                foreach ($item as $key => $value) {
                    $entry->addChild($key, htmlspecialchars($value));
                }
            }

            echo $xml->asXML();
        }

        if($this->terminateOnFinish) {
            die;
        }
    }
}