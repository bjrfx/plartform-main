<?php

namespace App\Http\Controllers\Notifications;

use App\Enums\Notifications\NotificationTypeEnums;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\NotificationRequest;
use App\Http\Resources\Notifications\NotificationResource;
use App\Models\Notifications\Notification;
use App\Services\Notifications\NotificationService;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    protected ?string $id = null;

    public function __construct(
        protected NotificationService $service
    )
    {
        // Get the merchantId from the request (injected by middleware)
        $this->id = request()->input('merchant_id');
    }

    public function editBillingsDefaults(): NotificationResource
    {
        $notification = $this->service->getDefault();

        return NotificationResource::make($notification);
    }

    public function saveBillingsDefaults(NotificationRequest $request): NotificationResource
    {
        $notification = $this->service->saveDefault($request->validated());

        return NotificationResource::make($notification);
    }
}
