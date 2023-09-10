<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Moderator;
use App\Models\Processing;
use Illuminate\Http\Request;

class ModeratorController extends Controller
{

    public function index(){

        $moderators = Moderator::all();
        return response()->json($moderators);

    } 

    public function getByIdModerator($id)
    {
        $processing = Moderator::find($id);
        
        if (!$processing) {
            return response()->json(['error' => 'Report not found'], 404);
        }
        
        return response()->json($processing);
    }

    public function updateByIdModerator(Request $request, $id)
    {
        $data = $request->all();
        
        $company = Moderator::find($id);
        
        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }
        
        $company->fill($data);
        
        // Update the password if provided
        if (isset($data['password'])) {
            $company->password = bcrypt($data['password']);
        }
        
        $company->save();
        
        return response()->json($company);
    }

}
