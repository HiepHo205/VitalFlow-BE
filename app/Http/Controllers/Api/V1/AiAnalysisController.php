<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Contracts\AiAnalysisContract;
use App\Models\DailyLog;
use Illuminate\Http\Request;

class AiAnalysisController extends Controller
{
    public function analyzeLogs(Request $request, AiAnalysisContract $ai)
    {
        $user = $request->user();

        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $query = $user->dailyLogs()->latest();
        if ($start) {
            $query->whereDate('log_date', '>=', $start);
        }
        if ($end) {
            $query->whereDate('log_date', '<=', $end);
        }

        $logs = $query->get()->map(function (DailyLog $log) {
            return [
                'id' => $log->id,
                'log_date' => $log->log_date,
                'mood_score' => $log->mood_score,
                'energy_level' => $log->energy_level,
                'stress_level' => $log->stress_level,
                'sleep_hours' => $log->sleep_hours,
            ];
        })->toArray();

        $result = $ai->analyzeDailyLogs($user, $logs);

        return response()->json(['data' => $result]);
    }
}
