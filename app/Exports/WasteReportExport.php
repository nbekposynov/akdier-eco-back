<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class WasteReportExport implements FromArray, WithStyles, WithEvents, WithTitle
{
    protected $report;
    protected $columnCount = 0;
    protected $rowCount = 0;

    // Обновляем цветовую схему для лучшей контрастности
    protected $colors = [
        'header_bg' => '1F4E78',     // Темно-синий для заголовков
        'subheader_bg' => '4472C4',  // Средне-синий
        'total_bg' => '8EAADB',      // Более насыщенный голубой для итогов категорий
        'border' => '8EA9DB',
        'company_bg' => 'F5F9FF',    // Светло-голубой фон для четных строк
        'company_alt_bg' => 'FFFFFF', // Белый фон для нечетных строк
        'highlight' => 'FFC000',     // Желтый для выделения важных данных
    ];

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function title(): string
    {
        // Формируем название листа с датами
        $filters = $this->report['filters'] ?? [];
        $dateRange = "";

        if (isset($filters['start_date'], $filters['end_date'])) {
            $from = date('d.m.Y', strtotime($filters['start_date']));
            $to = date('d.m.Y', strtotime($filters['end_date']));
            $dateRange = "({$from}-{$to})";
        }

        return "Отчет по отходам {$dateRange}";
    }

    public function array(): array
    {
        // Проверка наличия данных
        if (empty($this->report['headers']) || empty($this->report['companies'])) {
            return [['Нет данных для отображения']];
        }

        $data = [];

        // Добавляем заголовок отчета
        $data[] = ['СВОДНЫЙ ОТЧЕТ ПО ОТХОДАМ'];

        // Добавляем информацию о периоде отчета сразу после заголовка
        if (isset($this->report['filters']['start_date'], $this->report['filters']['end_date'])) {
            $from = date('d.m.Y', strtotime($this->report['filters']['start_date']));
            $to = date('d.m.Y', strtotime($this->report['filters']['end_date']));
            $data[] = ["Отчетный период: с {$from} по {$to}"];
        }

        // Указываем единицы измерения после периода
        $data[] = ['Все измерения приведены в кубических метрах (м³)'];
        $data[] = [''];

        // Первая строка заголовков (категории)
        $headers1 = ['Компания'];
        // Вторая строка заголовков (отходы)
        $headers2 = [''];

        // Формируем заголовки с категориями и типами отходов
        // Теперь "Итого" будет после всех типов отходов в категории
        foreach ($this->report['headers'] as $category => $info) {
            // Пропускаем пустые категории
            if (empty($info['wastes'])) {
                continue;
            }

            $headers1[] = $category;
            $headers1 = array_merge($headers1, array_fill(0, $info['colspan'] - 1, ''));

            // Сначала добавляем все типы отходов
            foreach ($info['wastes'] as $waste) {
                $headers2[] = $waste;
            }

            // Затем добавляем итого
            $headers2[] = 'Итого (м³)';
        }

        $data[] = $headers1;
        $data[] = $headers2;

        // Добавляем данные компаний
        $rowIndex = 0;
        foreach ($this->report['companies'] as $company) {
            $row = [$company['name']];
            $rowTotal = 0; // Общий итог по компании

            foreach ($this->report['headers'] as $category => $info) {
                // Пропускаем пустые категории
                if (empty($info['wastes'])) {
                    continue;
                }

                $categoryTotal = $company[$category . '_total'] ?? 0;

                // Сначала добавляем значения для всех типов отходов
                foreach ($info['wastes'] as $waste) {
                    $row[] = $company[$waste] ?? 0;
                }

                // Затем добавляем итого по категории
                $row[] = $categoryTotal;
                $rowTotal += $categoryTotal;
            }

            // Добавляем общий итог компании
            $row[] = $rowTotal;
            $data[] = $row;
            $rowIndex++;
        }

        // Добавляем итоговую строку
        $totalRow = ['ИТОГО ПО ВСЕМ КОМПАНИЯМ:'];
        $grandTotal = 0;

        // Начиная со второй колонки (пропускаем "Компания")
        for ($col = 1; $col < count($headers2); $col++) {
            $sum = 0;
            // Суммируем значения по колонке (начиная с первой строки данных)
            for ($row = 6; $row < 6 + count($this->report['companies']); $row++) {
                $sum += isset($data[$row][$col]) ? $data[$row][$col] : 0;
            }
            $totalRow[] = $sum;

            // Обновляем логику для итоговых сумм
            // Проверяем, что это колонка итогов категории по названию заголовка
            if ($col > 0 && isset($headers2[$col]) && strpos($headers2[$col], 'Итого') === 0) {
                $grandTotal += $sum;
            }
        }

        // Добавляем общий итог
        $totalRow[] = $grandTotal;
        $data[] = $totalRow;

        $this->columnCount = count($headers2) + 1; // +1 для общего итога
        $this->rowCount = count($data);

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = Coordinate::stringFromColumnIndex($this->columnCount);

                // Стилизуем заголовок отчета
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
                $sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Стилизуем информацию о периоде (теперь она на второй строке)
                if (isset($this->report['filters']['start_date'])) {
                    $sheet->mergeCells("A2:{$maxCol}2");
                    $sheet->getStyle('A2')->getFont()->setBold(true);
                    $sheet->getStyle('A2')->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Стилизуем информацию о единицах измерения (теперь на третьей строке)
                $sheet->mergeCells("A3:{$maxCol}3");
                $sheet->getStyle('A3')->getFont()->setSize(12)->setItalic(true);
                $sheet->getStyle('A3')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Заголовки категорий
                $col = 1;
                foreach ($this->report['headers'] as $category => $info) {
                    if (empty($info['wastes'])) continue;

                    $startCol = Coordinate::stringFromColumnIndex($col + 1);
                    $endCol = Coordinate::stringFromColumnIndex($col + $info['colspan']);
                    $sheet->mergeCells("{$startCol}5:{$endCol}5");
                    $col += $info['colspan'];
                }

                // Добавляем колонку общего итога и стилизуем ее
                $sheet->setCellValue("{$maxCol}5", "ОБЩИЙ ИТОГ");
                $sheet->setCellValue("{$maxCol}6", "ИТОГО (м³)");
                $sheet->getStyle("{$maxCol}5:{$maxCol}6")->getFont()->setBold(true);
                $sheet->getStyle("{$maxCol}5:{$maxCol}6")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($this->colors['highlight']);

                // Добавляем фиксацию первой строки и первого столбца
                $sheet->freezePane('B7');

                // Альтернативная окраска строк компаний
                for ($i = 0; $i < count($this->report['companies']); $i++) {
                    $rowNum = $i + 7; // Начало данных на 7 строке
                    $bgcolor = ($i % 2 == 0) ? $this->colors['company_bg'] : $this->colors['company_alt_bg'];

                    $sheet->getStyle("A{$rowNum}:{$maxCol}{$rowNum}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($bgcolor);
                }

                // Создаем линии сетки для всей таблицы данных
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => $this->colors['border']],
                        ],
                    ],
                ];

                // Применяем границы ко всей таблице данных
                $dataLastRow = 6 + count($this->report['companies']);
                $sheet->getStyle("A5:{$maxCol}{$dataLastRow}")->applyFromArray($styleArray);

                // Дополнительное форматирование для итоговой строки
                $totalRow = $dataLastRow;
                $sheet->getStyle("A{$totalRow}:{$maxCol}{$totalRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($this->colors['subheader_bg']);
                $sheet->getStyle("A{$totalRow}:{$maxCol}{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRow}:{$maxCol}{$totalRow}")->getFont()
                    ->getColor()->setRGB('FFFFFF');

                // Выравнивание и форматирование для всех числовых данных
                $sheet->getStyle("B7:{$maxCol}{$dataLastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Формат с единицами измерения для всех числовых ячеек
                $sheet->getStyle("B7:{$maxCol}{$dataLastRow}")->getNumberFormat()
                    ->setFormatCode('#,##0.00 "м³"');

                // Увеличиваем ширину первой колонки
                $sheet->getColumnDimension('A')->setWidth(30);

                // Применяем автоподбор ширины для остальных колонок
                for ($i = 1; $i <= $this->columnCount; $i++) {
                    $colLetter = Coordinate::stringFromColumnIndex($i);
                    if ($colLetter != 'A') {
                        $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                    }
                }

                // Выделяем итоговые значения по категориям (теперь это последний столбец каждой категории)
                $col = 1;
                foreach ($this->report['headers'] as $category => $info) {
                    if (empty($info['wastes'])) continue;

                    // Вычисляем колонку с "Итого" (теперь это последний столбец группы)
                    $colLetter = Coordinate::stringFromColumnIndex($col + $info['colspan']);

                    // Для итоговых значений по категориям - тёмный текст и выделение
                    $dataLastRow = 6 + count($this->report['companies']);
                    $sheet->getStyle("{$colLetter}6:{$colLetter}{$dataLastRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($this->colors['total_bg']);
                    $sheet->getStyle("{$colLetter}6:{$colLetter}{$dataLastRow}")->getFont()->getColor()->setRGB('000000');
                    // Выделяем жирным шрифтом
                    $sheet->getStyle("{$colLetter}6:{$colLetter}{$dataLastRow}")->getFont()->setBold(true);

                    $col += $info['colspan'];
                }

                // Стилизуем пояснения и параметры
                $startRow = $dataLastRow + 3;
                $sheet->getStyle("A{$startRow}")->getFont()->setBold(true)->setSize(12);
                $sheet->mergeCells("A{$startRow}:{$maxCol}{$startRow}");

                $paramsRow = $startRow + 5;
                $sheet->getStyle("A{$paramsRow}")->getFont()->setBold(true)->setSize(12);
                $sheet->mergeCells("A{$paramsRow}:{$maxCol}{$paramsRow}");

                // Обновляем стиль для итоговых колонок - темный текст на светло-синем фоне
                $col = 1;
                foreach ($this->report['headers'] as $category => $info) {
                    if (empty($info['wastes'])) continue;

                    $colLetter = Coordinate::stringFromColumnIndex($col + 1);
                    // Добавляем для ячейки итога в заголовке - более тёмный текст
                    $sheet->getStyle("{$colLetter}6")->getFont()->getColor()->setRGB('000000');
                    // Для итоговых значений по категориям - тоже тёмный текст
                    $dataLastRow = 6 + count($this->report['companies']);
                    $sheet->getStyle("{$colLetter}7:{$colLetter}{$dataLastRow}")->getFont()->getColor()->setRGB('000000');

                    $col += $info['colspan'];
                }

                // Для колонки "ИТОГО (м³)" тоже делаем темный текст для лучшей читаемости
                $sheet->getStyle("{$maxCol}6")->getFont()->getColor()->setRGB('000000');
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Заголовок и подзаголовки таблицы
            '5:6' => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->colors['header_bg']],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Фиксируем заголовки
            '5' => [
                'font' => ['size' => 12],
            ],
            // Обновляем стили для заголовков в 6-й строке (названия отходов/колонок)
            '6' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->colors['subheader_bg']],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Стилизуем названия компаний
            'A7:A100' => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],
        ];
    }
}
