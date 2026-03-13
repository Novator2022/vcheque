<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cheque;
use App\Services\Cloudpayments;
use Log;

/**
 * Webhook-контроллер для Receipt-уведомлений от CloudKassir.
 *
 * Настройка в ЛК CloudPayments:
 *   Настройки сайта → Уведомления → Receipt → POST
 *   → https://yourdomain.ru/webhooks/cloudpayments/receipt
 *
 * Формат входящего запроса: POST application/x-www-form-urlencoded
 * Подпись: X-Content-HMAC = base64(HMAC-SHA256(rawBody, apiPassword))
 * Ожидаемый ответ: тело "0", код 200.
 * При любом другом ответе — 100 повторных попыток (1, 2, 5, 10, 30 мин).
 */
class CloudpaymentsWebhookController extends Controller
{
    public function receipt(Request $request)
    {
        $rawBody = $request->getContent();

        Log::info('CloudpaymentsWebhook income: ' . $rawBody);

        // 1. Парсим тело — нужен InvoiceId чтобы найти чек и организацию
        parse_str($rawBody, $data);

        $invoiceId = isset($data['InvoiceId']) ? $data['InvoiceId'] : null;

        if (empty($invoiceId)) {
            Log::warning('CloudpaymentsWebhook: нет InvoiceId', $data);
            return $this->ok();
        }

        // 2. Ищем чек по doc_num = "{organisation_id}-{cheque_id}"
        //    getDocNumAttribute() возвращает именно такой формат
        $cheque = Cheque::with('organisation')
            ->whereRaw("CONCAT(organisation_id, '-', id) = ?", [$invoiceId])
            ->first();

        if (!$cheque) {
            Log::warning('CloudpaymentsWebhook: чек не найден, InvoiceId=' . $invoiceId);
            return $this->ok();
        }

        // 3. Создаём сервис с ключами из organisation->data['cloudpayments']
        //    и проверяем HMAC подпись
        try {
            $service = Cloudpayments::fromCheque($cheque);
        } catch (\RuntimeException $e) {
            Log::error('CloudpaymentsWebhook: ' . $e->getMessage());
            return $this->ok();
        }

        if (!$service->verifyHmac($rawBody, $request->header('X-Content-HMAC'))) {
            Log::warning('CloudpaymentsWebhook: неверная HMAC-подпись для cheque #' . $cheque->id);
            return $this->ok();
        }

        // 4. Сохраняем фискальные данные и обновляем статус
        $cp = is_array($cheque->cloudpayments) ? $cheque->cloudpayments : [];

        $cheque->cloudpayments = array_merge($cp, ['receipt_notification' => $data]);
        $cheque->status = Cheque::STATUS_SUCCESS;
        $cheque->save();

        Log::info(sprintf(
            'CloudpaymentsWebhook: чек #%s фискализирован. DocumentNumber=%s FiscalSign=%s OfdReceiptUrl=%s',
            $cheque->id,
            isset($data['DocumentNumber']) ? $data['DocumentNumber'] : '-',
            isset($data['FiscalSign'])     ? $data['FiscalSign']     : '-',
            isset($data['OfdReceiptUrl'])  ? $data['OfdReceiptUrl']  : '-'
        ));

        return $this->ok();
    }

    /**
     * CloudKassir ожидает "0" в теле ответа как подтверждение успешного приёма.
     */
    protected function ok()
    {
        return response('0', 200)->header('Content-Type', 'text/plain');
    }
}
