<!DOCTYPE html>
<html lang="en">
<head>
	<title>кассовый чек / приход</title>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=450">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="date=no">
	<style type="text/css">
		html, body {
			padding: 0;
			margin: 0;
		}
	</style>
</head>
<body>
	<?php /*<pre style="position:fixed;left:10px;top:10px; border:1px solid #ccc;padding: 10px; background: #fff;opacity:0.5"><?php print_r($cheque->organisation); ?><?php print_r($cheque->modulkassa); ?></pre>*/ ?>
	<div class="wrapper" style="background-color:white; font-weight:400; margin:0 auto; padding:15px 30px 15px 0; max-width: 450px;" bgcolor="white">
		<h6 class="long_text">&nbsp;</h6>
		<table style='border:0; font-family:"HelveticaNeue", "Helvetica", "Arial", "sans-serif"; font-size:14px; margin:6px 0; width:100%' width="100%">
			<tr class="doc-type" style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">
					кассовый чек
					/ приход
				</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print date('d.m.Y H:i', strtotime($cheque->modulkassa['fiscalInfo']['date'])); ?></td>
			</tr>
		</table>

		<br/>

		<h2 class="user long_text" style="text-align:center; overflow-wrap:break-word; white-space:normal; font-family: 'HelveticaNeue', 'Helvetica', 'Arial', 'sans-serif'; margin: 0; font-size:20px" align="center"><?php print $cheque->organisation->name; ?></h2>
		
		<div class="retail-point long_text" style="text-align:center; overflow-wrap:break-word; white-space:normal;" align="center">
			<h3 style='font-family: "HelveticaNeue", "Helvetica", "Arial", "sans-serif"; margin: 0; font-size: 16px;'><?php print $cheque->organisation->data['address']; ?></h3>
			<h3 style='font-family: "HelveticaNeue", "Helvetica", "Arial", "sans-serif"; margin: 0; font-size: 16px;'><?php print $cheque->organisation->data['address']; ?></h3>
		</div>
		
		<table style='border:0; font-family:"HelveticaNeue", "Helvetica", "Arial", "sans-serif"; font-size:14px; margin:6px 0; width:100%' width="100%">
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">ИНН</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->organisation->data['inn']; ?></td>
			</tr>
			
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Налогообложение</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php if ($cheque->organisation->is_usn15) { ?>УСН доход - расход<?php } else { ?>ОСН<?php } ?></td>
			</tr>
		</table>
		
		<table style='border:0; font-family:"HelveticaNeue", "Helvetica", "Arial", "sans-serif"; font-size:14px; margin:6px 0; width:100%' width="100%">
			<tr style="line-height:20px">
				<th style="text-align:left" align="left">№</th>
				<th style="text-align:left">Наименование</th>
				<th style="text-align:left">Сумма</th>
			</tr>

			<?php $ind = 1; ?>
			<?php $nds = 0.2; ?>
			<?php foreach ($cheque->data['positions'] as $item) { ?>
				<?php $nds = $item['nds'] * 100; ?>
				<tr style="line-height:20px">
					<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top"><?php print $ind; ?>.</td>
					<td class="text_left" style="padding:0 3px 0 0; text-align:left; vertical-align:top; width:60%" align="left" valign="top" width="60%"><?php print $item['nomenclature']; ?></td>
					<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top">
						<?php print number_format($item['amount'], 2, '.', ''); ?>₽ × <?php print $item['quantity']; ?> = <?php print number_format($item['amount'] * $item['quantity'], 2, '.', ''); ?>₽
					</td>
				</tr>
				<?php if ($cheque->organisation->is_usn15) { ?>
					<tr style="line-height:20px">
	                    <td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top"></td>
	                    <td class="text_left" style="padding:0 3px 0 0; text-align:left; vertical-align:top" align="left" valign="top">Сумма без НДС</td>
	                    <td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"></td>
	                </tr>
				<?php } else { ?>
					<tr style="line-height:20px">
						<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top"></td>
						<td class="text_left" style="padding:0 3px 0 0; text-align:left; vertical-align:top" align="left" valign="top">В том числе НДС 20%</td>
						<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top">
							<?php print number_format(($item['amount'] * $item['quantity']) / ($nds + 100) * $nds, 2, '.', ''); ?>₽
						</td>
					</tr>
				<?php } ?>

				<tr>
					<td style="padding:0 3px 0 0; text-align:right; vertical-align:top" align="right" valign="top"></td>
					<td class="text_left" style="padding:0 3px 0 0; text-align:left; vertical-align:top" align="left" valign="top">Товар / Полный расчет</td>
					<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"></td>
				</tr>
				<?php $ind++; ?>
			<?php } ?>
		</table>

		<br>
		<table style='border:0; font-family:"HelveticaNeue", "Helvetica", "Arial", "sans-serif"; font-size:14px; margin:6px 0; width:100%' width="100%">
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0; font-weight:bold" align="left" valign="top">Итого</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print number_format($cheque->modulkassa['fiscalInfo']['sum'], 2, '.', ''); ?>₽</td>
			</tr>
			<?php if ($cheque->organisation->is_usn15) { ?>
				<tr class="comment" style="line-height:20px; color:#8f8f8f">
		            <td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Сумма без НДС</td>
		            <td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print number_format($cheque->modulkassa['fiscalInfo']['sum'], 2, '.', ''); ?>₽</td>
		        </tr>
			<?php } else { ?>
				<tr class="comment" style="line-height:20px; color:#8f8f8f">
					<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">В том числе НДС 20%</td>
					<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print number_format($cheque->modulkassa['fiscalInfo']['sum'] / ($nds + 100) * $nds, 2, '.', ''); ?>₽</td>
				</tr>
			<?php } ?>
		</table>

		<table style='border:0; font-family:"HelveticaNeue", "Helvetica", "Arial", "sans-serif"; font-size:14px; margin:6px 0; width:100%' width="100%">
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Наличными</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print number_format($cheque->modulkassa['fiscalInfo']['sum'], 2, '.', ''); ?>₽</td>
			</tr>
		</table>

		<br>

		<br>

		<table style='border:0; font-family:"HelveticaNeue", "Helvetica", "Arial", "sans-serif"; font-size:14px; margin:6px 0; width:100%' width="100%">
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Кассир</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->organisation->data['cashier']; ?></td>
			</tr>
			
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Номер смены</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->modulkassa['fiscalInfo']['shiftNumber']; ?></td>
			</tr>
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Номер ФД</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->modulkassa['fiscalInfo']['fnDocNumber']; ?></td>
			</tr>
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">ФПД: </td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->modulkassa['fiscalInfo']['fnDocMark']; ?></td>
			</tr>
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Номер ФД в смене</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->modulkassa['fiscalInfo']['checkNumber']; ?></td>
			</tr>
		</table>

		<table style='border:0; font-family:"HelveticaNeue", "Helvetica", "Arial", "sans-serif"; font-size:14px; margin:6px 0; width:100%' width="100%">
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Рег. номер ККТ в ФНС</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->modulkassa['fiscalInfo']['ecrRegistrationNumber']; ?></td>
			</tr>
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Сер. номер ККТ</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->modulkassa['fiscalInfo']['kktNumber']; ?></td>
			</tr>
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Сер. номер ФН</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top"><?php print $cheque->modulkassa['fiscalInfo']['fnNumber']; ?></td>
			</tr>

			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Версия ФФД</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top">1.05</td>
			</tr>
		</table>

		<?php if (!empty($cheque->email)) { ?>
			<table style="border:0; font-family:&quot;HelveticaNeue&quot;, &quot;Helvetica&quot;, &quot;Arial&quot;, &quot;sans-serif&quot;; font-size:14px; margin:6px 0; width:100%" width="100%">
	            <tbody>
		        	<tr style="line-height:20px">
		                <td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0; padding-right:0" align="left" valign="top">Эл. адрес покупателя:</td>
		            </tr>
		            <tr style="line-height:20px">
		                <td class="long_text" style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0; padding-right:0; overflow-wrap:break-word; white-space:normal" align="left" valign="top"><?php print $cheque->email; ?></td>
		            </tr>
		            <tr style="line-height:20px"></tr>
		            <tr style="line-height:20px">
		                <td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0; padding-right:0" align="left" valign="top">Эл. адрес отправителя:</td>
		            </tr>
		            <tr style="line-height:20px">
		                <td class="long_text" style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0; padding-right:0; overflow-wrap:break-word; white-space:normal" align="left" valign="top">
		                    <a href="https://ofd.yandex.ru/check">no-reply@ofd.yandex.ru</a>
		                </td>
		            </tr>
		        </tbody>
		    </table>
		<?php } ?>

		<table style='border:0; font-family:"HelveticaNeue", "Helvetica", "Arial", "sans-serif"; font-size:14px; margin:6px 0; width:100%' width="100%">
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Адрес сайта ФНС</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top">
					<a href="https://www.nalog.ru/">https://www.nalog.ru/</a>
				</td>
			</tr>
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Название ОФД</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top">Яндекс.ОФД</td>
			</tr>
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">Сайт ОФД для проверки чека</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top">
					<a href="https://ofd.yandex.ru/check">https://ofd.yandex.ru/check</a>
				</td>
			</tr>
			<tr style="line-height:20px">
				<td style="padding:0 3px 0 0; text-align:left; vertical-align:top; padding-left:0" align="left" valign="top">ИНН ОФД</td>
				<td style="padding:0 3px 0 0; text-align:right; vertical-align:top; padding-right:0" align="right" valign="top">7704358518</td>
			</tr>
		</table>

		<br><br>
		<div style="text-align: center; margin-bottom: 30px;">
			<img src="data:image/png;base64,<?php print $qr; ?>" alt="" style="width: 120px">
		</div>
	</div>

	<script type="text/javascript">
		parent.document.getElementById('chequeFrame').style.height = (document['body'].offsetHeight + 2) + 'px';
	</script>
</body>
</html>