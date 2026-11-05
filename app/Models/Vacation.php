<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Cast;

class Vacation extends Model
{
    protected  $primaryKey = 'id_vacation';

    use HasUuids;
    protected $fillable = [
        'libelle',
        'heure_debut',
        'heure_fin'
    ];

    public function apprenants(){
        return $this->hasMany(Apprenant::class,'vacation_id','id_vacation');
    }

    protected $casts = [
       'heure_debut' => 'datetime:H:i',
       'heure_fin' => 'datetime:H:i',
    ];
}
