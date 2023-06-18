<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\AgentTask;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SetupTasksForAgents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-tasks-for-agents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $agents = Agent::with([])->get();

        $dataToInsert = [];
        foreach ($agents as $agent) {

            $agentId = $agent->{'id'};
            $agentUserId = $agent->{'user_id'};

            // carry forward the tasks remaining from yesterday ----------

            $yesterdaysTaskRecord = AgentTask::with([])->whereDate('date', '=', Carbon::yesterday()->startOfDay())->first();

            Log::info('yesterdaysTaskRecord ----');
            Log::info(json_encode($yesterdaysTaskRecord));

            $tasksCountRemaining = !blank($yesterdaysTaskRecord) ? $yesterdaysTaskRecord->{'tasks_count'} - $yesterdaysTaskRecord->{'collected_count'} : 0.0;
            $tasksAmountRemaining = !blank($yesterdaysTaskRecord) ? $yesterdaysTaskRecord->{'tasks_amount'} - $yesterdaysTaskRecord->{'collected_amount'} : 0.0;

            $dataToInsert[] = [
                'user_id' => $agentUserId,
                'agent_id' => $agentId,
                'date' => Carbon::today(),
                'tasks_count' => $tasksCountRemaining,
                'collected_count' => 0,
                'tasks_amount' => $tasksAmountRemaining,
                'collected_amount' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];

        }

        AgentTask::with([])->insert($dataToInsert);
    }
}
