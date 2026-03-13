<?php

namespace App\Http\Controllers;

use App\ChequeSchedule;
use Illuminate\Http\Request;
use App\Imports\ChequeScheduleImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChequeScheduleTemplateExport;
use App\User;
use App\Organisation;
use ZipArchive;

class ChequeScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $item = ChequeSchedule::with(['user','organisation', 'expense_type'])->when($request->has('user_id'), function ($query) use ($request) {
            $users = $request->user_id;
            $query->whereIn('user_id', preg_split('/\,/im', $users));
        });

        if (in_array($request->user()->role, ['user','manager'])) {
            $users = [$request->user()->id];
            $users = array_merge($users, User::where('manager_id', $request->user()->id)->pluck('id')->toArray());
            $item->whereIn('user_id', $users);
        }

        if ($request->has('organisation_id')) {
            $item->where('organisation_id', $request->organisation_id);
        }
        if ($request->has('expense_type_id')) {
            $item->where('expense_type_id', $request->status);
        }

        $item->where('created_at', '>=', date('Y-m-d H:i:s', date('U') - 86400 * 60));

        $items = $item->orderBy('id', 'desc')->get();
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
        $data = $request->validate([
            "periodic" => "sometimes|required|string",
            "organisation_id" => "sometimes|required|exists:organisations,id",
            "period_start" => "sometimes|required|date",
            "period_end" => "sometimes|required|date",
            "data" => "sometimes|required|array",
            "template_upload" => "sometimes|required|file",
        ]);

        $chequeSchedule = null;

        $import = $request->has('template_upload') ? $request->template_upload->path() : false;
        $importFile = $import ? $request->template_upload->store('local') : false;

        $user_id = $request->input('user_id', null);
        $no_nds = $request->input('no_nds', 0);

        \Log::debug($no_nds);

        if ($importFile) {
            $user = $request->user();
            if (!empty($user_id)) {
                $user = User::find($user_id);
            }
            
            try {
                Excel::import(new ChequeScheduleImport($user, $no_nds), $importFile);
            } catch (\Exception $e) {
                return response()->json([
                    "error" => true,
                    "code" => "INVALID_DATA",
                    "message" => $e->getMessage()
                ], 400, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);

                // $request->session()->flash('message', $e->getMessage());
            }
        } else {
            $data = $request->all();

            if (isset($data["no_nds"])) {
                if (!empty($data["no_nds"])) {
                    if (sizeof($data['data']['positions']) > 0) {
                        foreach ($data['data']['positions'] as $pindex => $position) {
                            $data['data']['positions'][$pindex]['nds'] = 0;
                        }
                    }
                }
            }

            $chequeSchedule = ChequeSchedule::create($data);
            $chequeSchedule->load(['user','organisation', 'expense_type']);
        }
        return response()->json($chequeSchedule, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ChequeSchedule  $chequeSchedule
     * @return \Illuminate\Http\Response
     */
    public function show(ChequeSchedule $chequeSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ChequeSchedule  $chequeSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(ChequeSchedule $chequeSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ChequeSchedule  $chequeSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ChequeSchedule $chequeSchedule)
    {
        $data = $request->all();

        $chequeSchedule->update($data);
        $chequeSchedule->load(['user','organisation', 'expense_type']);
        return response()->json($chequeSchedule, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ChequeSchedule  $chequeSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChequeSchedule $chequeSchedule)
    {
        $chequeSchedule->delete();
        return response()->json($chequeSchedule, 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * [template description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function template(Request $request)
    {
        return Excel::download(new ChequeScheduleTemplateExport, 'Шаблон_заявки_на_чеки_по_расписанию_' . date('Ymd') . '.xlsx');
    }

    public function template_date_old(Request $request)
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load(resource_path('views/xlsx/template_date.xlsx'));
        $spreadsheet->setActiveSheetIndex(1);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // вставляем данные по компаниям
        $index = 2;
        $organisations = Organisation::orderBy('is_nonds', 'desc')->orderBy('name')->get();   

        foreach ($organisations as $organisation) {
            $name = preg_replace('/["\'\n\r\t]/ui', '', $organisation->name);
            $inn = empty($organisation->data['inn']) ? '' : $organisation->data['inn'];

            $worksheet->setCellValue("A" . $index, ($organisation->is_nonds ? 'без' : 'с') . ' НДС: ' .  $name);
            $worksheet->setCellValue("B" . $index, $inn);
            $worksheet->setCellValue("C" . $index, $organisation->data['okveds'] ?? '');

            $index++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Шаблон_заявки_на_чеки_на_дату_' . date('Ymd') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function template_date(Request $request)
    {
        // die('ok!');
        $tempFile = storage_path(uniqid() . '.zip');

        $z = new ZipArchive();
        $zipFileCode  = $z->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if (true === $zipFileCode) {
            $z->addFile(resource_path('views/xlsx/template_past_date.xlsx'), 'Заявка на прошедшие даты (чеки).xlsx');

            // 
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
            $spreadsheet = $reader->load(resource_path('views/xlsx/template_date.xlsx'));
            $spreadsheet->setActiveSheetIndex(1);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // вставляем данные по компаниям
            $index = 2;
            $organisations = Organisation::orderBy('is_nonds', 'desc')->orderBy('name')->get();   

            foreach ($organisations as $organisation) {
                $name = preg_replace('/["\'\n\r\t]/ui', '', $organisation->name);
                $inn = empty($organisation->data['inn']) ? '' : $organisation->data['inn'];

                $worksheet->setCellValue("A" . $index, ($organisation->is_nonds ? 'без' : 'с') . ' НДС: ' .  $name);
                $worksheet->setCellValue("B" . $index, $inn);
                $worksheet->setCellValue("C" . $index, $organisation->data['okveds'] ?? '');

                $index++;
            }

            $spreadsheet->setActiveSheetIndex(0);

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

            ob_start();
            $writer->save('php://output');
            $z->addFromString('Заявка на текущую и будущие даты (чеки).xlsx', ob_get_clean());
            //

            $z->close();
        }

        return response()->download($tempFile, 'Шаблоны заявки на дату ' . date('Ymd') . ' (чеки).zip')->deleteFileAfterSend(true);
    }
}
