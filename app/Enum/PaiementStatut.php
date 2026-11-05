<?php

namespace App\Enum;

enum PaiementStatut:string
{
    case VALIDE = 'valide';
    case ANNULE = 'annule';
    case REFUSE = 'refuse';

    public function label(): string
    {
        return match($this) {
            self::VALIDE => 'Validé',
            self::ANNULE => 'Annulé',
            self::REFUSE => 'Refusé',
        };
    }

}
