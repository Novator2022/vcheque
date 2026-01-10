<?php

namespace App\Http\Controllers;

use Storage;
use Redirect;

use Illuminate\Http\Request;
use App\Exports\SimpleXml;
use App\Exports\ChequeXML;
use App\Cheque;
use App\Param;
use App\ChequeSchedule;
use Auth;

use ZipArchive;

use App\Jobs\ModulkassaJob;

use App\Services\Modulkassa;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function login()
    {
        return redirect('/home');
    }
    public function index()
    {
        return view('home');
    }

    public function tariff()
    {
        $param = Param::where('name', 'default_procent')->firstOrFail(); 

        if (in_array(Auth::user()->role, ['user'])) {
            return view('tariff_user', ['default' => $param->value]);
        } else {
            return view('tariff_admin', ['default' => $param->value]);
        }
    }

    public function tariff_save(Request $request)
    {
        if (!in_array(Auth::user()->role, ['user'])) {
            // $param = Param::where('name', 'default_procent')->firstOrFail();
            $param = $request->get('param');
            foreach ($param as $name => $value) {
                Param::updateOrCreate(['name' => $name], ['value' => $value]);
            }

            return Redirect::back()->with('message', 'Сохранено!');
        } else {
            abort(404);
        }
    }

    public function export(Request $request){
        $export = new ChequeXML( $request->input(inn,'1234567') );
        $file = $export->store('cheque');
        return Storage::get($file);
    }

    public function modulkassa(Request $request)
    {
        $kassa = new Modulkassa();
        return $kassa->test();
    }

    public function shell(Request $request)
    {
    	file_put_contents(base_path('reboot_queue.lock'), 1);
    	return 'ok';
    }

    public function test(Request $request) {
        $cheques = Cheque::whereIn('id', [134912])->get(); 
        foreach ($cheques as $cheque) {
            $auth = $cheque->organisation->data["modulkassa"] ?? null;
            $kassa = new Modulkassa(false, $auth);

            print_r($kassa->chequeStatus($cheque));
        //     // ModulkassaJob::dispatch($cheque)->onConnection('redis');
        //     ModulkassaJob::dispatch($cheque)->onQueue('force_cheques');
        }

        die('ok!');
    }

    public function download_schedules(Request $request, $schedule_id) {
        $schedule = ChequeSchedule::where('id', $schedule_id)->firstOrFail();
        
        $to_export = [];

        $cheques = Cheque::where('organisation_id', $schedule->organisation_id)
            ->where('type', 'schedule')
            ->where('created_at', '>=', $schedule->created_at)
            ->get();

        foreach ($cheques as $cheque) {
            if ((sizeof($cheque['data']['positions'])) === (sizeof($schedule['data']['positions']))) {
                $all_good = true;
                foreach ($cheque['data']['positions'] as $cheque_position) {
                    $found = false;
                    foreach ($schedule['data']['positions'] as $schedule_position) {
                        if (
                            ($cheque_position['name'] == $schedule_position['nomenclature']) 
                            && 
                            ($cheque_position['amount'] >= $schedule_position['amount_from'])
                            &&
                            ($cheque_position['amount'] <= $schedule_position['amount_to'])
                            && 
                            ($cheque_position['quantity'] >= $schedule_position['quantity_from'])
                            &&
                            ($cheque_position['quantity'] <= $schedule_position['quantity_to'])
                        ) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $all_good = false;
                    }
                }

                if ($all_good) {
                    $to_export[] = $cheque;
                }
            }
        }

        if (sizeof($to_export) > 0) {
            include_once(base_path('vendor/qr/qrlib.php'));
            require_once __DIR__ . '/vendor/autoload.php';

            $files = [];
            foreach ($to_export as $cheque) {
                ob_start();
                \QRcode::png($cheque->modulkassa['fiscalInfo']['qr'], false, QR_ECLEVEL_L, 4);
                $qr = base64_encode(ob_get_clean());

                $fname = storage_path('pdf_tmp/' . $cheque->id . '.pdf');

                $mpdf = new \Mpdf\Mpdf([
                    'tempDir' => storage_path('pdf'),
                    'default_font' => 'helvetica',
                    'mode' => 'utf-8', 
                    'format' => [150, 1000],
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                    'margin_header' => 0,
                    'margin_footer' => 0
                ]);

                $mpdf->WriteHTML(view('cheque.view', ['qr' => $qr, 'cheque' => $cheque]));
                $mpdf->Output($fname);

                $files[] = $fname;

                unset($qr);
                unset($mpdf);
            }
        } else {
            abort(404);
        }

        $tempFile = storage_path('pdf_tmp/' . $schedule->id . '.zip');

        $z = new ZipArchive();
        $zipFileCode  = $z->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if (true === $zipFileCode) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $z->addFile($file, basename($file));
                }
            }

            $z->close();
        }

        return response()->download($tempFile, $schedule->id . '.zip');
    }
}
