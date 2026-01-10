<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['auth:api'])->group(function () {
    Route::resource('/api/cheque', 'ChequeController');
    Route::resource('/api/contractor', 'ContractorController');
    Route::resource('/api/organisation', 'OrganisationController');
    Route::resource('/api/expensetype', 'ExpenseTypeController');
    Route::resource('/api/nomenclature', 'NomenclatureController');

    Route::get('export', 'HomeController@export')->name('exports');

    Route::post('/api/cheque/{id}', 'ChequeController@update');
});
