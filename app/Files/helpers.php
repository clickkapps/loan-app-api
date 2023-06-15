<?php

use App\Models\Configuration;
use Carbon\Carbon;

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

function toNDecimalPlaces($number, $dp = 2): string
{
    return number_format((float)$number, $dp, '.', '');
}


function getTodayDescription(): string
{

    $generalConfig = Configuration::with([])->first();
    $isHoliday = $generalConfig->{'today_is_holiday'};
    if($isHoliday){
        $todayIs = 'holiday';
    }else{
        $todayIs = Carbon::today()->isWeekend() ? 'weekend' : 'weekday';

    }
    return $todayIs;
}
