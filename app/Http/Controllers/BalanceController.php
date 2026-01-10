<?php

namespace App\Http\Controllers;

use Storage;
use Redirect;

use Illuminate\Http\Request;
use App\Exports\SimpleXml;
use App\Exports\ChequeXML;
use App\Cheque;
use App\Param;
use App\Orders;
use App\Transactions;
use Auth;
use App\User;

class BalanceController extends Controller
{
    public function index()
    {
        return view('balance.index');
    }

    public function by_method(Request $request, $method)
    {
        if ($method == 'yandexmoney') {
            $sum = (float)$request->get('sum');

            $sum /= 0.98;

            $order = Orders::create(['user_id' => Auth::id(), 'sum' => $sum]);
            return view('balance.yandexmoney', array('order_id' => $order->id, 'sum' => $sum, 'user' => Auth::user()));
        }
    }

    public function notify_yandex_money(Request $request)
    {
        // file_put_contents(__DIR__ . '/notify_yandex_money.log', print_r($request->all(), 1), 8);
        $params = array(
            $request->get('notification_type'),
            $request->get('operation_id'),
            $request->get('amount'),
            $request->get('currency'),
            $request->get('datetime'),
            $request->get('sender'),
            $request->get('codepro'),
            trim(config('money.yandex_money_secret')),
            $request->get('label'),
        );

        $sha1 = sha1(implode($params, '&'));

        // уведомление от яндекс денег - по безопасности все ок
        if ($sha1 == $request->get('sha1_hash')) {
            // сразу запишем в логи
            if ($request->has('label')) {
                $order = Orders::where('id', $request->get('label'))->firstOrFail();

                if ($order->status == 'pending') {
                    // меняем статус заказа чтобы повторно его случайно не зачислить
                    $order->update(['status' => 'complete']); //, 'log' => print_r($request->all(), 1)]);

                    $user = User::where('id', $order->user_id)->first();

                    $sum = $order->sum * 0.98;

                    // зачисляем платеж
                    Transactions::create(['user_id' => $order->user_id, 'amount' => (float)$sum, 'description' => 'Зачисление оплаты через Яндекс.Деньги (id операции ' . $request->get('operation_id') . ')']);

                    // обновляем баланс
                    $user->update(['balance' => Transactions::where('user_id', $order->user_id)->sum('amount')]);
                }
            }

            print 'OK';
        } else {
            print 'ERROR';
        }
    }
}
