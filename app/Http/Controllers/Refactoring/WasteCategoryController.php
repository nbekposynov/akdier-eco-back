<?php

namespace App\Http\Controllers\Refactoring;

use App\Http\Controllers\Controller;
use App\Models\Refactoring\WasteCategory;
use Illuminate\Http\Request;

class WasteCategoryController extends Controller
{
    public function index()
    {
        $categories = WasteCategory::all();
        return response()->json($categories);
    }

    // Создание категории
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:waste_categories,name|max:255',
            'slug' => 'required|string|unique:waste_categories,slug|max:255',
        ]);

        $category = WasteCategory::create($validated);

        return response()->json(['message' => 'Category created successfully', 'data' => $category]);
    }

    // Обновление категории
    public function update(Request $request, $id)
    {
        $category = WasteCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:waste_categories,name,' . $category->id . '|max:255',
            'slug' => 'required|string|unique:waste_categories,slug,' . $category->id . '|max:255',
        ]);

        $category->update($validated);

        return response()->json(['message' => 'Category updated successfully', 'data' => $category]);
    }

    // Удаление категории
    public function destroy($id)
    {
        $category = WasteCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
