<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Moderator;
use App\Models\Processing;
use App\Models\User;
use Illuminate\Http\Request;

class ModeratorController extends Controller
{

    public function getModerators()
    {
        $moderators = User::where('role', 'moderator')->get(['id', 'name', 'email']);
        return response()->json($moderators);
    }
}
