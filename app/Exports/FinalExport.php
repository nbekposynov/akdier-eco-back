<?php

namespace App\Exports;

use App\Models\Processing;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinalExport implements FromCollection, WithHeadings
{
    protected $reports;

    public function __construct($reports)
    {
        $this->reports = $reports;
    }

    public function collection(): Collection
    {
        $finalProcessingRecordsWithTotals = [];
        $currentCategory = null;
        $currentTypeOperation = null;
        $categoryTotal = 0;

        foreach ($this->reports as $record) {
            if ($currentCategory !== $record->name_othod || $currentTypeOperation !== $record->type_operation) {
                if ($currentCategory !== null) {
                    $categoryTotalRecord = [
                        'Наименование отхода' => 'Итого',
                        'БИН Организации' => '', // Оставляем пустое поле для итогов
                        'Тип Операции' => $currentTypeOperation,
                        'Количество в т.' => $categoryTotal,
                    ];
                    $finalProcessingRecordsWithTotals[] = $categoryTotalRecord;
                }

                $currentCategory = $record->name_othod;
                $currentTypeOperation = $record->type_operation;
                $categoryTotal = 0;
            }

            $categoryTotal += $record->total_value;
            $finalProcessingRecordsWithTotals[] = [
                'Наименование отхода' => $record->name_othod,
                'БИН Организации' => $record->bin_company,
                'Тип Операции' => $record->type_operation,
                'Количество в т.' => $record->total_value,
            ];
        }

        if ($currentCategory !== null) {
            $categoryTotalRecord = [
                'Наименование отхода' => 'Итого',
                'БИН Организации' => '', // Оставляем пустое поле для итогов
                'Тип Операции' => $currentTypeOperation,
                'Количество в т.' => $categoryTotal,
            ];
            $finalProcessingRecordsWithTotals[] = $categoryTotalRecord;
        }

        return new Collection($finalProcessingRecordsWithTotals);
    }

    public function headings(): array
    {
        return [
            'Наименование отхода',
            'БИН Организации',
            'Тип Операции',
            'Количество в т.'
        ];
    }
}