<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
        use HasUuids;

        protected $primaryKey = 'id_formation';

        protected $keyType = 'string';

        protected $fillable = [
        'code',
        'intitule',
        'description',
        'prix',
        'duree'
        ];


        public function apprenants()
        {
                return $this->belongsToMany(
                Apprenant::class,
                'inscriptions',
                'id_formation',
                'id_apprenant'
                )->withTimestamps();
        }
}
