<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Formation;
use App\Service\FormationService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FormationSeeder extends Seeder
{
    protected $formationService;

    // Injection du service contenant votre méthode createNewFormation
    public function __construct(FormationService $formationService)
    {
        $this->formationService = $formationService;
    }

    public function run(): void
    {
        // 1. Désactiver les contraintes pour pouvoir nettoyer les tables liées
        Schema::disableForeignKeyConstraints();

        // 2. Vider les tables dans le bon ordre (les tables enfants d'abord pour éviter les résidus)
        DB::table('inscriptions')->truncate();
        DB::table('apprenants')->truncate(); // Recommandé d'ajouter cette table ici pour éviter les ID orphelins
        DB::table('formations')->truncate();

        // 3. Réactiver la vérification des clés étrangères
        Schema::enableForeignKeyConstraints();

        // Liste de formations culinaires de test
        $formations = [
            [
                'intitule' => '  les bases de la cuisine française et sauces mères  ', // Espaces au début et à la fin pour tester le trim
                'description' => 'Maîtriser les techniques de découpe, les fonds de sauce et les cuissons classiques.',
                'prix' => ' 450000 ', // Espaces à tester avec trim
            ],
            [
                'intitule' => 'pâtisserie fine : entremets et décors modernes', // En minuscules pour tester le mb_convert_case (Title Case)
                'description' => 'Apprendre l’art du glaçage miroir, des mousses légères et du travail du chocolat.',
                'prix' => '600000',
            ],
        ];

        $this->command->info('Début du seeding des formations de cuisine...');

        foreach ($formations as $formationData) {
            try {
                // Exécution de votre méthode
                $formation = $this->formationService->createNewFormation($formationData);
                
                $this->command->info("Succès : Formation de cuisine [{$formation->intitule}] créée avec le code [{$formation->code}].");
            } catch (Exception $e) {
                $this->command->error("Échec pour [{$formationData['intitule']}] : " . $e->getMessage());
            }
        }
    }
}
