<?php

use HoltBosse\Alba\Core\{CMS};

CMS::Instance()->head_entries[] = '<script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.7/dist/htmx.min.js"></script>';

require_once(__DIR__ . "/../../components.php");