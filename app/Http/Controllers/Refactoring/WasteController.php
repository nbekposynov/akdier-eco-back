<?php

namespace App\Http\Controllers\Refactoring;

use App\Http\Controllers\Controller;
use App\Models\Refactoring\Waste;
use App\Models\Refactoring\FinalWasteType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WasteController extends Controller
{
    public function index()
    {
        $wastes = Waste::with(['category', 'finalWasteType'])->get();
        return response()->json($wastes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:wastes,name|max:255',
            'slug' => 'required|string|unique:wastes,slug|max:255',
            'category_id' => 'required|exists:waste_categories,id',
            'final_waste_type_id' => 'required|exists:final_waste_types,id',
        ]);

        $waste = Waste::create($validated);
        $waste->load('category', 'finalWasteType');


        return response()->json(['message' => 'Waste created successfully', 'data' => $waste]);
    }

    public function update(Request $request, $id)
    {
        $waste = Waste::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:wastes,name,' . $waste->id . '|max:255',
            'slug' => 'required|string|unique:wastes,slug,' . $waste->id . '|max:255',
            'category_id' => 'required|exists:waste_categories,id',
            'final_waste_type_id' => 'required|exists:final_waste_types,id',
        ]);

        $waste->update($validated);

        // Загружаем связанные модели, чтобы вернуть их в ответе
        $waste->load('category', 'finalWasteType');

        return response()->json(['message' => 'Waste updated successfully', 'data' => $waste]);
    }


    public function destroy($id)
    {
        $waste = Waste::findOrFail($id);
        $waste->delete();

        return response()->json(['message' => 'Waste deleted successfully']);
    }

    public function getAllWastes(): JsonResponse
    {
        $data = Waste::with(['category', 'finalWasteType'])->orderBy('category_id')->get()->map(function ($waste) {
            return [
                'id' => $waste->id,
                'name' => $waste->name,
                'slug' => $waste->slug,
                'category' => $waste->category->name,
                'final_waste_type' => $waste->finalWasteType->final_name ?? null,
            ];
        });

        return response()->json($data);
    }
}
