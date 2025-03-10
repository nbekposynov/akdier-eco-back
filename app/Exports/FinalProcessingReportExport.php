<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class FinalProcessingReportExport implements FromArray, WithStyles, WithEvents, WithTitle
{
    protected $report;
    protected $columnCount = 0;
    protected $rowCount = 0;

    // Цвета для оформления
    protected $colors = [
        'header_bg' => '1F4E78',      // Темно-синий для заголовков
        'subheader_bg' => '4472C4',   // Средне-синий
        'waste_bg' => '8EAADB',       // Голубой для типов отходов
        'operation_bg' => 'D6E4F0',   // Светло-голубой для операций
        'border' => '8EA9DB',
        'company_bg' => 'F5F9FF',     // Светло-голубой фон для четных строк
        'company_alt_bg' => 'FFFFFF', // Белый фон для нечетных строк
        'highlight' => 'FFC000',      // Желтый для выделения важных данных
        'total_bg' => 'D6E4F0',       // Светло-голубой для итогов
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

        return "Финальная обработка {$dateRange}";
    }

    public function array(): array
    {
        // Проверка наличия данных
        if (empty($this->report['companies']) || empty($this->report['operations']) || empty($this->report['wastes'])) {
            return [['Нет данных для отображения']];
        }

        $data = [];

        // Заголовок отчета
        $data[] = ['ОТЧЕТ ПО ФИНАЛЬНОЙ ОБРАБОТКЕ ОТХОДОВ'];

        // Добавляем информацию о периоде
        if (isset($this->report['filters']['start_date'], $this->report['filters']['end_date'])) {
            $from = date('d.m.Y', strtotime($this->report['filters']['start_date']));
            $to = date('d.m.Y', strtotime($this->report['filters']['end_date']));
            $data[] = ["Отчетный период: с {$from} по {$to}"];
        }

        // Указываем единицы измерения
        $data[] = ['Все измерения приведены в тоннах (т)'];
        $data[] = [''];

        // Формируем заголовки отчета
        $headers = ['Компания', 'Тип отхода', 'Тип операции', 'Количество (т)'];
        $data[] = $headers;

        // Заполняем данные по каждой компании
        foreach ($this->report['companies'] as $companyIndex => $company) {
            $firstRowForCompany = true;
            $companyRows = 0;
            
            foreach ($this->report['wastes'] as $wasteName) {
                $firstRowForWaste = true;
                $wasteTotal = 0; // Общее количество для отхода
                
                foreach ($this->report['operations'] as $operationType) {
                    $operationData = $company['operations'][$operationType] ?? null;
                    $amount = ($operationData && isset($operationData['wastes'][$wasteName])) 
                        ? $operationData['wastes'][$wasteName] 
                        : 0;
                    
                    // Если значение равно 0, можно пропустить строку для компактности
                    if ($amount == 0) continue;
                    
                    $row = [];
                    
                    // Название компании только в первой строке компании
                    if ($firstRowForCompany) {
                        $row[] = $company['name'];
                        $firstRowForCompany = false;
                    } else {
                        $row[] = '';
                    }
                    
                    // Название типа отхода только в первой строке для этого отхода
                    if ($firstRowForWaste) {
                        $row[] = $wasteName;
                        $firstRowForWaste = false;
                    } else {
                        $row[] = '';
                    }
                    
                    // Тип операции и количество
                    $row[] = $operationType;
                    $row[] = $amount;
                    
                    $data[] = $row;
                    $companyRows++;
                    $wasteTotal += $amount;
                }
            }
            
            // Добавляем строку итогов для компании
            if ($companyRows > 0) {
                $data[] = [
                    '', 
                    'ИТОГО ПО КОМПАНИИ', 
                    '', 
                    $company['total']
                ];
                
                // Добавляем пустую строку между компаниями
                $data[] = ['', '', '', ''];
            }
        }
        
        // Убираем последнюю пустую строку перед общим итогом
        if (!empty($data)) {
            array_pop($data);
        }
        
        // Добавляем общую итоговую строку для всех компаний
        $grandTotal = 0;
        foreach ($this->report['companies'] as $company) {
            $grandTotal += $company['total'];
        }
        
        $data[] = ['ИТОГО ПО ВСЕМ КОМПАНИЯМ', '', '', $grandTotal];
        
        $this->columnCount = count($headers);
        $this->rowCount = count($data);
        
        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxCol = 'D'; // Теперь всегда 4 колонки
                
                // Формируем заголовок отчета
                $sheet->mergeCells("A1:{$maxCol}1");
                $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Формируем период отчета
                $sheet->mergeCells("A2:{$maxCol}2");
                $sheet->getStyle('A2')->getFont()->setBold(true);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Формируем информацию о единицах измерения
                $sheet->mergeCells("A3:{$maxCol}3");
                $sheet->getStyle('A3')->getFont()->setSize(12)->setItalic(true);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Форматируем заголовки
                $sheet->getStyle("A5:{$maxCol}5")->getFont()->setBold(true);
                $sheet->getStyle("A5:{$maxCol}5")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($this->colors['header_bg']);
                $sheet->getStyle("A5:{$maxCol}5")->getFont()->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A5:{$maxCol}5")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                
                // Находим строки с итогами по отходам и выделяем их
                for ($row = 6; $row <= $this->rowCount; $row++) {
                    $cellValue = $sheet->getCell("C{$row}")->getValue();
                    if ($cellValue === 'Итого по отходу') {
                        $sheet->getStyle("A{$row}:{$maxCol}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($this->colors['total_bg']);
                        $sheet->getStyle("A{$row}:{$maxCol}{$row}")->getFont()->setBold(true);
                    }
                }
                
                // Находим строки с итогами по компаниям и выделяем их
                for ($row = 6; $row <= $this->rowCount; $row++) {
                    $cellValue = $sheet->getCell("B{$row}")->getValue();
                    if ($cellValue === 'ИТОГО ПО КОМПАНИИ') {
                        $sheet->getStyle("A{$row}:{$maxCol}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($this->colors['subheader_bg']);
                        $sheet->getStyle("A{$row}:{$maxCol}{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}:{$maxCol}{$row}")->getFont()->getColor()->setRGB('FFFFFF');
                        $sheet->mergeCells("B{$row}:C{$row}");
                    }
                }
                
                // Выделяем последнюю строку с общим итогом
                $sheet->getStyle("A{$this->rowCount}:{$maxCol}{$this->rowCount}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($this->colors['header_bg']);
                $sheet->getStyle("A{$this->rowCount}:{$maxCol}{$this->rowCount}")->getFont()->setBold(true);
                $sheet->getStyle("A{$this->rowCount}:{$maxCol}{$this->rowCount}")->getFont()->getColor()->setRGB('FFFFFF');
                $sheet->mergeCells("A{$this->rowCount}:C{$this->rowCount}");
                
                // Форматирование для числовой колонки
                $sheet->getStyle("D6:D{$this->rowCount}")->getNumberFormat()
                    ->setFormatCode('#,##0.00 "т"');
                $sheet->getStyle("D6:D{$this->rowCount}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                
                // Выделяем колонку "Количество (т)" более жирной границей
                $sheet->getStyle("D5:D{$this->rowCount}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
                
                // Устанавливаем ширину столбцов
                $sheet->getColumnDimension('A')->setWidth(30); // Компания
                $sheet->getColumnDimension('B')->setWidth(30); // Тип отхода
                $sheet->getColumnDimension('C')->setWidth(25); // Тип операции
                $sheet->getColumnDimension('D')->setWidth(18); // Количество
                
                // Устанавливаем границы для всей таблицы
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => $this->colors['border']],
                        ],
                    ],
                ];
                
                $sheet->getStyle("A5:{$maxCol}{$this->rowCount}")->applyFromArray($styleArray);
                
                // Объединяем ячейки для компаний
                $currentCompany = '';
                $companyStartRow = 6;
                
                for ($row = 6; $row <= $this->rowCount; $row++) {
                    $company = $sheet->getCell("A{$row}")->getValue();
                    
                    // Если нашли заполненное значение компании
                    if ($company !== '' && $company !== 'ИТОГО ПО ВСЕМ КОМПАНИЯМ') {
                        $currentCompany = $company;
                        $companyStartRow = $row;
                    }
                    // Если нашли конец группы компании (либо итого, либо новая компания)
                    elseif (($sheet->getCell("B{$row}")->getValue() === 'ИТОГО ПО КОМПАНИИ') || 
                           ($company !== '' && $company !== $currentCompany)) {
                        
                        // Объединяем ячейки для текущей компании
                        if ($companyStartRow < $row - 1) {
                            $sheet->mergeCells("A{$companyStartRow}:A" . ($row - 1));
                        }
                        
                        if ($sheet->getCell("B{$row}")->getValue() === 'ИТОГО ПО КОМПАНИИ') {
                            $currentCompany = '';
                        } else {
                            $currentCompany = $company;
                            $companyStartRow = $row;
                        }
                    }
                }
                
                // Объединяем ячейки для типов отходов аналогично
                $currentWaste = '';
                $wasteStartRow = 6;
                
                for ($row = 6; $row <= $this->rowCount; $row++) {
                    $waste = $sheet->getCell("B{$row}")->getValue();
                    
                    if ($waste !== '' && $waste !== 'ИТОГО ПО КОМПАНИИ' && $waste !== 'ИТОГО ПО ВСЕМ КОМПАНИЯМ') {
                        $currentWaste = $waste;
                        $wasteStartRow = $row;
                    } elseif ($waste !== $currentWaste || $sheet->getCell("C{$row}")->getValue() === 'Итого по отходу') {
                        if ($wasteStartRow < $row - 1 && $currentWaste !== '') {
                            $sheet->mergeCells("B{$wasteStartRow}:B" . ($row - 1));
                        }
                        
                        if ($sheet->getCell("C{$row}")->getValue() === 'Итого по отходу') {
                            $currentWaste = '';
                        } else {
                            $currentWaste = $waste;
                            $wasteStartRow = $row;
                        }
                    }
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Общие стили для всего документа
            'A:Z' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
