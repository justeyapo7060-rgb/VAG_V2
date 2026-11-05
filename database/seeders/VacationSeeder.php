<?php

namespace Database\Seeders;

use App\Models\Vacation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VacationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $vacations = [
            [
                'libelle'     => 'Matin',
                'heure_debut' => '08:00:00',
                'heure_fin'   => '12:00:00',
            ],
            [
                'libelle'     => 'Après-midi',
                'heure_debut' => '13:00:00',
                'heure_fin'   => '17:00:00',
            ],
            [
                'libelle'     => 'Soir',
                'heure_debut' => '18:00:00',
                'heure_fin'   => '22:00:00',
            ],
        ];

        foreach($vacations as $vacation) {
            Vacation::create($vacation);
        }

        $this->command->info('vacation cree avec succès');
    }
}
