<?php

namespace App\Http\Controllers;

use Log;
use App\User;
use App\Cheque;
use App\ChequeToPrint;
use App\Param;
use App\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $item = Cheque::with(['user','organisation'])->when($request->has('user_id'), function ($query) use ($request) {
            $users = $request->user_id;
            $query->whereIn('user_id', preg_split('/\,/im', $users))
            ;
        });
        if (in_array($request->user()->role, ['user','manager'])) {
            $users = [$request->user()->id];
            $users = array_merge($users, User::where('manager_id', $request->user()->id)->pluck('id')->toArray());
            $item->whereIn('user_id', $users);
        }
        if ($request->has('dates')) {
            $item->whereBetween('created_at', preg_split('/\,/im', $request->dates));
        }
        if ($request->has('organisation_id')) {
            $item->where('organisation_id', $request->organisation_id);
        }
        if ($request->has('status')) {
            $item->whereIn('status', preg_split('/\,/im', $request->status));
        }
        if ($request->has('type')) {
            $item->whereIn('type', preg_split('/\,/im', $request->type));
        }
        if ($request->has('search')) {
            $search = $request->get('search');

            $item->where(function ($query) use($search) {
                $users = User::where('email', 'like', '%' . $search . '%')->pluck('id');
                $organisations = Organisation::where('name', 'like', '%' . $search . '%')->orWhere('data', 'like', '%' . $search . '%')->pluck('id');

                $query->orWhere('id', $search)
                      ->orWhere('amount', $search)
                      ->orWhereIn('user_id', $users)
                      ->orWhereIn('organisation_id', $organisations);
            });
        }

        $items = $item->orderBy('id', 'desc')->limit(256)->get()->toArray();

        // foreach ($items as $index => $item) {
        //     if ($item['status'] == 'new') {
        //         $slot = \App\ChequeSlot::where('cheque_id', $item['id'])->first();
        //         if ($slot) {
        //             $items[$index]['status_text'] = 'Отложен на ' . substr($slot->slot->time_from, 0, -3);
        //         }
        //     }
        // }

        return response()->json($items, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data=$request->all();
        $validator = Validator::make($data, [
            'organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'exists:users,id',
            'email' => 'required|email:rfc',
        ],[
            'organisation_id.required' => 'Не указана организация',
            'category_id.required' => 'Не указана категория',
            'email.required' => 'Не указан email',
        ]);
        $user = ($request->has('user_id'))? User::find($request->user_id) : $request->user();
        $data["user_id"] = $user->id;
        if ($validator->fails()) {
            return response()->json($validator->errors(), 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }

        /**/
        $procent = 0;

        if ($user->procent) {
            $procent = $user->procent;
        } else {
            $procent = Param::where('name', 'default_procent')->first()->value;
        }

        $summ = 0;

        if (sizeof($data['data']['positions']) > 0) {
            foreach ($data['data']['positions'] as $pindex => $position) {
                $summ += $position['amount'] * $position['quantity'];
            }
        }

        $take = $summ * ($procent / 100);

        $data['amount'] = $summ;

        if (($user->balance - $user->frozen) < $take) {
            return response()->json(['balance' => ['Недостаточно денег на счету']], 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }

        // проверка на лимиты
        if (!empty($data["sf"])) {
            $org = Organisation::where('id', $request->organisation_id)->firstOrFail();

            if ($org->reach() + $summ > $org->limit) {
                return response()->json(['limit' => ['Превышен лимит выдачи номеров счетов-фактур для данной компании']], 500, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            $data["sf"] = 0;
        }

        if (!isset($data['metadata'])) {
            $data["metadata"]=[];
        }
        $file = $request->has('file') ? $request->file('file')->store('public') : null;
        $data['file'] = $file;

        if (isset($data["no_nds"])) {
            if (!empty($data["no_nds"])) {
    	        if (sizeof($data['data']['positions']) > 0) {
    		        foreach ($data['data']['positions'] as $pindex => $position) {
    		        	$data['data']['positions'][$pindex]['nds'] = 0;
    		        }
                }
	        }
	    }

        $item = Cheque::create($data);
        $item->load(['user','organisation']);
        event(new \App\Events\LogEvent($item, $user, $action="create", $type="cheque"));

        return response()->json($item, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cheque  $cheque
     * @return \Illuminate\Http\Response
     */
    public function show(Cheque $cheque)
    {
        $cheque->load(['user','organisation']);
        return response()->json($cheque, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cheque  $cheque
     * @return \Illuminate\Http\Response
     */
    public function edit(Cheque $cheque)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cheque  $cheque
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cheque $cheque)
    {
        $data = $request->all();

        $cheque->update($data);

        $cheque->load(['user','organisation']);
        return response()->json($cheque, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cheque  $cheque
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cheque $cheque)
    {
        $cheque->delete();
        return response()->json($cheque, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function view(Request $request, $cheque_id = false)
    {
        $cheque = Cheque::with(['user','organisation'])->where('id', $cheque_id)->firstOrFail();

        include_once(base_path('vendor/qr/qrlib.php'));
        ob_start();
        \QRcode::png($cheque->modulkassa['fiscalInfo']['qr'], false, QR_ECLEVEL_L, 4);
        $qr = base64_encode(ob_get_clean());

        if ($request->has('pdf')) {
            require_once __DIR__ . '/vendor/autoload.php';
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
            $mpdf->Output($cheque_id . '.pdf', 'I');
        } else {
            return view('cheque.view', ['qr' => $qr, 'cheque' => $cheque]);
        }
    }

    public function view_pdf(Request $request, $cheque_id = false)
    {
        $cheque = Cheque::with(['user','organisation'])->where('id', $cheque_id)->firstOrFail();

        include_once(base_path('vendor/qr/qrlib.php'));
        ob_start();
        \QRcode::png($cheque->modulkassa['fiscalInfo']['qr'], false, QR_ECLEVEL_L, 4);
        $qr = base64_encode(ob_get_clean());

        $postfix = '<br/><br/>-';

        $w = 75;

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
        $mpdf->WriteHTML(view('cheque.view_pdf', ['qr' => $qr, 'cheque' => $cheque]));
        $mpdf->WriteHTML($postfix);

        $mpdf->page   = 0;
        $mpdf->state  = 0;
        unset($mpdf->pages[0]);
        
        /* шаг 2 - зануляем все, перезаливаем html с высотой вычисленной */
        $p = 'P';
        $mpdf->_setPageSize(array($w, $mpdf->y), $p);
        $mpdf->WriteHTML(view('cheque.view_pdf', ['qr' => $qr, 'cheque' => $cheque]));
        $mpdf->WriteHTML($postfix);

        // вывод
        $mpdf->Output($cheque_id . '.pdf', 'I');
    }

    public function print(Request $request, Cheque $cheque) {
        ChequeToPrint::create([
            'cheque_id' => $cheque->id
        ]);

        return response()->json(['ok' => 1], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
    
    public function print_queue(Request $request, Cheque $cheque, $queue_id = 1) {
        ChequeToPrint::create([
            'cheque_id' => $cheque->id,
            'queue_id' => $queue_id,
        ]);

        return response()->json(['ok' => 1], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function print_queue_many(Request $request, $queue_id = 1) {
        sleep(1);

        $cheques = $request->get('cheques', []);
        foreach ($cheques as $cheque_id) {
            ChequeToPrint::create([
                'cheque_id' => $cheque_id,
                'queue_id' => $queue_id,
            ]);
        }

        return response()->json(['ok' => 1], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }
}
