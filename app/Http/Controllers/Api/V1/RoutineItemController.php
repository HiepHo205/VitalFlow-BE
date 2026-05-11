<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRoutineItemRequest;
use App\Http\Requests\Api\V1\UpdateRoutineItemRequest;
use App\Http\Resources\Api\V1\RoutineItemResource;
use App\Models\Routine;
use App\Models\RoutineItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoutineItemController extends Controller
{
    public function index(Request $request, Routine $routine): AnonymousResourceCollection
    {
        $this->authorize('view', $routine);

        $items = $routine->items()->orderBy('priority')->orderBy('start_time')->get();

        return RoutineItemResource::collection($items);
    }

    public function store(StoreRoutineItemRequest $request, Routine $routine): RoutineItemResource
    {
        $this->authorize('update', $routine);

        $item = $routine->items()->create($request->validated());

        return new RoutineItemResource($item);
    }

    public function show(Request $request, Routine $routine, RoutineItem $item): RoutineItemResource
    {
        $this->authorize('view', $routine);

        abort_unless($item->routine_id === $routine->id, 404);

        return new RoutineItemResource($item);
    }

    public function update(UpdateRoutineItemRequest $request, Routine $routine, RoutineItem $item): RoutineItemResource
    {
        $this->authorize('update', $routine);

        abort_unless($item->routine_id === $routine->id, 404);

        $item->update($request->validated());

        return new RoutineItemResource($item->fresh());
    }

    public function destroy(Request $request, Routine $routine, RoutineItem $item): Response
    {
        $this->authorize('update', $routine);

        abort_unless($item->routine_id === $routine->id, 404);

        $item->delete();

        return response()->noContent();
    }
}
