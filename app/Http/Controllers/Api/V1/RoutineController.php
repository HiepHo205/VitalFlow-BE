<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\AiAnalysisContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GenerateRoutineRequest;
use App\Http\Requests\Api\V1\StoreRoutineRequest;
use App\Http\Requests\Api\V1\UpdateRoutineRequest;
use App\Http\Resources\Api\V1\AiFeedbackResource;
use App\Http\Resources\Api\V1\RoutineResource;
use App\Models\AiFeedback;
use App\Models\Routine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RoutineController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $routines = $request->user()->routines()->with('items')->latest()->paginate(15);

        return RoutineResource::collection($routines);
    }

    public function store(StoreRoutineRequest $request): RoutineResource
    {
        $routine = DB::transaction(function () use ($request) {
            $routine = $request->user()->routines()->create([
                'name' => $request->validated('name'),
                'description' => $request->validated('description'),
                'is_ai_generated' => $request->boolean('is_ai_generated'),
            ]);

            foreach ($request->input('items', []) as $item) {
                $routine->items()->create($item);
            }

            return $routine->load('items');
        });

        return new RoutineResource($routine);
    }

    public function show(Request $request, Routine $routine): RoutineResource
    {
        $this->authorize('view', $routine);

        $routine->load('items');

        return new RoutineResource($routine);
    }

    public function update(UpdateRoutineRequest $request, Routine $routine): RoutineResource
    {
        $this->authorize('update', $routine);

        $routine->update($request->validated());

        return new RoutineResource($routine->fresh()->load('items'));
    }

    public function destroy(Request $request, Routine $routine): Response
    {
        $this->authorize('delete', $routine);

        $routine->delete();

        return response()->noContent();
    }

    public function generate(GenerateRoutineRequest $request, AiAnalysisContract $ai): JsonResponse
    {
        $draft = $ai->generateRoutineDraft($request->user(), $request->validated());

        return response()->json($draft);
    }

    public function analyze(Request $request, Routine $routine, AiAnalysisContract $ai): AiFeedbackResource
    {
        $this->authorize('view', $routine);

        $ai->analyzeRoutine($request->user(), $routine);

        $feedback = AiFeedback::query()
            ->where('user_id', $request->user()->id)
            ->where('related_routine_id', $routine->id)
            ->latest()
            ->firstOrFail();

        return new AiFeedbackResource($feedback);
    }
}
