<?php

namespace App\Services;

use App\Models\FinalProcessing;
use App\Models\Refactoring\WasteRecord;
use App\Models\Refactoring\WasteRecordItem;

class WasteRecordService
{    // Создание записи
    public function create(array $data)
    {
        $wasteRecord = WasteRecord::create([
            'company_id'   => $data['company_id'],
            'moderator_id' => $data['moderator_id'],
            'car_num'      => $data['car_num'] ?? null,
            'driv_name'    => $data['driv_name'] ?? null,
            'record_date'  => $data['record_date'],
        ]);

        foreach ($data['items'] as $item) {
            WasteRecordItem::create([
                'waste_record_id' => $wasteRecord->id,
                'waste_id'        => $item['waste_id'],
                'amount'          => $item['amount'],
            ]);
        }

        // Передаем массив items с факторами для создания FinalProcessing
        $this->generateFinalProcessing($wasteRecord, $data['items']);

        return $wasteRecord;
    }

    // Обновление записи
    public function update(WasteRecord $wasteRecord, array $data)
    {
        $wasteRecord->update([
            'car_num'     => $data['car_num'] ?? $wasteRecord->car_num,
            'driv_name'   => $data['driv_name'] ?? $wasteRecord->driv_name,
            'record_date' => $data['record_date'],
        ]);

        // Удаляем отходы, которых нет в запросе
        $newItemIds = collect($data['items'])->pluck('waste_id');
        $wasteRecord->items()->whereNotIn('waste_id', $newItemIds)->delete();

        // Обновляем или создаем отходы
        foreach ($data['items'] as $item) {
            $recordItem = $wasteRecord->items()->where('waste_id', $item['waste_id'])->first();

            if ($recordItem) {
                $recordItem->update(['amount' => $item['amount']]);
            } else {
                WasteRecordItem::create([
                    'waste_record_id' => $wasteRecord->id,
                    'waste_id'        => $item['waste_id'],
                    'amount'          => $item['amount'],
                ]);
            }
        }

        // Удаляем и пересоздаем FinalProcessing с учетом переданных факторов
        FinalProcessing::where('waste_record_id', $wasteRecord->id)->delete();
        $this->generateFinalProcessing($wasteRecord, $data['items']);
        return $wasteRecord;
    }

    // Генерация записей в FinalProcessing
    public function generateFinalProcessing(WasteRecord $wasteRecord, array $itemsWithFactors = [])
    {
        // Создаем массив факторов по waste_id для быстрого поиска
        $customFactors = [];
        foreach ($itemsWithFactors as $item) {
            if (isset($item['factor'])) {
                $customFactors[$item['waste_id']] = $item['factor'];
            }
        }

        foreach ($wasteRecord->items()->with(['waste.finalWasteType'])->get() as $item) {
            $finalWasteType = $item->waste->finalWasteType;
            // Проверяем, что finalWasteType существует
            if (!$finalWasteType) {
                throw new \Exception("Не добавлен финальный тип отхода для отхода: {$item->waste->name}");
            }

            // Используем переданный фактор для этого waste_id, если он есть,
            // иначе используем фактор из finalWasteType
            $factor = isset($customFactors[$item->waste_id])
                ? $customFactors[$item->waste_id]
                : $finalWasteType->factor;

            FinalProcessing::create([
                'waste_record_id' => $wasteRecord->id,
                'name_othod'      => $finalWasteType->final_name,
                'company_id'      => $wasteRecord->company_id,
                'value'           => $item->amount * $factor,
                'type_operation'  => $finalWasteType->type_operation,
                'created_at'      => $wasteRecord->created_at,
                'updated_at'      => now(),
            ]);
        }
    }


    // Удаление записи
    public function delete(WasteRecord $wasteRecord)
    {
        FinalProcessing::where('waste_record_id', $wasteRecord->id)->delete();
        $wasteRecord->items()->delete();
        $wasteRecord->delete();
    }
}
