<?php

namespace App\Enums;

enum CommunicationChannel: string
{
    case WHATSAPP = 'whatsapp';
    case SMS = 'sms';
    case EMAIL = 'email';

    public function getLabel(): string
    {
        return match($this) {
            self::WHATSAPP => 'WhatsApp',
            self::SMS => 'SMS',
            self::EMAIL => 'Email',
        };
    }
}
