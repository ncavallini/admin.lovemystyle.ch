<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Money\Currencies\ISOCurrencies;

class MoneyUtils
{
    private static ?array $currencies = null;
    private static ?ISOCurrencies $isoCurrencies;

    public static function get_currencies(): array
    {
        if (self::$currencies === null) {
            $obj = new ISOCurrencies();
            self::$isoCurrencies = $obj;
            self::$currencies = [];
            foreach ($obj as $currency) {
                self::$currencies[] = $currency->getCode();
            }
        }
        return self::$currencies;
    }


    public static function options($selected = "")
    {
        $currencies = /*self::get_currencies()*/ ['CHF', 'EUR'];
        foreach ($currencies as $currency) {
            $selected_attr = $currency === $selected ? "selected" : "";
            echo "<option value='$currency' $selected_attr>$currency</option>";
        }
    }

    public static function format_price(Money\Money $price): string
    {
        self::get_currencies();
        $formatter = new Money\Formatter\DecimalMoneyFormatter(self::$isoCurrencies);
        return $formatter->format($price);
    }

    public static function format_price_int(?int $price, string $currency): string
    {
        self::get_currencies();
        if (empty($price))
            $price = 0;
        $money = new Money\Money($price, new Money\Currency($currency));
        $formatter = new Money\Formatter\DecimalMoneyFormatter(self::$isoCurrencies);
        return $formatter->format($money);
    }


    public static function multiply(int $price, int|float $quantity, string $currency): Money\Money
    {
        return (new Money\Money($price, new Money\Currency($currency)))->multiply(floatval($quantity));
    }

    public static function vat_part(Money\Money $price, int $vat): \Money\Money
    {
        return $price->divide(100)->multiply($vat);
    }

}