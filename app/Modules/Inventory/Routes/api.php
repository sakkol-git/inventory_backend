<?php

declare(strict_types=1);

use App\Modules\Inventory\Controllers\AchievementController;
use App\Modules\Inventory\Controllers\BorrowRecordController;
use App\Modules\Inventory\Controllers\ChemicalController;
use App\Modules\Inventory\Controllers\ChemicalUsageController;
use App\Modules\Inventory\Controllers\DashboardController;
use App\Modules\Inventory\Controllers\EquipmentController;
use App\Modules\Inventory\Controllers\PlantSampleController;
use App\Modules\Inventory\Controllers\PlantSpeciesController;
use App\Modules\Inventory\Controllers\PlantStockController;
use App\Modules\Inventory\Controllers\PlantVarietyController;
use App\Modules\Inventory\Controllers\TransactionController;
use App\Modules\Inventory\Controllers\UserDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {

    // ─── Dashboard ───────────────────────────────────────────────────────────
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // ─── Plant Module ────────────────────────────────────────────────────────
    Route::apiResource('plant-species', PlantSpeciesController::class)
        ->parameters(['plant-species' => 'plantSpecies']);

    Route::apiResource('plant-varieties', PlantVarietyController::class)
        ->parameters(['plant-varieties' => 'plantVariety']);

    Route::apiResource('plant-samples', PlantSampleController::class)
        ->parameters(['plant-samples' => 'plantSample']);

    Route::apiResource('plant-stocks', PlantStockController::class)
        ->parameters(['plant-stocks' => 'plantStock']);

    // ─── Chemical Module ─────────────────────────────────────────────────────
    Route::apiResource('chemicals', ChemicalController::class);
    Route::prefix('chemical-usage-logs')->name('chemical-usage-logs.')->group(function () {
        Route::post('use', [ChemicalUsageController::class, 'useChemical'])->name('use');
        Route::post('add', [ChemicalUsageController::class, 'addChemical'])->name('add');

        Route::apiResource('/', ChemicalUsageController::class)
            ->only(['index', 'show'])
            ->parameters(['' => 'chemicalUsageLog']);
    });

    // ─── Equipment Module ────────────────────────────────────────────────────
    Route::apiResource('equipment', EquipmentController::class);

    // ─── Borrow Module ───────────────────────────────────────────────────────
    Route::get('borrow-records/overdue', [BorrowRecordController::class, 'overdue'])
        ->name('borrow-records.overdue');
    Route::get('borrow-records/pending', [BorrowRecordController::class, 'pending'])
        ->name('borrow-records.pending');
    Route::post('borrow-records/{borrowRecord}/return', [BorrowRecordController::class, 'returnItem'])
        ->name('borrow-records.return');
    Route::post('borrow-records/{borrowRecord}/approve', [BorrowRecordController::class, 'approve'])
        ->name('borrow-records.approve');
    Route::post('borrow-records/{borrowRecord}/reject', [BorrowRecordController::class, 'reject'])
        ->name('borrow-records.reject');

    Route::apiResource('borrow-records', BorrowRecordController::class)
        ->only(['index', 'store', 'show']);

    //     // ─── Operations Module ───────────────────────────────────────────────────
    Route::apiResource('transactions', TransactionController::class)
        ->only(['index', 'show']);

    //     // ─── Achievements ────────────────────────────────────────────────────────
    Route::apiResource('achievements', AchievementController::class);
    Route::post('achievements/{achievement}/assign/{user}', [AchievementController::class, 'assign'])
        ->name('achievements.assign');
    Route::delete('achievements/{achievement}/revoke/{user}', [AchievementController::class, 'revoke'])
        ->name('achievements.revoke');

    // ─── User Documents ──────────────────────────────────────────────────────
    Route::get('user-documents/{userDocument}/download', [UserDocumentController::class, 'download'])
        ->name('user-documents.download');
    Route::apiResource('user-documents', UserDocumentController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy'])
        ->parameters(['user-documents' => 'userDocument']);

});
