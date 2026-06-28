<?php

namespace App\Models;

use App\Enums\NotificationEvent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['notification_id', 'event', 'metadata'])]
class NotificationLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'event'    => NotificationEvent::class,
            'metadata' => 'array',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
