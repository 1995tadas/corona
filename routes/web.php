<?php

use App\Http\Controllers\CoronaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::name('corona.')->group(function () {
    Route::get('/', [CoronaController::class, 'index'])->name('index');
    Route::get('cases/{slug}/{provinceId?}', [CoronaController::class, 'cases'])
        ->where(['slug' => '[a-z-]+', 'provinceId' => '[0-9]+'])->name('cases');
    Route::get('/{slug}', [CoronaController::class, 'show'])
        ->where('slug', '[a-z-]+')->name('show');
});
