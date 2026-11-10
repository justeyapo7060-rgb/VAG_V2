<?php

namespace App\Models;

use App\Enum\ApprenantStatut;
use App\Enum\PaiementStatut;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Apprenant extends Model
{
    use HasUuids, Notifiable;

    protected $primaryKey = 'id_apprenant';

    protected $keyType = 'string';

    protected $fillable = [
        'nom',
        'prenoms',
        'matricule',
        'sexe',
        'telephone',
        'date_naissance',
        'statut',
        'nationalite',
        'email',
        'vacation_id',
    ];


    protected $casts = [
        'date_naissance' => 'date',
        'statut' => ApprenantStatut::class,
    ];


    public function formations()
    {
        return $this->belongsToMany(
            Formation::class,
            'inscriptions',
            'id_apprenant',   
            'id_formation',   
            'id_apprenant',   
            'id_formation'   
        )->withTimestamps();
    }

    public function vacations(){
        return $this->belongsTo(Vacation::class,'vacation_id','id_vacation');
    }

    public function cohortes() {
        return $this->belongsTo(Cohorte::class,'cohorte_id','id_cohorte');
    }


    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'id_apprenant', 'id_apprenant');
    }

    /** Somme des prix de toutes les formations de l'apprenant. */
    public function getMontantTotalAttribute(): int
    {
        return (int) $this->formations()->get()->sum(fn ($f) => (int) $f->prix);
    }

    /** Somme des versements validés. */
    public function getTotalPayeAttribute(): int
    {
        return (int) $this->paiements()
            ->where('statut', PaiementStatut::VALIDE->value)
            ->sum('montant');
    }

    public function getResteAPayerAttribute(): int
    {
        return max(0, $this->montant_total - $this->total_paye);
    }

    public function getEstSoldeAttribute(): bool
    {
        return $this->montant_total > 0 && $this->reste_a_payer === 0;
    }

    public function getNombreVersementsAttribute(): int
    {
        return $this->paiements()
            ->where('statut', PaiementStatut::VALIDE->value)
            ->count();
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->date_limite !== null
            && $this->reste_a_payer > 0
            && $this->date_limite->copy()->endOfDay()->isPast();
    }

    /** Date limite de solde = fin de la cohorte (null tant que l'apprenant n'a pas de cohorte). */
    public function getDateLimiteAttribute(): ?Carbon
    {
        $fin = $this->cohortes?->date_fin;

        return $fin ? Carbon::parse($fin) : null;
    }
}
