<?php

namespace App\Models;

use App\Enum\PaiementStatut;
use App\Enums\ModePaiement;
use Illuminate\Database\Eloquent\Model;
use App\Enums\StatutEnum;

class Paiement extends Model
{
    protected $primaryKey = 'id_paiement';

    protected $fillable = [
        'inscription_id',
        'montant',
        'date_paiement',
        'mode_paiement',
        'reference',
        'reference_transaction',
        'statut',
    ];

    protected $casts = [
        'date_paiement' => 'datetime',
        'montant' => 'decimal:2',
        'mode_paiement' => ModePaiement::class,
        'statut' => PaiementStatut::class,
    ];
}