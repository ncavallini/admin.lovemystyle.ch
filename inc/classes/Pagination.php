<?php

class Pagination
{

    const EXPANDED_LINKS_THRESHOLD = 6;
    private int $totalRows;
    private int $pageSize;
    private int $currentPage;

    public function __construct(int $totalRows, int $pageSize = 20)
    {
        $this->totalRows = $totalRows;
        $this->pageSize = $pageSize;
        $this->currentPage = $_GET['page_number'] ?? 1;
    }

    public function get_total_rows(): int
    {
        return $this->totalRows;
    }

    public function get_total_pages(): float
    {
        return ceil($this->totalRows / $this->pageSize);
    }

    public function get_sql(): string
    {
        $offset = ($this->currentPage - 1) * $this->pageSize;
        return " LIMIT $this->pageSize OFFSET $offset";
    }

    public function get_page_links(): string
    {
        $totalPages = $this->get_total_pages();
        $q = $_GET['q'] ?? "";
        $page = $_GET['page'] ?? "";
        if ($totalPages <= self::EXPANDED_LINKS_THRESHOLD) {
            $pageLinks = "<ul class='pagination'>";
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $this->currentPage) {
                    $pageLinks .= "<li class='page-item active'><a class='page-link' href='index.php?page=$page&page_number=$i&q=$q'>$i</a></li>";
                } else {
                    $pageLinks .= "<li class='page-item'><a class='page-link' href='index.php?page=$page&page_number=$i&q=$q'>$i</a></li>";
                }
            }
            $pageLinks .= "</ul>";
        } else {
            $pageLinks = "<select class='form-select' onchange='gotopage()' id='pagination-select-page'>";
            for ($i = 1; $i <= $totalPages; $i++) {
                $i == $this->currentPage ? $selected = "selected" : $selected = "";
                $pageLinks .= "<option value='$i' $selected>$i</option>";
            }
            $pageLinks .= "</select>";
            $pageLinks .= "<script>";
            $pageLinks .= "function gotopage() {";
            $pageLinks .= "window.location.href = 'index.php?page=$page&page_number=' + document.querySelector('#pagination-select-page').value + '&q=$q';";
            $pageLinks .= "}";
            $pageLinks .= "</script>";
        }

        return $pageLinks;
    }

    public static function build_search_query(string $q, array $columns): array
{
    if($q === "") return ['text' => "TRUE", 'params' => []];
    $res = "";
    for($i = 0; $i < count($columns); $i++) {
        $res .= $columns[$i] . " LIKE ?";
        if($i < count($columns) - 1) $res .= " OR ";
    }
    return ['text' => $res, 'params' => array_fill(0, count($columns), "%$q%")];
}


}