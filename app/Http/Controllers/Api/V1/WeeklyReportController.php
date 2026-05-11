<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWeeklyReportRequest;
use App\Http\Resources\Api\V1\WeeklyReportResource;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WeeklyReportController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $reports = $request->user()->weeklyReports()->orderByDesc('week_start')->paginate(12);

        return WeeklyReportResource::collection($reports);
    }

    public function store(StoreWeeklyReportRequest $request): WeeklyReportResource
    {
        $report = $request->user()->weeklyReports()->create($request->validated());

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
