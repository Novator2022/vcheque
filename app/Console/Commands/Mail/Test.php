<?php

namespace App\Console\Commands\Mail;

use Illuminate\Console\Command;
use App\Services\Modulkassa;
use App\User;
use App\Cheque;
use App\Organisation;
use App\Notifications\Customers\ChequeComplete;
use App\Notifications\Customers\ChequeError;
use App\Notifications\Customers\ChequeInprogress;
use App\Notifications\Managers\ChequeError as ManagerChequeError;
use Notification;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cheque = \App\Cheque::find(env('APP_ENV') == 'production' ? 3384 : 170);

        $cheque->load(['user','organisation']);

        $this->info('event to ' . $cheque->user->email);
        event(new \App\Events\LogEvent($cheque, $cheque->user, $action="success", $type="cheque"));

        $this->info('notification Inprogress to ' . $cheque->user->email);
        $cheque->user->notify((new ChequeInprogress($cheque)));

        $this->info('notification Complete to ' . $cheque->user->email);
        $cheque->user->notify((new ChequeComplete($cheque)));

        $this->info('notification error to ' . $cheque->user->email);
        $cheque->user->notify((new ChequeError($cheque)));

        $this->info('manager notification error to ' . $cheque->user->email);
        Notification::send(User::where('email', 'v.bushuev@gmail.com')->get(), new ManagerChequeError($cheque));

        // $this->info('PHP mail:');
        //
        // $to      = 'yanusdnd@inbox.ru';
        // $subject = 'the subject';
        // $message = 'hello';
        // $headers = 'From: ' . env('MAIL_FROM_ADDRESS') . "\r\n" .
        //     'Reply-To: ' . env('MAIL_FROM_ADDRESS') . "\r\n" .
        //     'X-Mailer: PHP/' . phpversion();
        //
        // mail($to, $subject, $message, $headers);
    }
}
