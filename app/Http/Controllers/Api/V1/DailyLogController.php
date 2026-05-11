<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\AiAnalysisContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDailyLogRequest;
use App\Http\Requests\Api\V1\UpdateDailyLogRequest;
use App\Http\Resources\Api\V1\DailyLogResource;
use App\Models\DailyLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DailyLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->dailyLogs()->orderByDesc('log_date');

        if ($request->filled('from')) {
            $query->whereDate('log_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('log_date', '<=', $request->query('to'));
        }

        return DailyLogResource::collection($query->paginate(31));
    }

    public function store(StoreDailyLogRequest $request, AiAnalysisContract $ai): DailyLogResource
    {
        $log = $request->user()->dailyLogs()->create($request->validated());

        $settings = $request->user()->settings;
        if ($settings !== null && $settings->ai_auto_analysis) {
            $ai->analyzeDailyLog($request->user(), $log);
        }

        return new DailyLogResource($log->fresh());
    }

    public function show(Request $request, DailyLog $dailyLog): DailyLogResource
    {
        $this->authorize('view', $dailyLog);

        return new DailyLogResource($dailyLog);
    }

    public function update(UpdateDailyLogRequest $request, DailyLog $dailyLog, AiAnalysisContract $ai): DailyLogResource
    {
        $this->authorize('update', $dailyLog);

        $dailyLog->update($request->validated());

        $settings = $request->user()->settings;
        if ($settings !== null && $settings->ai_auto_analysis) {
            $ai->analyzeDailyLog($request->user(), $dailyLog->fresh());
        }

        return new DailyLogResource($dailyLog->fresh());
    }

    public function destroy(Request $request, DailyLog $dailyLog): Response
    {
        $this->authorize('delete', $dailyLog);

        $dailyLog->delete();

        return response()->noContent();
    }
}
