<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AnimalPhotoController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\HealthProcedureController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\AdoptionController;
use App\Http\Controllers\StrayAnimalController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API v1 — Sistema Municipal de Registro de Mascotas y Control de Zoonosis
| Municipalidad Distrital de San Jerónimo
|--------------------------------------------------------------------------
|
| Convenciones:
|   - Toda la API vive bajo /api/v1  (versionado desde bootstrap/app.php)
|   - Rutas públicas  → sin middleware
|   - Rutas privadas  → auth:sanctum
|   - Control fino    → Policies (AuthServiceProvider)
|   - Control grueso  → role:X middleware de Spatie solo donde Policy
|                        no alcanza (acciones transversales de rol)
|
*/

// ══════════════════════════════════════════════════════════════════════════
// MÓDULO: AUTENTICACIÓN  (públicas)
// ══════════════════════════════════════════════════════════════════════════
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register',        [AuthController::class, 'register'])->name('register');
    Route::post('login',           [AuthController::class, 'login'])->name('login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('reset-password',  [AuthController::class, 'resetPassword'])->name('password.reset');

    // Requieren usuario autenticado
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me',      [AuthController::class, 'me'])->name('me');
    });
});

// ══════════════════════════════════════════════════════════════════════════
// MÓDULO: CATÁLOGOS  (lectura pública / escritura solo ADMIN)
// ══════════════════════════════════════════════════════════════════════════
Route::prefix('catalogs')->name('catalogs.')->group(function () {

    // ── Lectura pública ───────────────────────────────────────────────────
    Route::get('species',                      [CatalogController::class, 'speciesIndex'])->name('species.index');
    Route::get('species/{species}/breeds',     [CatalogController::class, 'breedsBySpecies'])->name('species.breeds');
    Route::get('campaign-types',               [CatalogController::class, 'campaignTypes'])->name('campaign-types.index');
    Route::get('procedure-types',              [CatalogController::class, 'procedureTypes'])->name('procedure-types.index');
    Route::get('post-types',                   [CatalogController::class, 'postTypes'])->name('post-types.index');
    Route::get('vaccines',                     [CatalogController::class, 'vaccineCatalog'])->name('vaccines.index');

    // ── Escritura: solo ADMIN ─────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
        Route::post('species',                 [CatalogController::class, 'speciesStore'])->name('species.store');
        Route::put('species/{species}',        [CatalogController::class, 'speciesUpdate'])->name('species.update');

        Route::post('breeds',                  [CatalogController::class, 'breedStore'])->name('breeds.store');
        Route::put('breeds/{breed}',           [CatalogController::class, 'breedUpdate'])->name('breeds.update');

        Route::post('campaign-types',          [CatalogController::class, 'campaignTypeStore'])->name('campaign-types.store');
        Route::post('procedure-types',         [CatalogController::class, 'procedureTypeStore'])->name('procedure-types.store');
        Route::post('vaccines',                [CatalogController::class, 'vaccineCatalogStore'])->name('vaccines.store');
    });
});

// ══════════════════════════════════════════════════════════════════════════
// MÓDULO: PUBLICACIONES  (lectura pública)
// ══════════════════════════════════════════════════════════════════════════
Route::prefix('posts')->name('posts.')->group(function () {
    Route::get('/',          [PostController::class, 'index'])->name('index');
    Route::get('{post}',     [PostController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/',                             [PostController::class, 'store'])->name('store');
        Route::put('{post}',                         [PostController::class, 'update'])->name('update');
        Route::delete('{post}',                      [PostController::class, 'destroy'])->name('destroy');

        // Acciones de estado — verbo PATCH + sustantivo de acción (RESTful)
        Route::patch('{post}/status',                [PostController::class, 'updateStatus'])->name('status');
        Route::patch('{post}/lost-notice/resolve',   [PostController::class, 'resolveLostNotice'])->name('lost-notice.resolve');

        // Comentarios — sub-recurso anidado
        Route::prefix('{post}/comments')->name('comments.')->group(function () {
            Route::get('/',                          [CommentController::class, 'index'])->name('index');
            Route::post('/',                         [CommentController::class, 'store'])->name('store');
            Route::put('{comment}',                  [CommentController::class, 'update'])->name('update');
            Route::delete('{comment}',               [CommentController::class, 'destroy'])->name('destroy');

            // Moderación: rol específico
            Route::patch('{comment}/moderate',       [CommentController::class, 'moderate'])
                ->name('moderate')
                ->middleware('role:ADMIN|COORDINATOR');
        });
    });
});

// ══════════════════════════════════════════════════════════════════════════
// MÓDULO: CAMPAÑAS  (lectura pública)
// ══════════════════════════════════════════════════════════════════════════
Route::prefix('campaigns')->name('campaigns.')->group(function () {
    Route::get('/',          [CampaignController::class, 'index'])->name('index');
    Route::get('{campaign}', [CampaignController::class, 'show'])->name('show');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/',                                    [CampaignController::class, 'store'])->name('store');
        Route::put('{campaign}',                            [CampaignController::class, 'update'])->name('update');
        Route::patch('{campaign}/status',                   [CampaignController::class, 'updateStatus'])->name('status');
        Route::post('{campaign}/participants',              [CampaignController::class, 'registerParticipant'])->name('participants.store');
        Route::patch('{campaign}/participants/attendance',  [CampaignController::class, 'markAttendance'])->name('participants.attendance');
    });
});

