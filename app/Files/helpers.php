<?php

function generateRandomNumber($digits = 6): string
{
    return str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
}


function toCurrencyFormat($value, bool $showCurrency = true): string
{
    $value = blank($value) || !is_numeric($value) ? '0.00' : $value;
    if($showCurrency){
        return sprintf(config('custom.currency').' %01.2f', $value);
    }
    else {
        return sprintf(' %01.2f', $value);
    }

}
