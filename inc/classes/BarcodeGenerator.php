<?php
class BarcodeGenerator {
    public static function generateBarcode(string $data, int $height = 22, int $font = 5, bool $ssr = false): string {
        if($ssr) {
            return "<img src='https://barcodeapi.org/api/128/$data?height=$height&font=$font' alt='$data' />";
        }
        else {
            $height = $height * 100/22;
            return <<<EOD
                <svg class="barcode"
                jsbarcode-format="CODE128"
                jsbarcode-value="$data"
                jsbarcode-textmargin="0"
                jsbarcode-height="$height"
                "
                >
                </svg>
            EOD;
        }
    }
}

?>