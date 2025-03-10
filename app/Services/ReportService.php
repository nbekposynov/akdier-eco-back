<?php

namespace App\Services;

use App\Models\User;
use App\Models\FinalProcessing;
use App\Models\Refactoring\Waste;
use App\Models\Refactoring\WasteRecordItem;
use Carbon\Carbon;

class ReportService
{
    public function generateReport(array $filters)
    {
        // Получаем все компании с фильтрацией
        $companiesQuery = User::where('role', 'company');

        // Фильтрация по одной или нескольким компаниям
        if (isset($filters['company_id'])) {
            $companiesQuery->where('id', $filters['company_id']);
        } elseif (isset($filters['company_ids']) && is_array($filters['company_ids'])) {
            $companiesQuery->whereIn('id', $filters['company_ids']);
        }

        // Остальные фильтры для компаний
        if (isset($filters['company_name'])) {
            $companiesQuery->where('name', 'LIKE', "%{$filters['company_name']}%");
        }
        if (isset($filters['bin'])) {
            $companiesQuery->where('bin_company', 'LIKE', "%{$filters['bin']}%");
        }

        $companies = $companiesQuery->get();

        // Получаем все категории с фильтрацией
        $wastesQuery = Waste::with('category');

        // Фильтрация по категории отходов
        if (isset($filters['category_id'])) {
            $wastesQuery->where('category_id', $filters['category_id']);
        }
        if (isset($filters['waste_name'])) {
            $wastesQuery->where('name', 'LIKE', "%{$filters['waste_name']}%");
        }

        $categories = $wastesQuery->get()->groupBy('category.name');

        // Подготавливаем заголовки для отчета
        $headers = [];
        $wasteMap = []; // для быстрого поиска waste_id

        foreach ($categories as $categoryName => $wastes) {
            $headers[$categoryName] = [
                'name' => $categoryName,
                'wastes' => $wastes->map(function ($waste) use (&$wasteMap) {
                    $wasteMap[$waste->id] = $waste->name;
                    return $waste->name;
                })->toArray(),
                'colspan' => $wastes->count() + 1 // +1 для итого по категории
            ];
        }

        // Получаем данные с расширенной фильтрацией
        $recordsQuery = WasteRecordItem::with(['waste.category', 'wasteRecord.company'])
            ->when(isset($filters['start_date'], $filters['end_date']), function ($query) use ($filters) {
                $query->whereHas('wasteRecord', function ($q) use ($filters) {
                    $q->whereBetween('record_date', [
                        Carbon::parse($filters['start_date'])->startOfDay(),
                        Carbon::parse($filters['end_date'])->endOfDay()
                    ]);
                });
            })
            // Фильтрация по модератору
            ->when(isset($filters['moderator_id']), function ($query) use ($filters) {
                $query->whereHas('wasteRecord', function ($q) use ($filters) {
                    $q->where('moderator_id', $filters['moderator_id']);
                });
            })
            // Фильтрация по машине
            ->when(isset($filters['car_num']), function ($query) use ($filters) {
                $query->whereHas('wasteRecord', function ($q) use ($filters) {
                    $q->where('car_num', 'LIKE', "%{$filters['car_num']}%");
                });
            })
            // Фильтрация по водителю
            ->when(isset($filters['driver_name']), function ($query) use ($filters) {
                $query->whereHas('wasteRecord', function ($q) use ($filters) {
                    $q->where('driv_name', 'LIKE', "%{$filters['driver_name']}%");
                });
            });

        // Фильтрация по компаниям
        if (isset($filters['company_id'])) {
            $recordsQuery->whereHas('wasteRecord', function ($q) use ($filters) {
                $q->where('company_id', $filters['company_id']);
            });
        } elseif (isset($filters['company_ids']) && is_array($filters['company_ids'])) {
            $recordsQuery->whereHas('wasteRecord', function ($q) use ($filters) {
                $q->whereIn('company_id', $filters['company_ids']);
            });
        }

        $records = $recordsQuery->get();

        // Формируем данные по компаниям
        $companiesData = [];
        foreach ($companies as $company) {
            $companyData = ['name' => $company->name];

            foreach ($categories as $categoryName => $wastes) {
                $categoryTotal = 0;
                foreach ($wastes as $waste) {
                    $amount = $records
                        ->where('waste_id', $waste->id)
                        ->where('wasteRecord.company_id', $company->id)
                        ->sum('amount');

                    $companyData[$waste->name] = $amount;
                    $categoryTotal += $amount;
                }
                $companyData[$categoryName . '_total'] = $categoryTotal;
            }

            $companiesData[] = $companyData;
        }

        return [
            'headers' => $headers,
            'companies' => $companiesData,
            'filters' => $filters // Добавляем фильтры в ответ для отображения в отчете
        ];
    }

    /**
     * Генерирует отчет финальной обработки отходов для одной или нескольких компаний
     * 
     * @param array $filters Фильтры для отчета
     * @return array
     */
    public function generate(array $filters)
    {
        // Базовый запрос для FinalProcessing
        $query = FinalProcessing::query()
            ->with('company'); // Загружаем связанную компанию

        // Фильтрация по компаниям
        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        } elseif (isset($filters['company_ids']) && is_array($filters['company_ids'])) {
            $query->whereIn('company_id', $filters['company_ids']);
        }

        // Фильтрация по дате
        if (isset($filters['start_date'], $filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay(),
            ]);
        }

        // Получаем все записи
        $finalProcessings = $query->get();

        // Получаем уникальные названия операций
        $operationTypes = $finalProcessings->pluck('type_operation')->unique()->values()->toArray();

        // Получаем уникальные названия отходов
        $wasteNames = $finalProcessings->pluck('name_othod')->unique()->values()->toArray();

        // Получаем уникальные компании
        $companyIds = $finalProcessings->pluck('company_id')->unique()->toArray();
        $companies = User::whereIn('id', $companyIds)->get();

        // Формируем структуру отчета
        $report = [
            'operations' => $operationTypes,
            'wastes' => $wasteNames,
            'companies' => [],
            'filters' => $filters
        ];

        // Формируем данные по каждой компании и типу операции
        foreach ($companies as $company) {
            $companyData = [
                'name' => $company->name,
                'operations' => []
            ];

            foreach ($operationTypes as $operationType) {
                $operationData = [
                    'name' => $operationType,
                    'wastes' => []
                ];

                $totalForOperation = 0;

                foreach ($wasteNames as $wasteName) {
                    // Суммируем значения для каждого типа отхода по данному типу операции в данной компании
                    $amount = $finalProcessings
                        ->where('company_id', $company->id)
                        ->where('type_operation', $operationType)
                        ->where('name_othod', $wasteName)
                        ->sum('value');

                    $operationData['wastes'][$wasteName] = $amount;
                    $totalForOperation += $amount;
                }

                $operationData['total'] = $totalForOperation;
                $companyData['operations'][$operationType] = $operationData;
            }

            // Рассчитываем итоги для компании
            $companyTotal = 0;
            foreach ($operationTypes as $operationType) {
                if (isset($companyData['operations'][$operationType])) {
                    $companyTotal += $companyData['operations'][$operationType]['total'];
                }
            }
            $companyData['total'] = $companyTotal;

            $report['companies'][] = $companyData;
        }

        return $report;
    }
}
