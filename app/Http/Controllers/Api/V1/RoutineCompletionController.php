<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRoutineCompletionRequest;
use App\Http\Resources\Api\V1\RoutineCompletionResource;
use App\Models\RoutineCompletion;
use App\Models\RoutineItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoutineCompletionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $rows = $request->user()->routineCompletions()->with('routineItem')->latest()->paginate(40);

        return RoutineCompletionResource::collection($rows);
    }

    public function store(StoreRoutineCompletionRequest $request): RoutineCompletionResource
    {
        $validated = $request->validated();

        $item = RoutineItem::query()->findOrFail($validated['routine_item_id']);
        $this->authorize('view', $item->routine);

        $completion = $request->user()->routineCompletions()->create($validated);

        $completion->load('routineItem.routine.goal');

        if ($completion->routineItem && $completion->routineItem->routine && $completion->routineItem->routine->goal) {
            $completion->routineItem->routine->goal->recordProgress();
        }

        return new RoutineCompletionResource($completion->load('routineItem'));
    }

    public function show(Request $request, RoutineCompletion $routineCompletion): RoutineCompletionResource
    {
        abort_unless($routineCompletion->user_id === $request->user()->id, 404);

        return new RoutineCompletionResource($routineCompletion->load('routineItem'));
    }

    public function destroy(Request $request, RoutineCompletion $routineCompletion): Response
    {
        abort_unless($routineCompletion->user_id === $request->user()->id, 404);

        $routineCompletion->delete();

        return response()->noContent();
    }
}
