<?php

namespace App\Enums;

enum NotificationEvent: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';
    case Retried = 'retried';
}
