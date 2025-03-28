<?php
interface DYMOLabel {
    public function get_xml(): string;
    public function preview(): string;
    public function print(string $printerName, int $copies = 1): void;
    public function download(): void;
}
?>