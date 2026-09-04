<?php

use App\Http\Controllers\Api\PayrollFeederApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Connected Feeder endpoints for external systems like PayFlow MY
|
*/

Route::prefix('v1/payroll')->group(function () {
    Route::get('/feeder', [PayrollFeederApiController::class, 'feed'])->name('api.payroll.feeder');
});
