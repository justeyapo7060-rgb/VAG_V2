<?php

namespace App\Enum;

enum CohorteStatut:string
{
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';

      public function label(): string
    {
        return match($this) {
            self::PROCESSING => 'En_cours',
            self::COMPLETED => 'Terminée',
        };
    }
}
