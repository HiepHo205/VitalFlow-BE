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
        $query = $request->user()->routines()->with('items.completions')->latest();

        if ($request->filled('goal_id')) {
            $query->where('goal_id', $request->query('goal_id'));
        }

        $routines = $query->paginate(15);

        return RoutineResource::collection($routines);
    }

    public function store(StoreRoutineRequest $request): RoutineResource
    {
        $routine = DB::transaction(function () use ($request) {
            $toReplace = $request->input('replace_routine_ids', []);
            if (is_array($toReplace) && count($toReplace) > 0) {
                $request->user()->routines()->whereIn('id', $toReplace)->get()->each(function ($r) use ($request) {
                    if ($r->user_id === $request->user()->id) {
                        $r->delete();
                    }
                });
            }

            $routine = $request->user()->routines()->create([
                'goal_id' => $request->validated('goal_id'),
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

        $routine->load('items.completions');

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
        $prefs = $request->validated();

        $profile = $request->user()->healthProfile;
        $prefs['health_profile'] = $profile ? [
            'age' => $profile->age,
            'gender' => $profile->gender ?? null,
            'height_cm' => $profile->height_cm,
            'weight_kg' => $profile->weight_kg,
            'work_type' => $profile->work_type ?? null,
            'baseline_sleep_hours' => $profile->baseline_sleep_hours ?? null,
            'baseline_stress_level' => $profile->baseline_stress_level ?? null,
        ] : null;

        $goal = $request->user()->goals()->findOrFail($request->validated('goal_id'));
        $prefs['goal'] = [
            'id' => $goal->id,
            'goal_type' => $goal->goal_type,
            'target_value' => $goal->target_value,
            'current_value' => $goal->current_value,
            'status' => $goal->status,
            'start_date' => optional($goal->start_date)->toDateString(),
            'end_date' => optional($goal->end_date)->toDateString(),
        ];

        // include user's current routines so AI can consider them
        $existing = $request->user()->routines()->with('items')->get();
        $prefs['existing_routines'] = $existing->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'items' => $r->items->map(fn ($i) => [
                    'id' => $i->id,
                    'title' => $i->title,
                    'start_time' => $i->start_time ? (string) $i->start_time : null,
                    'end_time' => $i->end_time ? (string) $i->end_time : null,
                ])->toArray(),
            ];
        })->toArray();

        $draft = $ai->generateRoutineDraft($request->user(), $prefs);

        // detect overlaps between draft items and existing routine items
        $conflicts = [];

        $existingRanges = [];
        foreach ($existing as $r) {
            foreach ($r->items as $item) {
                if (! $item->start_time || ! $item->end_time) {
                    continue;
                }

                $existingRanges[] = [
                    'routine_id' => $r->id,
                    'routine_name' => $r->name,
                    'item_id' => $item->id,
                    'title' => $item->title,
                    'start' => strtotime($item->start_time),
                    'end' => strtotime($item->end_time),
                ];
            }
        }

        foreach ($draft['items'] ?? [] as $dIndex => $dItem) {
            $ds = isset($dItem['start_time']) ? strtotime($dItem['start_time']) : null;
            $de = isset($dItem['end_time']) ? strtotime($dItem['end_time']) : null;
            if (! $ds || ! $de) {
                continue;
            }

            foreach ($existingRanges as $er) {
                if ($ds < $er['end'] && $de > $er['start']) {
                    $conflicts[$er['routine_id']]['routine_id'] = $er['routine_id'];
                    $conflicts[$er['routine_id']]['routine_name'] = $er['routine_name'];
                    $conflicts[$er['routine_id']]['overlapping_items'][] = [
                        'existing_item_id' => $er['item_id'],
                        'existing_item_title' => $er['title'],
                        'draft_item_index' => $dIndex,
                        'draft_item_title' => $dItem['title'] ?? null,
                    ];
                }
            }
        }

        $conflicts = array_values($conflicts);

        return response()->json([
            'draft' => $draft,
            'conflicts' => $conflicts,
        ]);
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
