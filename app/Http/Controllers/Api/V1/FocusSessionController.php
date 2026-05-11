<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFocusSessionRequest;
use App\Http\Resources\Api\V1\FocusSessionResource;
use App\Models\FocusSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FocusSessionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $sessions = $request->user()->focusSessions()->orderByDesc('started_at')->paginate(30);

        return FocusSessionResource::collection($sessions);
    }

    public function store(StoreFocusSessionRequest $request): FocusSessionResource
    {
        $session = $request->user()->focusSessions()->create($request->validated());

        return new FocusSessionResource($session);
    }

    public function show(Request $request, FocusSession $focusSession): FocusSessionResource
    {
        abort_unless($focusSession->user_id === $request->user()->id, 404);

        return new FocusSessionResource($focusSession);
    }

    public function destroy(Request $request, FocusSession $focusSession): Response
    {
        abort_unless($focusSession->user_id === $request->user()->id, 404);

        $focusSession->delete();

        return response()->noContent();
    }
}
