<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AiFeedbackResource;
use App\Models\AiFeedback;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AiFeedbackController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = $request->user()->aiFeedbacks()->latest()->paginate(20);

        return AiFeedbackResource::collection($items);
    }

    public function show(Request $request, AiFeedback $aiFeedback): AiFeedbackResource
    {
        abort_unless($aiFeedback->user_id === $request->user()->id, 404);

        return new AiFeedbackResource($aiFeedback);
    }
}
