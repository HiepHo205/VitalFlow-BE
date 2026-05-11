<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGoalRequest;
use App\Http\Requests\Api\V1\UpdateGoalRequest;
use App\Http\Resources\Api\V1\GoalResource;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GoalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $goals = $request->user()->goals()->latest()->paginate(20);

        return GoalResource::collection($goals);
    }

    public function store(StoreGoalRequest $request): GoalResource
    {
        $goal = $request->user()->goals()->create($request->validated());

        return new GoalResource($goal);
    }

    public function show(Request $request, Goal $goal): GoalResource
    {
        $this->authorize('view', $goal);

        return new GoalResource($goal);
    }

    public function update(UpdateGoalRequest $request, Goal $goal): GoalResource
    {
        $this->authorize('update', $goal);

        $goal->update($request->validated());

        return new GoalResource($goal->fresh());
    }

    public function destroy(Request $request, Goal $goal): Response
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return response()->noContent();
    }
}
