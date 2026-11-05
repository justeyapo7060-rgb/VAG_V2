<?php

namespace App\Enum;

enum Apprenantstatut : string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case PROCESSING = 'processing';
    case ARCHIVED = 'archived';


    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En_attente',
            self::PROCESSING => 'En_cours',
            self::COMPLETED => 'Terminée',
            self::ARCHIVED => 'archivée',
        };
    }
}
