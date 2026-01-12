<?php
namespace HoltBosse\Alba\Core;

use HoltBosse\Form\Form as ExternalForm;

class HookQueryResult {
    public ExternalForm $searchForm;
    public ?array $results;
    public ?int $totalCount;
    public int $currentPage = 1;

    public function __construct(ExternalForm $searchForm, ?array $results = null, ?int $totalCount = null, int $currentPage = 1) {
        $this->searchForm = $searchForm;
        $this->results = $results;
        $this->totalCount = $totalCount;
        $this->currentPage = $currentPage;
    }
}