<?php

namespace App\Http\Controllers;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index(){

        $companies = Company::all();
        return response()->json($companies);

    } 
    public function getCompanyById()
    {
        $companyId = Auth::id();
        $company = Company::find($companyId);
    
        if ($company) {
            return response()->json($company);
        } else {
            return response()->json(['message' => 'Company not found'], 404);
        }
    }

    public function updateByIdCompany(Request $request, $id)
    {
        $data = $request->all();
        
        $company = Company::find($id);
        
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

    public function getByIdCompany($id)
{
    $processing = Company::find($id);
    
    if (!$processing) {
        return response()->json(['error' => 'Report not found'], 404);
    }
    
    return response()->json($processing);
}
}
