<?php

namespace App\Http\Controllers;

use App\Models\Refactoring\FinalWasteType;
use Illuminate\Http\Request;

class FinalWasteTypeController extends Controller
{
    public function index()
    {
        $types = FinalWasteType::all();
        return response()->json($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'final_name' => 'required|string|max:255|unique:final_waste_types,final_name',
            'factor' =>     'required|numeric',
            'type_operation' => 'required|string|max:255',
        ]);

        $type = FinalWasteType::create($validated);

        return response()->json(['message' => 'Type created successfully', 'data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $type = FinalWasteType::findOrFail($id);

        $validated = $request->validate([
            'final_name' => 'string|max:255|unique:final_waste_types,final_name,' . $type->id,
            'type_operation' => 'string|max:255',
            'factor' =>     'numeric',

        ]);

        $type->update($validated);

        return response()->json(['message' => 'Type updated successfully', 'data' => $type]);
    }

    public function destroy($id)
    {
        $type = FinalWasteType::findOrFail($id);
        $type->delete();

        return response()->json(['message' => 'Type deleted successfully']);
    }
}
