<?php

namespace App\Services\Notifications;

use App\Enums\Notifications\NotificationTypeEnums;
use App\Models\Notifications\Notification;

class NotificationService
{
    public function getDefault(): ?Notification
    {
        /** @var Notification $notification */
        $notification = Notification::query()
            ->with('body')
            ->whereNull('merchant_id')
            ->first();

        return $notification;
    }

    public function saveDefault(array $requestData): Notification
    {
        $notification = $this->getDefault();
        $save = [
            'merchant_id' => null,
            'subject' => '',
            'type' => NotificationTypeEnums::BILLING,
        ];
        if (is_null($notification)) {
            /** @var Notification $notification */
            $notification = Notification::query()
                ->create($save);
            $notification->body()->create([
                'body' => $requestData['body'],
            ]);
        } else {
            $notification->update($save);
            $notification->getRelationValue('body')->update([
                'body' => $requestData['body'],
            ]);
        }
        return $notification;
    }


}