// ══════════════════════════════════════════════════════════════════════════
// MÓDULO: ADOPCIONES  (lectura pública)
// ══════════════════════════════════════════════════════════════════════════
Route::prefix('adoptions')->name('adoptions.')->group(function () {
    Route::get('/',           [AdoptionController::class, 'index'])->name('index');
    Route::get('{adoption}',  [AdoptionController::class, 'show'])->name('show');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/',                        [AdoptionController::class, 'store'])->name('store');
        Route::put('{adoption}',                [AdoptionController::class, 'update'])->name('update');
        Route::patch('{adoption}/status',       [AdoptionController::class, 'updateStatus'])->name('status');
    });
});

// ══════════════════════════════════════════════════════════════════════════
// RUTAS PRIVADAS  (auth:sanctum requerido en todo lo que sigue)
// ══════════════════════════════════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // ── MÓDULO: USUARIOS ─────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',                     [UserController::class, 'index'])->name('index');
            Route::post('/',                    [UserController::class, 'store'])->name('store');
            Route::get('{user}',                [UserController::class, 'show'])->name('show');
            Route::put('{user}',                [UserController::class, 'update'])->name('update');
            Route::delete('{user}',             [UserController::class, 'destroy'])->name('destroy');
            Route::patch('{user}/status',       [UserController::class, 'updateStatus'])->name('status');
            Route::patch('{user}/role',         [UserController::class, 'updateRole'])
                ->name('role');
        });

    // ── MÓDULO: ANIMALES ─────────────────────────────────────────────────
    Route::prefix('animals')->name('animals.')->group(function () {
        Route::get('/',                         [AnimalController::class, 'index'])->name('index');
        Route::post('/',                        [AnimalController::class, 'store'])->name('store');
        Route::get('{animal}',                  [AnimalController::class, 'show'])->name('show');
        Route::put('{animal}',                  [AnimalController::class, 'update'])->name('update');
        Route::delete('{animal}',               [AnimalController::class, 'destroy'])->name('destroy');
        Route::patch('{animal}/status',         [AnimalController::class, 'updateStatus'])->name('status');
        Route::get('{animal}/history',          [AnimalController::class, 'history'])->name('history');

        // Fotos — sub-recurso
        Route::prefix('{animal}/photos')->name('photos.')->group(function () {
            Route::post('/',                    [AnimalPhotoController::class, 'store'])->name('store');
            Route::patch('{photo}/cover',       [AnimalPhotoController::class, 'setCover'])->name('cover');
            Route::delete('{photo}',            [AnimalPhotoController::class, 'destroy'])->name('destroy');
        });

        // Vacunas — sub-recurso
        Route::apiResource('{animal}/vaccinations', VaccinationController::class)
            ->names([
                'index'   => 'vaccinations.index',
                'store'   => 'vaccinations.store',
                'show'    => 'vaccinations.show',
                'update'  => 'vaccinations.update',
                'destroy' => 'vaccinations.destroy',
            ]);

        // Procedimientos — sub-recurso
        Route::apiResource('{animal}/procedures', HealthProcedureController::class)
            ->parameters(['{animal}/procedures' => 'healthProcedure'])
            ->names([
                'index'   => 'procedures.index',
                'store'   => 'procedures.store',
                'show'    => 'procedures.show',
                'update'  => 'procedures.update',
                'destroy' => 'procedures.destroy',
            ]);
    });

    // Vacunas globales próximas a vencer (fuera del scope de un animal)
    Route::get('vaccinations/upcoming', [VaccinationController::class, 'upcoming'])
        ->name('vaccinations.upcoming')
        ->middleware('role:ADMIN|VETERINARIAN|COORDINATOR');

    // ── MÓDULO: ANIMALES CALLEJEROS ──────────────────────────────────────
    Route::middleware('role:ADMIN|VETERINARIAN|INSPECTOR|COORDINATOR')
        ->prefix('stray-animals')->name('stray-animals.')->group(function () {
            Route::get('/',                             [StrayAnimalController::class, 'index'])->name('index');
            Route::post('/',                            [StrayAnimalController::class, 'store'])->name('store');
            Route::get('{strayAnimal}',                 [StrayAnimalController::class, 'show'])->name('show');
            Route::put('{strayAnimal}',                 [StrayAnimalController::class, 'update'])->name('update');
            Route::delete('{strayAnimal}',              [StrayAnimalController::class, 'destroy'])->name('destroy');
            Route::patch('{strayAnimal}/status',        [StrayAnimalController::class, 'updateStatus'])->name('status');
            Route::post('{strayAnimal}/photos',         [StrayAnimalController::class, 'addPhotos'])->name('photos.store');
        });

    // ── MÓDULO: NOTIFICACIONES ───────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                             [NotificationController::class, 'index'])->name('index');
        Route::get('unread-count',                  [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::patch('mark-all-read',               [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::patch('{notification}/read',         [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::delete('{notification}',             [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ── MÓDULO: REPORTES ─────────────────────────────────────────────────
    Route::middleware('role:ADMIN|VETERINARIAN|INSPECTOR|COORDINATOR')
        ->prefix('reports')->name('reports.')->group(function () {
            Route::get('dashboard',              [ReportController::class, 'dashboard'])->name('dashboard');
            Route::get('animals/by-species',     [ReportController::class, 'animalsBySpecies'])->name('animals.by-species');
            Route::get('animals/per-month',      [ReportController::class, 'animalsPerMonth'])->name('animals.per-month');
            Route::get('campaigns/participation',[ReportController::class, 'campaignParticipation'])->name('campaigns.participation');
            Route::get('vaccinations/upcoming',  [ReportController::class, 'upcomingVaccinations'])->name('vaccinations.upcoming');
            Route::get('stray-animals/summary',  [ReportController::class, 'strayAnimalsSummary'])->name('stray-animals.summary');
        });
});