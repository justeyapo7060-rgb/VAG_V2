<?php

use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\CohorteController;
use App\Http\Controllers\FormationController;
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


Route::prefix('apprenants')->name('apprenant.')->group(function () {
        Route::get('/', [ApprenantController::class, 'index'])->name('index');
        Route::get('/create', [ApprenantController::class, 'create'])->name('create');  
        Route::get('/{apprenant}/edit', [ApprenantController::class, 'edit'])->name('edit');  
        Route::put('/archived/{apprenant}', [ApprenantController::class, 'archived'])->name('archived');              
        Route::put('/{apprenant}', [ApprenantController::class, 'update'])->name('update');                          
        Route::post('/', [ApprenantController::class, 'store'])->name('store');   
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