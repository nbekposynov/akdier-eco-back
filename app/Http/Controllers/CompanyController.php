<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function getCompanies(Request $request)
    {
        $query = User::where('role', 'company'); // Фильтруем только пользователей с ролью "company"

        // Фильтрация по moderator_id, если параметр указан
        if ($request->has('moderator_id') && !empty($request->input('moderator_id'))) {
            $query->where('moderator_id', $request->input('moderator_id'));
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('search') . '%')
                    ->orWhere('bin_company', 'like', '%' . $request->input('search') . '%');
            });
        }

        // Возвращаем данные без пагинации
        $companies = $query->get(['id', 'name', 'bin_company', 'moderator_id']);

        return response()->json($companies);
    }
}
