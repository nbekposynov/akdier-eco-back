<?php

namespace App\Http\Controllers\Refactoring;

use App\Http\Controllers\Controller;
use App\Models\Refactoring\WasteRecord;
use App\Services\WasteRecordService;
use Illuminate\Http\Request;


class WasteRecordController extends Controller
{
    private WasteRecordService $service;

    public function __construct(WasteRecordService $service)
    {
        $this->service = $service;
    }

    public function search(Request $request)
    {
        // Инициализация запроса с моделью и связями
        $query = WasteRecord::with('items.waste');

        // Фильтрация по имени компании
        if ($request->filled('company_name')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('company_name') . '%');
            });
        }

        // Фильтрация по БИН компании
        if ($request->filled('bin')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('bin_company', 'like', '%' . $request->input('bin') . '%');
            });
        }

        // Фильтрация по дате создания
        if ($request->filled('created_from') && $request->has('created_to')) {
            $query->whereBetween('created_at', [$request->input('created_from'), $request->input('created_to')]);
        } elseif ($request->filled('created_from')) {
            $query->where('created_at', '>=', $request->input('created_from'));
        } elseif ($request->filled('created_to')) {
            $query->where('created_at', '<=', $request->input('created_to'));
        }

        // Фильтрация по модератору
        if ($request->filled('moderator_id')) {
            $query->where('moderator_id', $request->input('moderator_id'));
        }

        // Фильтрация по имени водителя
        if ($request->filled('driv_name')) {
            $query->where('driv_name', 'like', '%' . $request->input('driv_name') . '%');
        }

        // Фильтрация по номеру машины
        if ($request->filled('car_num')) {
            $query->where('car_num', 'like', '%' . $request->input('car_num') . '%');
        }

        // Сортировка по последним записям
        $query->orderBy('created_at', 'desc');

        // Пагинация (10 записей на страницу)
        $records = $query->paginate(10);

        return response()->json($records);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:users,id',
            'moderator_id' => 'required|exists:users,id',
            'car_num' => 'nullable|string',
            'driv_name' => 'nullable|string',
            'record_date' => 'required|date',
            'items' => 'required|array',
            'items.*.waste_id' => 'required|exists:wastes,id',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.factor' => 'nullable|numeric|min:0', // Фактор для каждого отхода отдельно
        ]);

        $record = $this->service->create($validated);
        return response()->json(['message' => 'Record created', 'data' => $record]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'car_num' => 'nullable|string',
            'driv_name' => 'nullable|string',
            'record_date' => 'required|date',
            'items' => 'required|array',
            'items.*.waste_id' => 'required|exists:wastes,id',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.factor' => 'nullable|numeric|min:0', // Фактор для каждого отхода отдельно
        ]);

        $record = WasteRecord::findOrFail($id);
        $updatedRecord = $this->service->update($record, $validated);
        return response()->json(['message' => 'Record updated', 'data' => $updatedRecord]);
    }

    public function destroy($id)
    {
        $record = WasteRecord::findOrFail($id);
        $this->service->delete($record);
        return response()->json(['message' => 'Record deleted']);
    }

    public function filter(Request $request)
    {
        // Инициализация запроса с моделью и связями
        $query = WasteRecord::with(['items.waste', 'company', 'moderator']);

        // Фильтрация по компании
        if ($request->filled('company_name')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('company_name') . '%');
            });
        }

        if ($request->filled('bin')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('bin_company', 'like', '%' . $request->input('bin') . '%');
            });
        }

        // Фильтрация по диапазону дат
        if ($request->filled('start_date') && $request->has('end_date')) {
            $query->whereBetween('record_date', [
                $request->input('start_date'),
                $request->input('end_date'), // Исправлено
            ]);
        } elseif ($request->filled('start_date')) {
            $query->where('record_date', '>=', $request->input('start_date'));
        } elseif ($request->filled('end_date')) {
            $query->where('record_date', '<=', $request->input('end_date'));
        }

        // Сортировка по дате
        $query->orderBy('record_date', 'desc');

        // Пагинация (10 записей на страницу)
        $records = $query->paginate(10);

        return response()->json($records);
    }
}
