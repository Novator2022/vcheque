<?php

namespace App\Jobs;

use Log;
use App\Cheque;
use App\ExportCheque;
use App\ExportCompanyInn;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // protected $connection = 'redis';
    protected $tries = 3;

    protected $check;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Cheque $check)
    {
        $this->check = $check;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('Handing export job: #' . $this->check->id);

        $export_count = ExportCheque::where('external_id', $this->check->id)->where('external_source', 'vcheque')->count();
        if (!$export_count) {
            try {
                // dd($this->check);
                $files = ExportCheque::get_files($this->check->id);

                if ($files) {
                    $datetime = strtotime($this->check->modulkassa['fiscalInfo']['date']);

                    $date = date('Y-m-d', $datetime);
                    $year = date('Y', $datetime);
                    $kv = (int)((date('n', $datetime)-1)/3+1);

                    // $company_name = null;
                    // $company_id = null;

                    // компания
                    $organisation = $this->check->organisation;
                    $organisation_name = $organisation->name;
                    $organisation_name = preg_replace('/["\'\»\«]/u', '', $organisation_name);
                    $organisation_name = preg_replace('/ип\s*/ui', '', $organisation_name);
                    $organisation_name = preg_replace('/ооо\s*/ui', '', $organisation_name);
                    $organisation_name = preg_replace('/^\s*/ui', '', $organisation_name);

                    $company = ExportCompanyInn::firstOrCreate(
                        ['inn' => $organisation->data['inn']],
                        [
                            'company_name' => $organisation_name,
                            'inn' => $organisation->data['inn'],
                            'kpp' => $organisation->data['kpp'],
                            'no_nds' => $organisation->is_nonds,
                        ]
                    );
                    // /компания

                    $company_name = $company->company_name;
                    $company_id = $company->id;

                    $path_prefix = storage_path('market_files/');
                    
                    $path = $year . '/' . $kv . '/';
                    if (!file_exists($path_prefix . '/' . $year)) {
                        @mkdir($path_prefix . '/' . $year);
                    }
                    if (!file_exists($path_prefix . '/' . $year . '/' . $kv)) {
                        @mkdir($path_prefix . '/' . $year . '/' . $kv);
                    }

                    $blurred_filename = $filename = $path . 'exp_' . ExportCheque::generateRandomString(32) . '.png';
                    // $pdf_filename = $path . 'exp_1_' . ExportCheque::generateRandomString(32) . '.pdf';
                    $pdf2_filename = $path . 'exp_2_' . ExportCheque::generateRandomString(32) . '.pdf';

                    // file_put_contents($path_prefix . $pdf_filename, $files['pdf']);
                    file_put_contents($path_prefix . $pdf2_filename, $files['pdf2']);
                    file_put_contents($path_prefix . $filename, $files['png']);
                    file_put_contents($path_prefix . $blurred_filename, $files['png']);

                    $data = [
                        'company_name' => $company_name,
                        'company_id' => $company_id,
                        'categories' => $this->check->category_id ? [$this->check->category->name] : null,
                        'name' => $this->check->id . '.png',
                        'date' => $date,
                        'year' => $year,
                        'kv' => $kv,
                        'filename' => $filename,
                        'blurred_filename' => $blurred_filename,
                        'pdf_filename' => $pdf2_filename,
                        'total' => $this->check->modulkassa['fiscalInfo']['sum'],
                        'is_usn' => $this->check->no_nds,
                        'external_id' => $this->check->id,
                        'external_source' => 'vcheque',
                        'external_schedule_id' => $this->check->schedule_id,
                        'external_category_id' => $this->check->category_id,
                        'status' => 1,
                        // 'status' => 0,
                    ];

                    $products = [];
                    foreach ($this->check->data['positions'] as $item) {
                        $products[] = [
                            'name' => $item['nomenclature'],
                        ];
                    }

                    // \Log::debug($data);
                    // \Log::debug($products);

                    // создаем экспортный c номенклатурой
                    $cheque = ExportCheque::create($data);
                    $cheque->products()->createMany($products);
                }
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                \Log::error("The exception was created on line: " . $e->getLine());
            }
        }

        $this->check->update(['export' => 0]);
    }
}
