<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\InAppNotificationResource;
use App\Models\InAppNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InAppNotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()->inAppNotifications()->orderByDesc('created_at')->paginate(25);

        return InAppNotificationResource::collection($notifications);
    }

    public function markRead(Request $request, string $id): InAppNotificationResource
    {
        $notification = InAppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($id)
            ->firstOrFail();

        $notification->is_read = true;
        $notification->save();

        return new InAppNotificationResource($notification->fresh());
    }
}
