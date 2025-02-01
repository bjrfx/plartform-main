<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationBody extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'notification_id',
        'body',
    ];

    /**
     * Get the notification that owns the body.
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
