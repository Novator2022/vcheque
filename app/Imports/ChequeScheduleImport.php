<?php

namespace App\Imports;

use Log;
use App\User;
use App\Organisation;
use App\ExpenseType;
use App\ChequeSchedule;
use App\Contractor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Collection;

class ChequeScheduleImport implements ToCollection, WithMultipleSheets, WithCalculatedFormulas
{
    protected $user;
    protected $no_nds;
    public function __construct($user, $no_nds = false)
    {
        $this->user = $user;
        $this->no_nds = $no_nds;
    }

    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    /**
     * @param array $row
     *
     * @return User|null
     */

    public function collection_date(Collection $rows)
    {
        $user = $this->user;
        $no_nds = $this->no_nds;
        $nomenclature = null;
        $quantity = null;
        $amount = null;
        $date = null;
        $periodic = ChequeSchedule::PERIODIC_ONCE;
        $organisationName = null;
        $contractorName = null;
        $sf = null;
        $email = null;
        $data = [];
        $organisation = null;

        $flag_in_cheque = false;
        $skip = 2;
        foreach ($rows as $row) {
            if ($skip-- > 0) continue;

            foreach ($row as $rindex => $row_value) {
                $row[$rindex] = trim(preg_replace('/\s+/sim', ' ', $row_value));
            }

            if ($row[0] == '№ чека') {
                $flag_in_cheque = true;
                $skip = 4;
                continue;
            }

            if ($row[0] == 'Итоговая сумма чека:') {
                // make cheque schedule
                // print '<pre>';
                // // print_r($user);
                // print_r($organisation ? $organisation->name : false);
                // print_r($data);
                // print '</pre>';

                if (!empty($user) && !empty($organisation) && !empty($data)) {
                    // Log::debug('Checks:' . "\n\tuser:\t" . json_encode($user) .  "\n\torganisation:\t" . json_encode($organisation) . "\n\tdata:\t" . json_encode($data));
                    // Log::debug($data);

                    if ($user->id == 29) {
                        $amountTotal = 0;
                        foreach ($data['positions'] as $data_item) {
                            $amountTotal += $data_item['amount_to'] * $data_item['quantity_to'];
                        }

                        if ($amountTotal > 100000) {
                            throw new \Exception("Ошибка загрузки - сумма чека превышает 100 000", 2);
                        }
                    }

                    $cs = ChequeSchedule::create([
                        'user_id' =>$user->id,
                        'expense_type_id' => $organisation->expense_type_id,
                        'organisation_id' => $organisation->id,
                        'data' => $data,
                        'period_start' => $date,
                        'period_end' => $date,
                        'periodic' => $periodic,
                        'updated_at' => '2000-01-01 00:00:00',
                        'no_nds' => $no_nds ? 1 : 0,
                        'email' => $email ?? null
                    ]);

                    Log::debug('Created ChequeSchedule: ' . json_encode($cs));
                }

                $nomenclature = null;
                $quantity = null;
                $amount = null;
                $date = null;
                $organisationName = null;
                $contractorName = null;
                $sf = null;
                $data =[];
                $organisation = null;

                $flag_in_cheque = false;
                $skip = 1;
                continue;
            }

            if ($flag_in_cheque) {
                // 1 - название номенклатуры, 4 - за шт
                if (empty($row[1]) || ($row[1] == 'Пропишите нужную вам номенклатуру') || empty($row[4])) {
                    continue;
                }

                // Log::debug('ChequeScheduleImport[row]: ' . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

                $nomenclature = $row[1];
                $quantity = $row[3];
                $amount = round($row[4], 2);
                $date = !empty($row[6]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[6]) : $date;
                $organisationName = empty($row[8]) ? $organisationName : $row[8];
                $contractorName = empty($row[9]) ? $contractorName : $row[9];
                $sf = empty($row[7]) ? $sf : $row[7];

                // $contractor = Contractor::where('name', $contractorName)->first();
                // // Log::debug('ChequeScheduleImport[contractor][' . $contractorName . ']: ' . json_encode($contractor, JSON_UNESCAPED_UNICODE));

                // if (empty($contractor) && !empty($contractorName)) {
                //     $contractor = Contractor::create([
                //         'user_id' => $user->id,
                //         'name' => $contractorName
                //     ]);
                // }

                if (isset($data['positions'])) {
                    $data['positions'][] = array(
                        'nomenclature' => $nomenclature,
                        'quantity_from' => $quantity,
                        'quantity_to' => $quantity,
                        'amount_from' => $amount,
                        'amount_to' => $amount,
                        'sf' => (!empty($sf) && $sf == 'Да') ? 1 : 0,
                    );
                } else {
                    $data = [
                        "contractor" => null, //empty($contractor) ? null : $contractor->id,
                        "positions" => [
                            [
                                'nomenclature' => $nomenclature,
                                'quantity_from' => $quantity,
                                'quantity_to' => $quantity,
                                'amount_from' => $amount,
                                'amount_to' => $amount,
                                'sf' => (!empty($sf) && $sf == 'Да') ? 1 : 0,
                            ]
                        ]
                    ];
                }

                Log::debug('ChequeScheduleImport[data]: '. json_encode($data, JSON_UNESCAPED_UNICODE));

                $organisationName = trim(str_replace(['ООО', 'с НДС:', 'без НДС:'], '', $organisationName));
                $organisation = Organisation::where("name", "like", "%{$organisationName}%")->first();
                Log::debug('ChequeScheduleImport[organisation][' . $organisationName . ']: ' . json_encode($organisation, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    public function collection(Collection $rows)
    {
        if ((isset($rows[0][0])) && (trim($rows[0][0]) == 'Кликните по этой ячейке и скачайте файл "порядок работы" с инструкцией по заполнению данной заявки')) {
            return $this->collection_date($rows);
        }

        $first = true;

        $email = null;
        $user = $this->user;
        $no_nds = $this->no_nds;
        $nomenclature = null;
        $quantity = null;
        $amount = null;
        $period_from = null;
        $period_to = null;
        $periodic = null;
        $organisationName = null;
        $contractorName = null;
        $upd = null;
        $sf = null;
        $email = null;
        $data =[];
        $organisation = null;
        $expenseType = null;

        $i = 0;
        foreach ($rows as $row) {
            // Log::debug('row[' . (++$i) . ']: ' . $row[0] . '{' . (mb_strtolower($row[0]) == 'итого' ? 'итого' : 'wait_for') . '}');
            if (!empty($row[0])) {
                if ($first) {
                    $first = false;
                    continue;
                } elseif (mb_strtolower($row[0]) == 'итого') {
                    // make cheque schedule

                    if (!empty($user) && !empty($organisation) && !empty($data)) {
                        Log::debug('Checks:' . "\n\tuser:\t" . json_encode($user) .  "\n\torganisation:\t" . json_encode($organisation) . "\n\tdata:\t" . json_encode($data));
                        Log::debug($data);

                        if ($user->id == 29) {
                            $amountTotal = 0;
                            foreach ($data['positions'] as $data_item) {
                                $amountTotal += $data_item['amount_to'] * $data_item['quantity_to'];
                            }

                            if ($amountTotal > 100000) {
                                throw new \Exception("Ошибка загрузки - сумма чека превышает 100 000", 2);
                            }
                        }

                        $cs = ChequeSchedule::create([
                            'user_id' =>$user->id,
                            'expense_type_id' => $organisation->expense_type_id,
                            'organisation_id' => $organisation->id,
                            'data' => $data,
                            'period_start' => $period_from,
                            'period_end' => $period_to,
                            'periodic' => $periodic,
                            'updated_at' => '2000-01-01 00:00:00',
                            'no_nds' => $no_nds ? 1 : 0,
                            'email' => $email ?? null
                        ]);

                        Log::debug('Created ChequeSchedule: ' . json_encode($cs));
                    }

                    $nomenclature = null;
                    $quantity = null;
                    $amount = null;
                    $period_from = null;
                    $period_to = null;
                    $periodic = null;
                    $organisationName = null;
                    $contractorName = null;
                    $upd = null;
                    $sf = null;
                    $data =[];
                    $organisation = null;
                    $expenseType = null;
                }
            }
            if (!is_numeric($row[0])) {
                continue;
            }
            if (empty($row[1]) || empty($row[2]) || empty($row[3])) {
                continue;
            }
            Log::debug('ChequeScheduleImport[row]: ' . json_encode($row, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            // $email = $row[1];
            $nomenclature = $row[1];
            $quantity = $row[3];
            $amount = round($row[4], 2);
            $period_from = !empty($row[6]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[6]) : $period_from;
            $period_to = !empty($row[7]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[7]) : $period_to;
            $periodic = !empty($row[8]) ? $row[8] : $periodic;
            $organisationName = empty($row[9]) ? $organisationName : $row[9];
            $contractorName = empty($row[10]) ? $contractorName : $row[10];
            $upd = empty($row[11]) ? $upd : $row[11];
            $sf = empty($row[12]) ? $sf : $row[12];
            $email = empty($row[13]) ? $sf : $row[13];



            $contractor = Contractor::where('name', $contractorName)->first();
            // Log::debug('ChequeScheduleImport[contractor][' . $contractorName . ']: ' . json_encode($contractor, JSON_UNESCAPED_UNICODE));

            if (empty($contractor) && !empty($contractorName)) {
                $contractor = Contractor::create([
                    'user_id' => $user->id,
                    'name' => $contractorName
                ]);
            }

            if (isset($data['positions'])) {
                $data['positions'][] = array(
                    'nomenclature' => $nomenclature,
                    'quantity_from' => $quantity,
                    'quantity_to' => $quantity,
                    'amount_from' => $amount,
                    'amount_to' => $amount,
                    'upd' => !empty($upd) && $upd == 'Да' ,
                    'sf' => !empty($sf) && $sf == 'Да' ,
                );
            } else {
                $data = [
                    "contractor" => empty($contractor) ? null : $contractor->id,
                    "positions" => [
                        [
                            'nomenclature' => $nomenclature,
                            'quantity_from' => $quantity,
                            'quantity_to' => $quantity,
                            'amount_from' => $amount,
                            'amount_to' => $amount,
                            'upd' => !empty($upd) && $upd == 'Да' ,
                            'sf' => !empty($sf) && $sf == 'Да' ,
                        ]
                    ]
                ];
            }
            // Log::debug('ChequeScheduleImport[data]: '. json_encode($data, JSON_UNESCAPED_UNICODE));
            $inn = preg_replace('/^.+?\s\-\s(\d+).*$/isu', '$1', $organisationName);
            $organisation = Organisation::whereRaw('JSON_EXTRACT(data, "$.inn") = ' . "'{$inn}'")->first();
            // Log::debug('ChequeScheduleImport[organisation][' . $organisationName . ']: ' . json_encode($organisation, JSON_UNESCAPED_UNICODE));

            if (empty($organisation)) {
                $expenseType = $organisation->expense_type;
            }

            if (!empty($periodic)) {
                switch ($periodic) {
                    case "Ежедневно":
                        $periodic = ChequeSchedule::PERIODIC_DAYLY;
                        break;
                    case "Еженедельно":
                        $periodic = ChequeSchedule::PERIODIC_WEEKLY;
                        break;
                    case "Ежемесячно":
                        $periodic = ChequeSchedule::PERIODIC_MONTHLY;
                        break;
                }
            }
        }
    }
}
