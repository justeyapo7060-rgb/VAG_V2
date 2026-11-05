<?php

namespace Database\Seeders;

use App\Models\Formation;
use App\Models\Vacation;
use App\Service\ApprenantService as ServiceApprenantService;
use Illuminate\Database\Seeder;
use Exception;

class ApprenantSeeder extends Seeder
{
    protected $apprenantService;

    public function __construct(ServiceApprenantService $apprenantService)
    {
        $this->apprenantService = $apprenantService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Préparation des données pour les inscriptions ciblées...');

        // 1. Récupération des deux formations spécifiques créées par votre FormationSeeder
        $formationCuisine = Formation::where('intitule', 'like', '%Cuisine Française%')->first();
        $formationPatisserie = Formation::where('intitule', 'like', '%Pâtisserie Fine%')->first();

        if (!$formationCuisine || !$formationPatisserie) {
            $this->command->error('Les formations cibles sont introuvables. Lancez d\'abord le FormationSeeder !');
            return;
        }

        // 2. Récupération ou création des trois vacations requises
        $vacationMatin = Vacation::firstOrCreate(
            ['libelle' => 'Matin'],
            ['heure_debut' => '08:00:00', 'heure_fin' => '12:00:00']
        );

        $vacationMidi = Vacation::firstOrCreate(
            ['libelle' => 'Après-midi'], 
            ['heure_debut' => '14:00:00', 'heure_fin' => '18:00:00']
        );

        $vacationSoir = Vacation::firstOrCreate(
            ['libelle' => 'soir'], 
            ['heure_debut' => '19:00:00', 'heure_fin' => '23:00:00']
        );

        // Banques de noms génériques pour alimenter la boucle de test
        $nomsFaux = ['Kouadio', 'Touré', 'Koné', 'Diomandé', 'Yao', 'Traoré', 'Diallo', 'Bamba', 'Ouattara', 'N\'guessan', 'Koffi', 'Sidibé', 'Cissé', 'Coulibaly', 'Zouzoua', 'Gnakpa'];
        $prenomsFaux = ['ahmed sylvain', 'marie-emmanuelle', 'ibrahim', 'fatoumata junior', 'christian', 'alima', 'cheick tidiane', 'grâce carole', 'ousseynou', 'jean-baptiste', 'awa laetitia', 'mohamed lamine', 'charlotte dany', 'arnaud', 'rokya', 'yves michel'];
        $nationalites = ['ivoirienne', 'Burkinabè', 'Malienne', 'Guinéenne', 'Sénégalaise'];

        // --- 1. GÉNÉRATION DES 14 APPRENANTS : MATIN + CUISINE FRANÇAISE ---
        $this->command->info('-> Inscription de 14 apprenants pour [Cuisine Française] (Vacation: MATIN)...');
        for ($i = 0; $i < 14; $i++) {
            $nomIndex = $i % count($nomsFaux);
            $prenomIndex = $i % count($prenomsFaux);

            $data = [
                'nom' => '  ' . $nomsFaux[$nomIndex] . ' ' . ($i + 1) . '  ', 
                'prenoms' => mb_strtolower($prenomsFaux[$prenomIndex]),
                'email' => strtolower($nomsFaux[$nomIndex]) . '_matin' . $i . '@odc-test.ci',
                'telephone' => '+22507' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'nationalite' => $nationalites[array_rand($nationalites)],
                'sexe' => ($i % 2 === 0) ? 'M' : 'F',
                'date_naissance' => '199' . rand(5, 9) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                'formations' => [$formationCuisine->id_formation] 
            ];

            try {
                $apprenant = $this->apprenantService->Inscription($data, $vacationMatin->id_vacation);
                $this->command->line("   [Matin] Inscrit : {$apprenant->nom} {$apprenant->prenoms} (Matricule: {$apprenant->matricule})");
            } catch (Exception $e) {
                $this->command->error("   Erreur d'inscription : " . $e->getMessage());
            }
        }

        // --- 2. GÉNÉRATION DES 10 APPRENANTS : APRÈS-MIDI + PÂTISSERIE FINE ---
        $this->command->info('-> Inscription de 10 apprenants pour [Pâtisserie Fine] (Vacation: APRÈS-MIDI)...');
        for ($i = 0; $i < 10; $i++) {
            $nomIndex = ($i + 2) % count($nomsFaux); // Décalage d'index pour varier les noms
            $prenomIndex = ($i + 2) % count($prenomsFaux);

            $data = [
                'nom' => mb_strtolower($nomsFaux[$nomIndex]) . ' ' . ($i + 1),
                'prenoms' => '  ' . $prenomsFaux[$prenomIndex], 
                'email' => strtolower($nomsFaux[$nomIndex]) . '_midi' . $i . '@odc-test.ci',
                'telephone' => '+22505' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'nationalite' => $nationalites[array_rand($nationalites)],
                'sexe' => ($i % 2 === 0) ? 'M' : 'F',
                'date_naissance' => '200' . rand(0, 5) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                'formations' => [$formationPatisserie->id_formation] 
            ];

            try {
                $apprenant = $this->apprenantService->Inscription($data, $vacationMidi->id_vacation);
                $this->command->line("   [Après-midi] Inscrit : {$apprenant->nom} {$apprenant->prenoms} (Matricule: {$apprenant->matricule})");
            } catch (Exception $e) {
                $this->command->error("   Erreur d'inscription : " . $e->getMessage());
            }
        }

        // --- 3. GÉNÉRATION DES 10 APPRENANTS : SOIR + PÂTISSERIE FINE ---
        $this->command->info('-> Inscription de 10 apprenants pour [Pâtisserie Fine] (Vacation: SOIR)...');
        for ($i = 0; $i < 10; $i++) {
            $nomIndex = ($i + 5) % count($nomsFaux);
            $prenomIndex = ($i + 5) % count($prenomsFaux);

            $data = [
                'nom' => mb_strtolower($nomsFaux[$nomIndex]) . ' ' . ($i + 1),
                'prenoms' => '  ' . $prenomsFaux[$prenomIndex], 
                'email' => strtolower($nomsFaux[$nomIndex]) . '_soir' . $i . '@odc-test.ci',
                'telephone' => '+22501' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'nationalite' => $nationalites[array_rand($nationalites)],
                'sexe' => ($i % 2 === 0) ? 'F' : 'M',
                'date_naissance' => '200' . rand(0, 4) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                'formations' => [$formationPatisserie->id_formation] 
            ];

            try {
                $apprenant = $this->apprenantService->Inscription($data, $vacationSoir->id_vacation);
                $this->command->line("   [Soir] Inscrit : {$apprenant->nom} {$apprenant->prenoms} (Matricule: {$apprenant->matricule})");
            } catch (Exception $e) {
                $this->command->error("   Erreur d'inscription : " . $e->getMessage());
            }
        }

        $this->command->info('Seeding des apprenants terminé avec succès !');
    }
}
