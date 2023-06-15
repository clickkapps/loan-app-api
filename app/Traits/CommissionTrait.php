<?php

namespace App\Traits;

trait CommissionTrait
{
    public  function creditAgent($userId, $amount) {
        // get the loan stage, and
        // check if today is holday and find the percentage given on holidays for that loan stage
        // else check if today is weekday and find percentage for that loan stage
        // else check if today is weekend and find percentage for that loan stage

    }

    // userid is the agent userit
    public  function debitAgent($userId, $amount) {

    }
}
