<?php

namespace App\Models;

use App\Enum\Apprenantstatut;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Apprenant extends Model
{
    use HasUuids;

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
        'vacation_id'
    ];


    protected $casts = [
        'date_naissance' => 'date',
        'statut' => Apprenantstatut::class,
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


}
