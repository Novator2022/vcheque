<?php

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

Auth::routes(['verify' => true]);

Route::any('/notify/incoming/yandex_money', 'BalanceController@notify_yandex_money')->name('notify_yandex_money');

Route::get('/printer/{key}/queue', 'QueueController@get_queue');
Route::get('/printer/{key}/pdf/{cheque_id}', 'QueueController@pdf');
Route::post('/printer/{key}/complete/{cheque_id}', 'QueueController@complete');
Route::get('/printer/{key}/complete/{cheque_id}', 'QueueController@complete');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('download/schedules/{id}', 'HomeController@download_schedules')->name('download_schedules');

    Route::get('export', 'HomeController@export')->name('exports');

    Route::get('/test', 'TestController@test')->name('test');

    Route::get('/', 'HomeController@login')->name('index');
    Route::get('/home/tariff/frame', 'HomeController@tariff')->name('tariff');
    Route::post('/home/tariff/frame', 'HomeController@tariff_save')->name('tariff_save');

    Route::get('/home/balance/frame', 'BalanceController@index')->name('balance');
    Route::post('/home/balance/{method}', 'BalanceController@by_method')->name('by_method');

    Route::get('/home/{page?}', 'HomeController@index')->name('home');
    Route::resource('/user', 'UserController');
    Route::resource('/cheque', 'ChequeController');
    Route::any('/cheque/stats', 'ChequeController@stats');
    Route::get('/cheque/view/{id}', 'ChequeController@view')->name('view_cheque');
    Route::get('/cheque/view_pdf/{id}', 'ChequeController@view_pdf')->name('view_cheque_pdf');
    Route::post('/cheque/{cheque}/print', 'ChequeController@print')->name('cheque_print');
    Route::post('/cheque/{cheque}/print_queue/{queue_id}', 'ChequeController@print_queue')->name('cheque_print_queue');
    Route::post('/cheques/print_queue/{queue_id}', 'ChequeController@print_queue_many')->name('cheque_print_queue_many');
    Route::resource('/contractor', 'ContractorController');
    Route::resource('/organisation', 'OrganisationController');

    Route::model('expensetype', 'App\ExpenseType');
    Route::resource('/expensetype', 'ExpenseTypeController');
    Route::resource('/nomenclature', 'NomenclatureController');
    Route::resource('/category', 'CategoryController');
    Route::resource('/support', 'SupportController');
    Route::resource('/log', 'LogController');

    Route::model('cheque_schedule', 'App\ChequeSchedule');
    Route::resource('/cheque_schedule', 'ChequeScheduleController');

    Route::post('/cheque_schedule/upload', 'ChequeScheduleController@upload');
    Route::get('/cheque_schedules/template', 'ChequeScheduleController@template');
    Route::get('/cheque_schedules/template_date', 'ChequeScheduleController@template_date');
    Route::get('/cheque_schedules/template_date_zip', 'ChequeScheduleController@template_date_zip');

    Route::get('modulkassa/test', 'HomeController@modulkassa');
    Route::get('test-broadcast', function(){
        // broadcast(new \App\Events\TestEvent);

        // $event = new \App\Events\LogEvent(Auth::user(), Auth::user(), 'login', 'user');
        // event( $event );

        $cheque = \App\Cheque::find(env('APP_ENV') == 'production' ? 3384 : 170);

        $cheque->load(['user','organisation']);

        \Log::debug('event to ' . $cheque->user->email);
        event(new \App\Events\LogEvent($cheque, $cheque->user, $action="success", $type="cheque"));

        \Log::debug('notification to ' . $cheque->user->email);
        $cheque->user->notify((new \App\Notifications\Customers\ChequeComplete($cheque)));

        \Log::debug('PHP mail:');

        $to      = 'yanusdnd@inbox.ru';
        $subject = 'the subject';
        $message = 'hello';
        $headers = 'From: ' . env('MAIL_FROM_ADDRESS') . "\r\n" .
            'Reply-To: ' . env('MAIL_FROM_ADDRESS') . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);


    });
    Route::get('/shell', 'HomeController@shell');
});

Route::get('modulkassa/cheque/{cheque}', 'ModulekassaController@callback')->name('modulkassa.callback');
// Route::post('modulkassa/cheque/{cheque}', 'ModulekassaController@callback');

Route::post(
    'webhooks/cloudpayments/receipt',
    'CloudpaymentsWebhookController@receipt'
)->name('cloudpayments.webhook.receipt');

Route::middleware(['auth:api'])->group(function () {
    Route::resource('/api/cheque', 'ChequeController');
    Route::resource('/api/contractor', 'ContractorController');
    Route::resource('/api/organisation', 'OrganisationController');
    Route::resource('/api/expensetype', 'ExpenseTypeController');
    Route::resource('/api/nomenclature', 'NomenclatureController');

    Route::get('export', 'HomeController@export')->name('exports');
});
