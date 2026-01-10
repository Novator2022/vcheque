<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExportCheque extends Model
{
    protected $connection = 'market';
    protected $table = 'cheques';  

    protected $primaryKey = 'cheque_id';

    protected $fillable = [
        'disk_id', 'name', 'date', 'year', 'kv', 'status', 'sync_flag', 'filename', 'filesize', 'blurred_filename', 'pdf_filename', 'qr_coords', 'manual_coords', 'total', 'categories', 'company_name', 'is_usn', 'company_id', 'external_id', 'external_source', 'external_schedule_id', 'external_category_id'
    ];

    protected $casts = [
        'categories' => 'array',
    ];

    public function products() {
        return $this->hasMany('App\ExportChequeProduct', 'cheque_id');
    }

    private static function getBlurImage($key, $text) {
        $tempDir = storage_path('blurred_tpls/');

        if (in_array($key, ['Кассир'])) {
            $cache_key = $key . '_' . $text;
        } else {
            $cache_key = mb_strlen($text);
        }

        $cache_key = md5($cache_key);

        if (!file_exists($tempDir . $cache_key . '.png')) {
            // Создаем новый объект Imagick
            $image = new \Imagick();

            // Устанавливаем размеры изображения
            $width = 300;
            $height = 16;
            $image->newImage($width, $height, new \ImagickPixel('white'));

            // Устанавливаем параметры текста
            $draw = new \ImagickDraw();
            $draw->setFontSize(14);
            $draw->setFillColor('black');
            $draw->setGravity(3);

            // Рисуем текст на изображении
            $image->annotateImage($draw, 0, 0, 0, $text);

            // Применяем размытие
            // $image->blurImage(5, 5);
            $image->motionBlurImage(15, 20, 0);
            // $image->gaussianBlurImage(5, 5);

            // Устанавливаем формат PNG
            $image->setImageFormat('png');

            file_put_contents($tempDir . $cache_key . '.png', $image);

            return $image;
        } else {
            return file_get_contents($tempDir . $cache_key . '.png');
        }
    }

    public static function get_files($cheque_id) {
        // pdf для картинки - замазанный
        include_once(base_path('vendor/qr/qrlib.php'));
        $cheque = Cheque::find($cheque_id);

        ob_start();
        \QRcode::png($cheque->modulkassa['fiscalInfo']['qr'], false, QR_ECLEVEL_L, 4);
        $qr = base64_encode(ob_get_clean());

        $w = 150;

        require_once base_path('app/Http/Controllers/vendor/autoload.php');

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
        
        foreach ([
            'Кассир' => $cheque->organisation->data['cashier'],
            'Номер смены' => $cheque->modulkassa['fiscalInfo']['shiftNumber'],
            'Номер ФД' => $cheque->modulkassa['fiscalInfo']['fnDocNumber'],
            'ФПД' => $cheque->modulkassa['fiscalInfo']['fnDocMark'],
            'Номер ФД в смене' => $cheque->modulkassa['fiscalInfo']['checkNumber'],
            'Рег. номер ККТ в ФНС' => $cheque->modulkassa['fiscalInfo']['ecrRegistrationNumber'],
            'Сер. номер ККТ' => $cheque->modulkassa['fiscalInfo']['kktNumber'],
            'Сер. номер ФН' => $cheque->modulkassa['fiscalInfo']['fnNumber'],
            'Версия ФФД' => '1.05',
            'Адрес сайта ФНС' => 'https://www.nalog.ru/',
            'Название ОФД' => 'Яндекс.ОФД',
            'Сайт ОФД для проверки чека' => 'https://ofd.yandex.ru/check',
            'ИНН ОФД' => '7704358518',
        ] as $var => $value) {
            $mpdf->imageVars[$var] = self::getBlurImage($var, $value);
        }

        $mpdf->imageVars['QR'] = file_get_contents(resource_path('views/cheque/qr_blurred.png'));

        /* шаг 1 - заливаем html */
        $mpdf->WriteHTML(view('cheque.view_blurred', ['qr' => $qr, 'cheque' => $cheque]));

        $mpdf->showImageErrors = true.

        // вывод
        $pdf = $mpdf->Output($cheque_id . '.pdf', 'S');
        // /pdf для картинки - замазанный

        // картинка замазанная
        $im = new \Imagick();
        $im->setResolution(150, 150);
        $im->setBackgroundColor('white');
        $im->readImageBlob($pdf);
        $im->trimImage(0);
        $im->setImageAlphaChannel(\Imagick::VIRTUALPIXELMETHOD_WHITE);
        $im->setImageFormat('png');
        // /картинка замазанная

        // полный пдф
        ob_start();
        \QRcode::png($cheque->modulkassa['fiscalInfo']['qr'], false, QR_ECLEVEL_L, 4);
        $qr = base64_encode(ob_get_clean());

        $postfix = '<br/><br/>-';

        $w = 75;

        unset($mpdf);

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
        $pdf2 = $mpdf->Output($cheque_id . '.pdf', 'S');
        // /полный пдф

        header_remove();

        return [
            'pdf' => $pdf,
            'pdf2' => $pdf2,
            'png' => $im,
        ];
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
