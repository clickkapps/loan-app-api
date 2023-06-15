<?php

namespace App\Console\Commands;

use App\Models\Configuration;
use Illuminate\Console\Command;

class RevertHolidayToRegularDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:revert-holiday-to-regular-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle()
    {
        $config = Configuration::with([])->first();
        if(blank($config)) {
            throw new \Exception("Invalid config");
        }

        // get configurations
        $config->update([
            'today_is_holiday' => false
        ]);
    }
}
