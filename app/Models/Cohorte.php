<?php

namespace App\Models;

use App\Enum\Cohortestatut as EnumCohortestatut;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Cohorte extends Model
{
    use HasUuids;
    protected $primaryKey = 'id_cohorte';

    protected $fillable = [
        'slug',
        'date_debut',
        'date_fin',
        'statut'
    ];

    public function apprenants() {
    return $this->hasMany(Apprenant::class, 'cohorte_id', 'id_cohorte');   
     }
    
     protected $casts = [
        'statut'=>EnumCohortestatut::class
     ];
        public function getVacationLibelleAttribute(): ?string
    {
        return $this->apprenants->first()?->vacations?->libelle;
    }

    public function getFormationsLibelleAttribute(): string
    {
        return $this->apprenants
            ->map(fn ($a) => $a->formations->pluck('intitule'))
            ->reduce(fn ($commun, $noms) => $commun === null ? $noms : $commun->intersect($noms))
            ?->implode(', ') ?? '';
    }
}
