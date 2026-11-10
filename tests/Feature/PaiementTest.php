<?php

namespace Tests\Feature;

use App\Enum\ApprenantStatut;
use App\Enum\ModePaiement;
use App\Models\Apprenant;
use App\Models\Cohorte;
use App\Models\Formation;
use App\Models\Vacation;
use App\Notifications\RappelPaiement;
use App\Service\ApprenantService;
use App\Service\PaiementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaiementTest extends TestCase
{
    use RefreshDatabase;

    private Formation $cuisine;   // 450 000
    private Formation $patisserie; // 600 000
    private Vacation $vacation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cuisine = Formation::create(['code' => 'FOR-00001', 'intitule' => 'Cuisine', 'description' => 'd', 'prix' => ' 450000 ']);
        $this->patisserie = Formation::create(['code' => 'FOR-00002', 'intitule' => 'Pâtisserie', 'description' => 'd', 'prix' => '600000']);
        $this->vacation = Vacation::create(['libelle' => 'MATIN', 'heure_debut' => '08:00', 'heure_fin' => '12:00']);
    }

    private function inscrire(array $formations, int $acompte, string $email = 'a@test.ci'): Apprenant
    {
        return app(ApprenantService::class)->Inscription([
            'nom' => 'Kone', 'prenoms' => 'Awa', 'email' => $email, 'telephone' => '+2250700000000',
            'nationalite' => 'Ivoirienne', 'sexe' => 'F', 'date_naissance' => '1998-05-10',
            'formations' => array_map(fn ($f) => $f->id_formation, $formations),
            'acompte' => $acompte,
        ], $this->vacation->id_vacation);
    }

    public function test_inscription_cree_le_premier_versement_retranche_du_total(): void
    {
        $a = $this->inscrire([$this->cuisine, $this->patisserie], 100000);

        $this->assertSame(1050000, $a->montant_total);
        $this->assertSame(100000, $a->total_paye);
        $this->assertSame(950000, $a->reste_a_payer);
        $this->assertSame(1, $a->paiements()->count());
        $this->assertSame(1, $a->paiements()->first()->numero);
    }

    public function test_acompte_superieur_au_total_annule_linscription(): void
    {
        try {
            $this->inscrire([$this->cuisine], 500000);
            $this->fail('Une ValidationException était attendue.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('montant', $e->errors());
        }
        $this->assertSame(0, Apprenant::count());   // rollback complet
        $this->assertSame(0, DB::table('paiements')->count());
    }

    public function test_acompte_obligatoire(): void
    {
        $this->expectException(ValidationException::class);
        $this->inscrire([$this->cuisine], 0);
    }

    public function test_versements_libres_et_quatrieme_doit_solder(): void
    {
        $svc = app(PaiementService::class);
        $a = $this->inscrire([$this->cuisine], 50000);        // reste 400 000

        $svc->enregistrer($a, 123456, ModePaiement::MOBILE_MONEY); // n°2 : montant "aléatoire"
        $svc->enregistrer($a, 7001);                               // n°3
        $this->assertSame(450000 - 50000 - 123456 - 7001, $a->fresh()->reste_a_payer);

        // n°4 partiel => refusé
        try {
            $svc->enregistrer($a, 1000);
            $this->fail('Le 4e versement partiel doit être refusé.');
        } catch (ValidationException) {
            $this->assertSame(3, $a->fresh()->nombre_versements);
        }

        // n°4 = reste exact => soldé
        $svc->enregistrer($a, $a->fresh()->reste_a_payer);
        $a = $a->fresh();
        $this->assertTrue($a->est_solde);
        $this->assertSame(4, $a->nombre_versements);

        // plus rien à payer
        $this->expectException(ValidationException::class);
        $svc->enregistrer($a, 1);
    }

    public function test_montant_superieur_au_reste_refuse(): void
    {
        $a = $this->inscrire([$this->cuisine], 50000);
        $this->expectException(ValidationException::class);
        app(PaiementService::class)->enregistrer($a, 400001);
    }

    public function test_retirer_une_formation_sous_le_deja_paye_est_refuse(): void
    {
        $a = $this->inscrire([$this->cuisine, $this->patisserie], 700000);
        $this->expectException(ValidationException::class);
        app(ApprenantService::class)->updateApprenant(['formations' => [$this->cuisine->id_formation]], $a);
    }

    public function test_date_limite_est_la_fin_de_la_cohorte(): void
    {
        $a = $this->inscrire([$this->cuisine], 50000);
        $c = Cohorte::create(['slug' => 'c1', 'date_debut' => '2026-11-01', 'date_fin' => '2027-02-28', 'statut' => 'processing']);
        $a->cohorte_id = $c->id_cohorte;
        $a->save();

        $this->assertSame('2027-02-28', $a->fresh()->date_limite->toDateString());
    }

    public function test_rappel_fin_de_mois_cible_uniquement_les_non_soldes(): void
    {
        Notification::fake();
        $svc = app(PaiementService::class);

        $nonSolde = $this->inscrire([$this->cuisine], 50000, 'ns@test.ci');
        $solde = $this->inscrire([$this->cuisine], 450000, 'solde@test.ci');
        $archive = $this->inscrire([$this->cuisine], 50000, 'arch@test.ci');
        DB::table('apprenants')->where('id_apprenant', $archive->id_apprenant)->update(['statut' => ApprenantStatut::ARCHIVED->value]);

        $this->artisan('paiements:rappel')->expectsOutputToContain('1 rappel(s) envoyé(s).')->assertSuccessful();

        Notification::assertSentTo($nonSolde, RappelPaiement::class, fn ($n) => $n->reste === 400000);
        Notification::assertNotSentTo($solde, RappelPaiement::class);
        Notification::assertNotSentTo($archive, RappelPaiement::class);
    }

    public function test_rappel_est_planifie_le_dernier_jour_du_mois(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('paiements:rappel')->assertSuccessful();
    }

    public function test_pages_http_paiements(): void
    {
        $a = $this->inscrire([$this->cuisine], 50000);

        $this->get(route('apprenant.paiement.index', $a))->assertOk()->assertSee('400 000 FCFA');

        $this->post(route('apprenant.paiement.store', $a), ['montant' => 100000, 'mode_paiement' => 'espece'])
            ->assertRedirect(route('apprenant.paiement.index', $a));
        $this->assertSame(300000, $a->fresh()->reste_a_payer);

        $this->post(route('apprenant.paiement.store', $a), ['montant' => 999999, 'mode_paiement' => 'espece'])
            ->assertSessionHasErrors('montant');
    }

    private function jeuImpayes(): array
    {
        $passee = Cohorte::create(['slug' => 'passee', 'date_debut' => now()->subMonths(3), 'date_fin' => now()->subDays(2), 'statut' => 'processing']);
        $future = Cohorte::create(['slug' => 'future', 'date_debut' => now()->subMonth(), 'date_fin' => now()->addDays(20), 'statut' => 'processing']);

        $retard = $this->inscrire([$this->cuisine], 50000, 'retard@test.ci');     // reste 400 000
        $retard->cohorte_id = $passee->id_cohorte; $retard->save();
        $enCours = $this->inscrire([$this->cuisine], 150000, 'encours@test.ci');   // reste 300 000
        $enCours->cohorte_id = $future->id_cohorte; $enCours->save();
        $sansCohorte = $this->inscrire([$this->cuisine], 50000, 'sans@test.ci');   // reste 400 000
        $solde = $this->inscrire([$this->cuisine], 450000, 'solde@test.ci');

        return compact('passee', 'future', 'retard', 'enCours', 'sansCohorte', 'solde');
    }

    public function test_impayes_tri_resume_et_filtres(): void
    {
        $j = $this->jeuImpayes();
        $svc = app(PaiementService::class);

        $r = $svc->impayes();
        $this->assertSame(
            [$j['retard']->id_apprenant, $j['enCours']->id_apprenant, $j['sansCohorte']->id_apprenant],
            $r['lignes']->map(fn ($l) => $l->apprenant->id_apprenant)->all(),   // retard, puis échéance, puis sans cohorte
        );
        $this->assertSame(
            ['nb_non_soldes' => 3, 'reste_total' => 1100000, 'nb_en_retard' => 1, 'reste_en_retard' => 400000],
            $r['resume'],
        );

        $this->assertCount(1, $svc->impayes($j['future']->id_cohorte)['lignes']);
        $this->assertSame($j['retard']->id_apprenant, $svc->impayes(null, true)['lignes']->first()->apprenant->id_apprenant);
        $this->assertCount(1, $svc->impayes(null, true)['lignes']);
    }

    public function test_impayes_ignore_les_archives_et_versements_annules(): void
    {
        $j = $this->jeuImpayes();
        DB::table('apprenants')->where('id_apprenant', $j['sansCohorte']->id_apprenant)->update(['statut' => ApprenantStatut::ARCHIVED->value]);
        DB::table('paiements')->where('id_apprenant', $j['enCours']->id_apprenant)->update(['statut' => 'annule']);

        $r = app(PaiementService::class)->impayes();
        $this->assertCount(2, $r['lignes']);
        $this->assertSame(450000, $r['lignes']->firstWhere(fn ($l) => $l->apprenant->id_apprenant === $j['enCours']->id_apprenant)->reste);
    }

    public function test_page_impayes_http(): void
    {
        $j = $this->jeuImpayes();

        $this->get(route('paiement.impayes'))->assertOk()
            ->assertSee('Suivi des impayés')->assertSee('En retard')->assertSee('1 100 000 FCFA');

        $this->get(route('paiement.impayes', ['retard' => 1]))->assertOk()
            ->assertSee('400 000 FCFA')->assertDontSee('300 000 FCFA');

        $this->get(route('paiement.impayes', ['cohorte' => $j['future']->id_cohorte]))->assertOk()
            ->assertSee('300 000 FCFA');
    }
}
