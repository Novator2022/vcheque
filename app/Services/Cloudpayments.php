<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use App\Cheque;
use Log;

class Cloudpayments
{
    protected $baseUrl = 'https://api.cloudpayments.ru';

    protected $client;
    protected $publicId;
    protected $apiPassword;
    protected $inn;

    protected $messages = [];

    /**
     * Системы налогообложения:
     * 0 - ОСН, 1 - УСН Доходы, 2 - УСН Доходы минус Расходы, 3 - ЕСХН, 5 - Патент
     */
    const TAX_COMMON             = 0;
    const TAX_SIMPLIFIED         = 1;
    const TAX_SIMPLIFIED_EXPENSE = 2;
    const TAX_AGRICULTURAL       = 3;
    const TAX_PATENT             = 5;

    /**
     * Ставки НДС:
     * -1 — не облагается, 0 — 0%, 10 — 10%, 20 — 20%, 22 — 22% (с 01.01.2026)
     * 110 — расчётная 10/110, 120 — 20/120, 122 — 22/122
     */

    /**
     * $auth — массив из organisation->data['cloudpayments']:
     *   [
     *     'public_id' => 'pk_xxx',
     *     'password'  => 'yyy',
     *   ]
     *
     * $inn — строка из organisation->data['inn']
     *
     * Аналогично Modulkassa::__construct($debug, $auth)
     */
    public function __construct($debug = false, $auth = null, $inn = null)
    {
        $this->publicId    = (!empty($auth['public_id'])) ? $auth['public_id'] : env('CLOUDPAYMENTS_PUBLIC_ID', '');
        $this->apiPassword = (!empty($auth['password']))  ? $auth['password']  : env('CLOUDPAYMENTS_API_PASSWORD', '');
        $this->inn         = (!empty($inn))               ? $inn               : env('CLOUDPAYMENTS_INN', '');

        Log::debug('Cloudpayments API init, inn=' . $this->inn);

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'auth'     => [$this->publicId, $this->apiPassword],
            'headers'  => ['Content-Type' => 'application/json'],
        ]);
    }

    // -------------------------------------------------------------------------
    // Публичный API
    // -------------------------------------------------------------------------

    /** Чек прихода */
    public function cheque(Cheque $cheque)
    {
        return $this->sendCheque($cheque, 'Income');
    }

    /** Чек возврата */
    public function chequeReturn(Cheque $cheque)
    {
        return $this->sendCheque($cheque, 'IncomeReturn');
    }

    /**
     * Запрос статуса чека через API (polling).
     * Используется если webhook не настроен или нужна ручная проверка.
     */
    public function chequeStatus(Cheque $cheque)
    {
        $cp        = is_array($cheque->cloudpayments) ? $cheque->cloudpayments : [];
        $receiptId = isset($cp['Model']['Id']) ? $cp['Model']['Id'] : null;

        if (empty($receiptId)) {
            Log::warning('Cloudpayments chequeStatus: нет Model.Id для cheque #' . $cheque->id);
            return null;
        }

        try {
            $responseJson = $this->sendRequest('POST', '/kkt/receipt/status/get', [
                'Id' => $receiptId,
            ]);

            $cheque->cloudpayments = array_merge($cp, ['status_response' => $responseJson]);
            $cheque->status = $this->resolveStatus($responseJson);
            $cheque->save();

            return $responseJson;

        } catch (RequestException $e) {
            Log::error('Cloudpayments chequeStatus error: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $responseJson = json_decode((string)$e->getResponse()->getBody(), true);
                $cheque->cloudpayments = array_merge($cp, ['status_error' => $responseJson]);
                $cheque->status = Cheque::STATUS_FAILED;
                $cheque->save();
                return $responseJson;
            }
            return null;
        }
    }

    /**
     * Проверка HMAC подписи входящего webhook.
     * Ключ — apiPassword этой организации.
     */
    public function verifyHmac($rawBody, $headerHmac)
    {
        if (empty($headerHmac)) {
            return false;
        }
        $computed = base64_encode(hash_hmac('sha256', $rawBody, $this->apiPassword, true));
        return hash_equals($computed, $headerHmac);
    }

    public function getMessages()
    {
        return $this->messages;
    }

    // -------------------------------------------------------------------------
    // Внутренние методы
    // -------------------------------------------------------------------------

    protected function sendCheque(Cheque $cheque, $type)
    {
        $items = $this->buildItems($cheque);

        if (empty($items)) {
            Log::warning('Cloudpayments: нет позиций в чеке #' . $cheque->id);
            return null;
        }

        $sum = 0;
        foreach ($items as $pos) {
            $sum += $pos["amount"];
        }

        $email = 'noreply@ofd-sbis.ru';

        $data = [
            'Inn'             => $this->inn,
            'Type'            => $type,
            'InvoiceId'       => (string)$cheque->doc_num_proj,
            'AccountId'       => $email,
            'CustomerReceipt' => [
                'Items'          => $items,
                'taxationSystem' => $this->resolveTaxSystem($cheque),
                'email'          => $email,
                'phone'          => '',
                'Amounts'        => [
                    'Cash' => $sum,
                ],
            ]
        ];

        $cashierName = isset($cheque->organisation->data['cashier'])
            ? $cheque->organisation->data['cashier']
            : null;
        if (!empty($cashierName)) {
            $data['CustomerReceipt']['CashierName'] = $cashierName;
        }

        Log::debug('Cloudpayments sendCheque: ' . json_encode($data, JSON_UNESCAPED_UNICODE));

        try {
            $responseJson = $this->sendRequest('POST', '/kkt/receipt', $data);

            $cheque->cloudpayments = $responseJson;
            $cheque->status = !empty($responseJson['Success'])
                ? Cheque::STATUS_INPROGRESS
                : Cheque::STATUS_FAILED;
            $cheque->save();

            return $responseJson;

        } catch (RequestException $e) {
            Log::error('Cloudpayments sendCheque error: ' . $e->getMessage());
            if ($e->hasResponse()) {
                $responseJson = json_decode((string)$e->getResponse()->getBody(), true);
                $cheque->cloudpayments = $responseJson;
                $cheque->status = Cheque::STATUS_FAILED;
                $cheque->save();
                return $responseJson;
            }
            return null;
        }
    }

    protected function buildItems(Cheque $cheque)
    {
        $items      = [];
        $orgData    = is_array($cheque->organisation->data) ? $cheque->organisation->data : [];
        $defaultNds = isset($orgData['nds']) ? (float)$orgData['nds'] : 0.22;

        foreach ($cheque->data['positions'] as $pos) {
            if (empty($pos['nomenclature'])) {
                continue;
            }

            $price    = (float)$pos['amount'];
            $quantity = (float)$pos['quantity'];
            $amount   = round($price * $quantity, 2);
            $nds      = isset($pos['nds']) ? (float)$pos['nds'] : $defaultNds;

            $items[] = [
                'label'           => mb_substr($pos['nomenclature'], 0, 128),
                'price'           => $price,
                'quantity'        => $quantity,
                'amount'          => $amount,
                'vat'             => $this->resolveVat($nds),
                'method'          => 4, // полный расчёт
                'object'          => 1, // товар
                'measurementUnit' => 'шт',
            ];
        }

        return $items;
    }

    protected function resolveTaxSystem(Cheque $cheque)
    {
        $org = $cheque->organisation;
        if ($org && $org->is_usn15) return self::TAX_SIMPLIFIED_EXPENSE;
        if ($org && $org->is_usn6)  return self::TAX_SIMPLIFIED;
        return self::TAX_COMMON;
    }

    protected function resolveVat($nds)
    {
        $pct = (int)round($nds * 100);

        if ($pct === 0)                  return null; //0;
        if ($pct === 10)                 return 10;
        if ($pct === 20)                 return 20;
        if ($pct === 22)                 return 22;
        if (abs($nds - 10/110) < 0.001) return 110;
        if (abs($nds - 20/120) < 0.001) return 120;
        if (abs($nds - 22/122) < 0.001) return 122;
        return -1;
    }

    protected function resolveStatus($responseJson)
    {
        $model = isset($responseJson['Model']) ? $responseJson['Model'] : null;

        if (is_array($model)) {
            $s = isset($model['Status']) ? $model['Status'] : null;
            if (in_array($s, ['Complete', 2], true))               return Cheque::STATUS_SUCCESS;
            if (in_array($s, ['Error', 3], true))                  return Cheque::STATUS_FAILED;
            if (in_array($s, ['Wait', 'InProgress', 0, 1], true))  return Cheque::STATUS_INPROGRESS;
        }

        if (is_string($model) && $model === 'Error') return Cheque::STATUS_FAILED;

        return Cheque::STATUS_INPROGRESS;
    }

    protected function sendRequest($method, $uri, $data)
    {
        $options = empty($data) ? [] : ['json' => $data];

        $response     = $this->client->request($method, $uri, $options);
        $responseJson = json_decode((string)$response->getBody(), true);

        if (!is_array($responseJson)) {
            $responseJson = [];
        }

        $this->messages[] = [
            'time'     => date('Y-m-d H:i:s'),
            'url'      => strtoupper($method) . ' ' . $uri,
            'request'  => $data,
            'response' => $responseJson,
        ];

        Log::info(
            'Cloudpayments ' . strtoupper($method) . ' ' . $uri
            . "\nRequest:\n"  . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            . "\nResponse:\n" . json_encode($responseJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $responseJson;
    }
}
