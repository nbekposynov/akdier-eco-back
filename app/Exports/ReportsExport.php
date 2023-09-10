<?php

namespace App\Exports;

use App\Models\Processing;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;


class ReportsExport implements FromCollection, WithHeadings, WithStyles
{
    protected $reports;

    public function __construct($reports)
    {
        $this->reports = $reports;
    }

    public function collection(): Collection
    {
        return $this->reports;
    }

    public function headings(): array
    {
        return [
            'Наименование Компании',
            'ТБО Всего:',
            'ТБО Пищевые',
            'ТБО Пластиковые',
            'ТБО Бумага',
            'ТБО Деревяные',
            'ТБО Мешки',
            'TBO Металл',
            'ТБО Неутиль. часть',
            'БСВ',
            'ТПО Всего',
            'ТПО Цемент',
            'ТПО Древесн',
            'ТПО Металл-M',
            'ТПО Крышки',
            'ТПО Мешки',
            'ТПО Пластик',
            'ТПО Шины. рез',
            'ТПО Ветош, Фи',
            'ТПО Макул',
            'ТПО Аккумулятор',
            'ТПО Тара Металлическая',
            'ТПО Тара Пол',
            'ПО Всего',
            'ПО Нефтеш',
            'ПО Зам Гр',
            'ПО Бур Шл',
            'ПО Обр',
            'ПО Хим Реаг'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            // Добавьте другие стили, если нужно...

            // Установка ширины столбцов
            'A:Z' => [
                'width' => 15,
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true, // Позволяет тексту переноситься на новую строку, если он длиннее ячейки
                ],
            ],
    
            // Установка высоты строк
            '1:' . (count($this->reports) + 2) => ['height' => 20], // +2 для заголовков и итоговой строки
        ];
    }
}
