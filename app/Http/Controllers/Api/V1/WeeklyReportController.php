<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWeeklyReportRequest;
use App\Http\Resources\Api\V1\WeeklyReportResource;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class WeeklyReportController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $reports = $request->user()->weeklyReports()->orderByDesc('week_start')->paginate(12);

        return WeeklyReportResource::collection($reports);
    }

    public function store(StoreWeeklyReportRequest $request): WeeklyReportResource
    {
        $weekStart = Carbon::parse($request->validated('week_start'))->startOfDay();
        $weekEnd = Carbon::parse($request->validated('week_end'))->endOfDay();

        $user = $request->user();

        $dailyLogs = $user->dailyLogs()
            ->whereBetween('log_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $routineCompletions = $user->routineCompletions()
            ->whereBetween('completed_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->with('routineItem.routine.goal')
            ->get();

        $avgMood = $dailyLogs->whereNotNull('mood_score')->avg('mood_score');
        $avgEnergy = $dailyLogs->whereNotNull('energy_level')->avg('energy_level');
        $avgSleep = $dailyLogs->whereNotNull('sleep_hours')->avg('sleep_hours');
        $avgStress = $dailyLogs->whereNotNull('stress_level')->avg('stress_level');

        $completedRoutines = $routineCompletions->count();
        $completedRoutineGoals = $routineCompletions->filter(fn ($completion) => $completion->routineItem && $completion->routineItem->routine && $completion->routineItem->routine->goal_id !== null
        )->count();

        $goalCount = $user->goals()->count();
        $completedGoalCount = $user->goals()->where('status', 'completed')->count();

        $burnoutRiskScore = null;
        if ($dailyLogs->isNotEmpty()) {
            $wellness = 0;
            $wellness += ($avgMood !== null ? ($avgMood / 10) * 35 : 17.5);
            $wellness += ($avgEnergy !== null ? ($avgEnergy / 10) * 35 : 17.5);
            $wellness += ($avgSleep !== null ? min($avgSleep, 9) / 9 * 20 : 10);
            $wellness += ($avgStress !== null ? ((10 - $avgStress) / 10) * 10 : 5);

            $burnoutRiskScore = max(0, min(100, 100 - $wellness));
            $burnoutRiskScore = round($burnoutRiskScore, 2);
        }

        $summaryLines = [];
        $summaryLines[] = "Tuần từ {$weekStart->toDateString()} đến {$weekEnd->toDateString()}";

        if ($dailyLogs->isNotEmpty()) {
            $summaryLines[] = sprintf(
                'Điểm tâm trạng trung bình %.2f, năng lượng %.2f, giấc ngủ %.2f giờ.',
                $avgMood ?? 0,
                $avgEnergy ?? 0,
                $avgSleep ?? 0,
            );
        }

        if ($completedRoutines > 0) {
            $summaryLines[] = "Hoàn thành {$completedRoutines} item routine trong tuần.";
            if ($completedRoutineGoals > 0) {
                $summaryLines[] = "Trong đó {$completedRoutineGoals} item liên quan đến mục tiêu.";
            }
        } else {
            $summaryLines[] = 'Chưa có item routine nào được hoàn thành tuần này.';
        }

        if ($goalCount > 0) {
            $summaryLines[] = "Bạn đang theo dõi {$goalCount} mục tiêu, đã hoàn thành {$completedGoalCount}.";
        }

        if ($burnoutRiskScore !== null) {
            $summaryLines[] = "Mức độ burnout ước tính: {$burnoutRiskScore}/100.";
        }

        $summary = implode(' ', $summaryLines);

        $report = $user->weeklyReports()->updateOrCreate(
            [
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
            ],
            [
                'summary' => $summary,
                'avg_mood' => $avgMood,
                'avg_energy' => $avgEnergy,
                'avg_sleep' => $avgSleep,
                'burnout_risk_score' => $burnoutRiskScore,
            ]
        );

        return new WeeklyReportResource($report);
    }

    public function show(Request $request, WeeklyReport $weeklyReport): WeeklyReportResource
    {
        abort_unless($weeklyReport->user_id === $request->user()->id, 404);

        return new WeeklyReportResource($weeklyReport);
    }

    public function destroy(Request $request, WeeklyReport $weeklyReport): Response
    {
        abort_unless($weeklyReport->user_id === $request->user()->id, 404);

        $weeklyReport->delete();

        return response()->noContent();
    }
}
