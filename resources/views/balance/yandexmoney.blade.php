<html>
	<body onload="document.form.submit()">
		<form method="POST" action="https://money.yandex.ru/quickpay/confirm.xml" name="form" target="_parent">
			<input type="hidden" name="receiver" value="{{ config('money.yandex_money_id') }}">
			<input type="hidden" name="label" value="{{ $order_id }}">
			<input type="hidden" name="quickpay-form" value="donate">
			<input type="hidden" name="targets" value="Пополнение счета пользователя {{ $user->email }}">
			<input type="hidden" name="sum" value="{{ $sum }}" data-type="number">
			<input type="hidden" name="need-fio" value="false">
			<input type="hidden" name="need-email" value="false">
			<input type="hidden" name="need-phone" value="false">
			<input type="hidden" name="need-address" value="false">
			<input type="hidden" name="paymentType" value="AC">

			<input type="hidden" name="successURL" value="{{ config('app.url') }}/home/balance/">
		</form>
	</body>
</html>