<?php

namespace App\Exports;

use App\ExpenseType;
use App\Organisation;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ChequeScheduleTemplateExport implements FromCollection, WithHeadings, WithEvents //, ShouldAutoSize
{
    public function collection()
    {
        return collect([[1], [2], [3]]);
    }

    public function headings(): array
    {
        return [
            ['Заявка на получение чеков по расписанию'],
            [
                "№ чека",
                'Номенклатура',
                'Единица измерения товара',
                'Кол-во товара',
                "Цена за единицу товара с НДС/Без НДС",
                'Сумма общая за товар с НДС/Без НДС',
                'Расписание печати чеков (диапазон дат, включительно)',
                '',
                'Периодичность',
                'Организация',
                'Контрагент',
                'Дать номер счёт-фактуры (дополнительная стоимость!)',
                'Электронная почта',
            ],
            [
                '',
                '',
                '',
                '',
                '',
                '',
                'с',
                'по',
                '',
                '',
                '',
                '',
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'I' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $cellRange = 'A1:R2'; // All headers
                $sheet = $event->sheet->getDelegate();
                $sheet->setTitle('mainsheet');
                $this->styles($sheet);

                // Styling
                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:A3');
                $sheet->mergeCells('B2:B3');
                $sheet->mergeCells('C2:C3');
                $sheet->mergeCells('D2:D3');
                $sheet->mergeCells('E2:E3');
                $sheet->mergeCells('F2:F3');
                $sheet->mergeCells('G2:H2');
                $sheet->mergeCells('I2:I3');
                $sheet->mergeCells('J2:J3');
                $sheet->mergeCells('K2:K3');
                $sheet->mergeCells('L2:L3');
                $sheet->mergeCells('M2:M3');

                $this->datatypeCells($sheet, 'a', 4, 13, DataType::TYPE_NUMERIC);
                $this->datatypeCells($sheet, 'g', 4, 13, NumberFormat::FORMAT_DATE_DATETIME);
                $this->datatypeCells($sheet, 'h', 4, 13, NumberFormat::FORMAT_DATE_DATETIME);

                $sheet->mergeCells('G4:G17');
                $sheet->mergeCells('H4:H17');
                $sheet->mergeCells('I4:I17');
                $sheet->mergeCells('J4:J17');
                $sheet->mergeCells('K4:K17');
                $sheet->mergeCells('L4:L17');
                $sheet->mergeCells('M4:M17');

                $sheet->mergeCells('A18:D18');
                $sheet->mergeCells('G18:M18');

                $sheet->mergeCells('P1:T1');

                $this->setYesNoValidation($sheet);
                $this->setPeriodicValidation($sheet);
                $this->setOrganisationValidation($sheet);
                $this->setDeliveryValidation($sheet);


                $this->initialCells($sheet);
            },
        ];
    }
    protected function initialCells($sheet)
    {
        $initialRowsCount = 13;

        $this->fillCells($sheet, 'a', 4, $initialRowsCount, 1);
        // formulas
        $sheet->getCell('F4')->setValue('=E4*D4');
        $sheet->getCell('F5')->setValue('=E5*D5');
        $sheet->getCell('F6')->setValue('=E6*D6');
        $sheet->getCell('F7')->setValue('=E7*D7');
        $sheet->getCell('F8')->setValue('=E8*D8');
        $sheet->getCell('F9')->setValue('=E9*D9');
        $sheet->getCell('F10')->setValue('=E10*D10');
        $sheet->getCell('F11')->setValue('=E11*D11');
        $sheet->getCell('F12')->setValue('=E12*D12');
        $sheet->getCell('F13')->setValue('=E13*D13');
        $sheet->getCell('F14')->setValue('=E14*D14');
        $sheet->getCell('F15')->setValue('=E15*D15');
        $sheet->getCell('F16')->setValue('=E16*D16');
        $sheet->getCell('F17')->setValue('=E17*D17');
        $sheet->getCell('F18')->setValue('=SUM(F4:F17)');

        // finishing row
        $sheet->getCell('A18')->setValue('Итого');

        //delivery table
        $sheet->getCell('P1')->setValue('Данные для отправки чека');

        $sheet->getCell('P2')->setValue('Способ доставки');
        $sheet->getCell('Q2')->setValue('ФИО');
        $sheet->getCell('R2')->setValue('Индекс');
        $sheet->getCell('S2')->setValue('Адрес');
        $sheet->getCell('T2')->setValue('Телефон');
    }

    protected function fillCells($sheet, $column, $from, $count, $value)
    {
        for ($i = $from; $i <= ($count + $from); ++$i) {
            $val = $value;
            $pattern = '//';
            if (preg_match($pattern, $val)) {
                // preg_replace($pattern)
            }
            $sheet->getCell(mb_strtoupper($column) . $i)->setValue($val);
        }
    }

    protected function datatypeCells($sheet, $column, $from, $count, $datatype)
    {
        for ($i = $from; $i <= ($count + $from); ++$i) {
            $sheet->getCell(mb_strtoupper($column) . $i)->setDataType($datatype);
        }
    }
    protected function validationCells($sheet, $column, $from, $count, $validation)
    {
        for ($i = $from; $i <= ($count + $from); ++$i) {
            $sheet->getCell(mb_strtoupper($column) . $i)->setDataValidation(clone $validation);
        }
    }
    protected function styles($sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setVisible(false);//->setWidth(20);
        // $sheet->getColumnDimension('M')->setVisible(false);
        $sheet->getColumnDimension('M')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(20);
        $sheet->getColumnDimension('Q')->setWidth(20);
        $sheet->getColumnDimension('R')->setWidth(16);
        $sheet->getColumnDimension('S')->setWidth(20);
        $sheet->getColumnDimension('T')->setWidth(16);

        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'family'     => 'Calibri',
                'size'       => '16',
                'bold'       =>  true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'c3d69b']
            ]
            // fill
        ]);
        $sheet->getRowDimension('1')->setRowHeight(60);

        $sheet->getStyle('A2:M3')->applyFromArray([
            'font' => [
                'family'     => 'Calibri',
                'size'       => '11',
                'bold'       =>  true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'd9d9d9']
            ],
            // fill
        ]);
        $sheet->getRowDimension('2')->setRowHeight(60);
        $sheet->getStyle('A4:T17')->applyFromArray([
            'font' => [
                'family'     => 'Calibri',
                'size'       => '9',
                'bold'       =>  false
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'wrapText' => true
            ],

        ]);
        $sheet->getStyle('A4:M17')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);
        // $sheet->getStyle('A5:M5')->applyFromArray([
        //     'borders' => [
        //         'outline' => [
        //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //             'color' => ['argb' => '000000'],
        //         ],
        //     ],
        // ]);
        // $sheet->getStyle('A6:M6')->applyFromArray([
        //     'borders' => [
        //         'outline' => [
        //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //             'color' => ['argb' => '000000'],
        //         ],
        //     ],
        // ]);
        $sheet->getStyle('A18:M18')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'font' => [
                'family'     => 'Calibri',
                'size'       => '14',
                'bold'       =>  true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
        ]);

        $sheet->getStyle('P1:T1')->applyFromArray([
            'font' => [
                'family'     => 'Calibri',
                'size'       => '11',
                'bold'       =>  true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'd99694']
            ]
            // fill
        ]);

        $sheet->getStyle('P2:T2')->applyFromArray([
            'font' => [
                'family'     => 'Calibri',
                'size'       => '11',
                'bold'       =>  true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'd9d9d9']
            ]
        ]);
        $sheet->getStyle('P3:T3')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => '000000'],
                ],
            ],
            // fill d9d9d9
        ]);

        $sheet->getStyle('G4:M4')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
        ]);
    }
    protected function setOrganisationValidation($sheet)
    {
        $organisations = Organisation::all();
        $data = [];
        $i = 0;
        foreach ($organisations as $organisation) {
            $name = preg_replace('/["\'\n\r\t]/ui', '', $organisation->name);
            $inn = empty($organisation->data['inn']) ? '' : ' - ' . $organisation->data['inn'];
            $data[] = $name . $inn;
            $sheet->getCell('Z' . (++$i))->setValue($name . $inn);
        }
        // $sheet->getColumnDimension('Z')->setWidth(1);

        $validation = $sheet->getCell("J4")->getDataValidation();

        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Некорректное значение');
        $validation->setError('Выберете один вариант');
        $validation->setPromptTitle('Выберете организацию');
        $validation->setPrompt('Из предложенного списка');


        $str = sprintf('"%s"', implode(',', $data));
        // $validation->setFormula1($str);
        $validation->setFormula1('$Z$1:$Z$' . $i);


        // $sheet->getCell("J4")->setDataValidation(clone $validation);
    }

    protected function setPeriodicValidation($sheet)
    {
        $validation = $sheet->getCell("I4")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Некорректное значение');
        $validation->setError('Выберете периодичность');
        $validation->setPromptTitle('Выберете периодичность');
        $validation->setPrompt('Из предложенного списка');
        $validation->setFormula1(sprintf('"%s"', implode(',', ['Ежедневно', 'Еженедельно', 'Ежемесячно'])));

        $sheet->getCell("I4")->setDataValidation(clone $validation);
    }
    protected function setYesNoValidation($sheet)
    {
        $validation = $sheet->getCell("L4")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Некорректное значение');
        $validation->setError('Выберете Да или Нет');
        $validation->setPromptTitle('Отметьте');
        $validation->setPrompt('Да или Нет');
        $validation->setFormula1(sprintf('"%s"', implode(',', ['Да', 'Нет'])));

        $sheet->getCell("L4")->setDataValidation(clone $validation);
        // $sheet->getCell("M4")->setDataValidation(clone $validation);
    }
    protected function setDeliveryValidation($sheet)
    {
        $validation = $sheet->getCell("P3")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Некорректное значение');
        $validation->setError('Выберете способ доставки');
        $validation->setPromptTitle('Отметьте');
        $validation->setPrompt('Способ доставки');
        $validation->setFormula1(sprintf('"%s"', implode(',', ['Почта РФ', 'Курьерская служба'])));

        $sheet->getCell("P3")->setDataValidation(clone $validation);
    }
}
