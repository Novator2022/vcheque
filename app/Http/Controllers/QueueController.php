<?php

namespace App\Http\Controllers;

use Log;
use App\User;
use App\Cheque;
use App\ChequePrinted;
use App\ChequeToPrint;
use App\Services\Modulkassa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QueueController extends Controller
{
    private $token = 'iNgsTiCuNTrAYAotcHALIaTKIwAWNBRe';
    private $tokens = [
        'iNgsTiCuNTrAYAotcHALIaTKIwAWNBRe' => 1,
        'R9Ng5bzbjG7cVJ7lrpFmDgTKvWvaf6wG' => 2,
        'IPq4wU5OZ1T21FIJu2zvm1BvcDUyNDxD' => 3,
    ];

    private $from_cheque_id = 107597;

    public function get_queue(Request $request, $token = false)
    {
        // if ($token != $this->token) exit;
        if (!isset($this->tokens[$token])) exit;
        $queue_id = $this->tokens[$token];

        $has_cheques = false;

        $cheques = ChequeToPrint::where('status', 'pending')->where('queue_id', $queue_id)->limit(1)->get();
        foreach ($cheques as $cheque) {
            print $cheque->cheque_id;
            $has_cheques = true;
        }

        // if ((!$has_cheques) && ($queue_id == 1)) {
        //     // для главного принтера еще и обычные чеки все подряд печатаем
        //     $cheques = Cheque::where('id', '>=', $this->from_cheque_id)
        //         ->where('status', 'success')
        //         ->whereRaw("cheques.id not in (select cp.cheque_id from cheque_printed cp where queue_id = " . (int)$queue_id . ")")
        //         ->where('organisation_id', '>=', 98)
        //         ->where('user_id', '!=', 34)
        //         ->whereNotIn('organisation_id', [107, 108, 109, 116, 117, 118, 119])
        //         ->limit(1)
        //         ->get();
        //     foreach ($cheques as $cheque) {
        //         print $cheque->id;
        //     }
        // } else

        if ((!$has_cheques) && ($queue_id == 3)) {
            // для 3 принтера печатаем его чеки
            $cheques = Cheque::where('id', '>=', $this->from_cheque_id)
                ->where('status', 'success')
                ->whereRaw("cheques.id not in (select cp.cheque_id from cheque_printed cp where queue_id = " . (int)$queue_id . ")")
                ->where('user_id', 34)
                ->limit(1)
                ->get();
            foreach ($cheques as $cheque) {
                print $cheque->id;
            }
        }
    }

    public function complete(Request $request, $token = false, $cheque_id = false)
    {
        // if ($token != $this->token) exit;
        if (!isset($this->tokens[$token])) exit;
        $queue_id = $this->tokens[$token];

        $cheques_to_print = ChequeToPrint::where('cheque_id', $cheque_id)->where('queue_id', $queue_id)->where('status', 'pending');

        if ($cheques_to_print->count() > 0) {
            ChequeToPrint::where('cheque_id', $cheque_id)
                ->where('queue_id', $queue_id)
                ->where('status', 'pending')
                ->limit(1)
                ->update(['status' => 'complete']);
        } else {
            ChequePrinted::create([
                'cheque_id' => $cheque_id,
                'queue_id' => $queue_id,
            ]);
        }

        print 'OK';
    }

    public function pdf(Request $request, $token = false, $cheque_id = false)
    {
        // if ($token != $this->token) exit;
        if (!isset($this->tokens[$token])) exit;
        $queue_id = $this->tokens[$token];
        
        $cheque = Cheque::with(['user','organisation'])->where('id', $cheque_id)->firstOrFail();

        include_once(base_path('vendor/qr/qrlib.php'));
        ob_start();
        \QRcode::png($cheque->modulkassa['fiscalInfo']['qr'], false, QR_ECLEVEL_L, 4);
        $qr = base64_encode(ob_get_clean());

        $postfix = '<br/><br/>-';

        // $w = 90;
        $w = 70;

        require_once __DIR__ . '/vendor/autoload.php';

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => storage_path('pdf'),
            'default_font' => 'helvetica',
            'mode' => 'utf-8', 
            'format' => [$w, 3000],
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0
        ]);

        /* шаг 1 - заливаем html */
        // $mpdf->WriteHTML(view(($queue_id == 3 ? 'cheque.view_v2' : 'cheque.view'), ['qr' => $qr, 'cheque' => $cheque]));
        $mpdf->WriteHTML(view(($queue_id == 3 ? 'cheque.view_v2' : 'cheque.view_pdf_29052025'), ['qr' => $qr, 'cheque' => $cheque]));
        $mpdf->WriteHTML($postfix);

        $mpdf->page   = 0;
        $mpdf->state  = 0;
        unset($mpdf->pages[0]);
        
        /* шаг 2 - зануляем все, перезаливаем html с высотой вычисленной */
        $p = 'P';
        $mpdf->_setPageSize(array($w, $mpdf->y), $p);
        // $mpdf->WriteHTML(view(($queue_id == 3 ? 'cheque.view_v2' : 'cheque.view'), ['qr' => $qr, 'cheque' => $cheque]));
        $mpdf->WriteHTML(view(($queue_id == 3 ? 'cheque.view_v2' : 'cheque.view_pdf_29052025'), ['qr' => $qr, 'cheque' => $cheque]));
        $mpdf->WriteHTML($postfix);

        // вывод
        $mpdf->Output($cheque_id . '.pdf', 'I');
    }
}
