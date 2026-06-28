<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'channel', 'payload', 'status', 'attempts', 'sent_at', 'failed_at'])]
class Notification extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'channel'   => NotificationChannel::class,
            'status'    => NotificationStatus::class,
            'payload'   => 'array',
            'sent_at'   => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }
}
