<?php

namespace App\Enums;

enum CommunicationStatus: string
{
    case QUEUED = 'queued';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';

    public function getLabel(): string
    {
        return match($this) {
            self::QUEUED => 'Queued',
            self::SENT => 'Sent',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::QUEUED => 'yellow',
            self::SENT => 'blue',
            self::DELIVERED => 'green',
            self::FAILED => 'red',
        };
    }
}
