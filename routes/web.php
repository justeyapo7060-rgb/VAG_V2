<?php

use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\CohorteController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\PaiementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('formations')->name('formation.')->group(function () {
    Route::get('/', [FormationController::class, 'index'])->name('index');
    Route::get('/create', [FormationController::class, 'create'])->name('create');  
    Route::get('/edit', [FormationController::class, 'edit'])->name('edit');                          
    Route::put('/{formation}', [FormationController::class, 'update'])->name('update');    
    Route::post('/', [FormationController::class, 'store'])->name('store');   
    Route::get('/apprenants/{id}',[FormationController::class, 'show'])->name('show'); 
});


Route::get('/paiements/impayes', [PaiementController::class, 'impayes'])->name('paiement.impayes');
Route::get('/paiements/{paiement}/invoice', [PaiementController::class, 'invoice'])->name('paiement.invoice');


Route::prefix('apprenants')->name('apprenant.')->group(function () {
        Route::get('/', [ApprenantController::class, 'index'])->name('index');
        Route::get('/create', [ApprenantController::class, 'create'])->name('create');  
        Route::get('/{apprenant}/edit', [ApprenantController::class, 'edit'])->name('edit');  
        Route::put('/archived/{apprenant}', [ApprenantController::class, 'archived'])->name('archived');              
        Route::put('/{apprenant}', [ApprenantController::class, 'update'])->name('update');                          
        Route::post('/', [ApprenantController::class, 'store'])->name('store');   

        Route::get('/{apprenant}/paiements', [PaiementController::class, 'index'])->name('paiement.index');
        Route::post('/{apprenant}/paiements', [PaiementController::class, 'store'])->name('paiement.store');
    });
Route::resource('cohortes', CohorteController::class)->names('cohorte');
Route::get(
    '/cohortes/{cohorte}/apprenants/ajouter',
    [CohorteController::class, 'createApprenants']
)->name('cohorte.apprenant.create');

Route::post(
    '/cohortes/{cohorte}/apprenants',
    [CohorteController::class, 'storeApprenants']
)->name('cohorte.apprenant.store');