<?php

namespace App\Enums;

enum ModePaiement: string
{
    case ESPECE = 'espece';
    case CARTE = 'carte';
    case MOBILE_MONEY = 'mobile_money';
    case VIREMENT = 'virement';

    public function label(): string
    {
        return match($this) {
            self::ESPECE => 'Espèces',
            self::CARTE => 'Carte bancaire',
            self::MOBILE_MONEY => 'Mobile Money',
            self::VIREMENT => 'Virement bancaire',
        };
    }
}