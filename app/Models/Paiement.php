<?php

namespace App\Models;

use App\Enum\ModePaiement;
use App\Enum\PaiementStatut;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasUuids;

    protected $primaryKey = 'id_paiement';

    protected $keyType = 'string';

    protected $fillable = [
        'id_apprenant',
        'numero',
        'montant',
        'date_paiement',
        'mode_paiement',
        'reference',
        'reference_transaction',
        'statut',
    ];

    protected $casts = [
        'date_paiement' => 'datetime',
        'montant' => 'integer',
        'numero' => 'integer',
        'mode_paiement' => ModePaiement::class,
        'statut' => PaiementStatut::class,
    ];

    public function apprenant()
    {
        return $this->belongsTo(Apprenant::class, 'id_apprenant', 'id_apprenant');
    }
}
