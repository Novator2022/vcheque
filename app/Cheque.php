<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    const STATUS_NEW = 'new';
    const STATUS_REQUEST = 'request';
    const STATUS_INPROGRESS = 'inprogress';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    const TYPE_MANUAL = 'manual';
    const TYPE_SCHEDULE = 'schedule';
    const TYPE_FREE = 'free';

    protected $fillable = [
        'user_id',
        'data',
        'status',
        'organisation_id',
        'category_id',
        'file',
        'modulkassa',
        'cloudpayments',
        'type',
        'schedule_id',
        'no_nds',
        'sf',
        'take',
        'amount',
        'email',
    ];
    protected $casts = [
        'data' => 'array',
        'modulkassa' => 'array',
        'cloudpayments' => 'array',
    ];
    protected $attributes = [
        'status' => Cheque::STATUS_NEW,
        'type' => Cheque::TYPE_MANUAL,
    ];

    protected $appends = [
        'doc_num',
        'doc_num_proj',
    ];
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    public function organisation()
    {
        return $this->belongsTo('App\Organisation')->withTrashed();
    }
    public static function getStatuses()
    {
        return [
            self::STATUS_NEW => self::STATUS_NEW,
            self::STATUS_REQUEST => self::STATUS_REQUEST,
            self::STATUS_INPROGRESS => self::STATUS_INPROGRESS,
            self::STATUS_SUCCESS => self::STATUS_SUCCESS,
            self::STATUS_FAILED => self::STATUS_FAILED,
        ];
    }
    public static function getTypes()
    {
        return [
            self::TYPE_MANUAL => "ручной ввод",
            self::TYPE_SCHEDULE => "по расписанию",
            self::TYPE_FREE => "розничный",
        ];
    }

    public function getDocNumAttribute()
    {
        return $this->organisation_id . '-' . $this->id;
    }

    public function getDocNumProjAttribute()
    {
        return '01-' . $this->organisation_id . '-' . $this->id;
    }

    public function time_slot($only_check = false)
    {
        $cnt = \App\ChequeSlot::where('cheque_id', $this->id)->count();

        if ($only_check) {
            return $cnt > 0;
        }

        if ($cnt == 0) {
            (new ChequeSlot)->assign($this);
        }

        return $this->belongsTo('App\ChequeSlot', 'id', 'cheque_id');
    }

    public function getModulkassaAttribute($value)
    {
        return $value ?: $this->makeModulkassaFromCloudpayments();
    }

    public function makeModulkassaFromCloudpayments() {
        if (isset($this->cloudpayments['Receipt'])) {
            $receipt = json_decode($this->cloudpayments['Receipt'], 1);

            return [
                'fiscalInfo' => [
                    'date' => $this->cloudpayments['DateTime'],
                    'qr' => $receipt['Url'],
                    'sum' => $this->cloudpayments['Amount'],
                    'shiftNumber' => $this->cloudpayments['SessionNumber'],
                    'fnDocNumber' => $this->cloudpayments['DocumentNumber'],
                    'fnDocMark' => $this->cloudpayments['FiscalSign'],
                    'checkNumber' => $this->cloudpayments['Number'],
                    'ecrRegistrationNumber' => $this->cloudpayments['RegNumber'],
                    'kktNumber' => $this->cloudpayments['DeviceNumber'],
                    'fnNumber' => $this->cloudpayments['FiscalNumber'],
                ],
            ];
        }
    }
}
