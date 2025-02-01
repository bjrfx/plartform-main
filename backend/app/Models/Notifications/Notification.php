<?php

namespace App\Models\Notifications;

use App\Enums\Notifications\NotificationTypeEnums;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Notification extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'id',
        'merchant_id',
        'subject',
        'tested',
        'sent',
        'sent_count',
        'type',
        'enabled',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tested' => 'boolean',
            'sent' => 'boolean',
            'enabled' => 'boolean',
            'type' => NotificationTypeEnums::class,
        ];
    }

    /**
     * Get the body associated with the notification.
     */
    public function body(): HasOne
    {
        return $this->hasOne(NotificationBody::class);
    }
}
