<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function getAccountInfo()
    {
        $user = Auth::user();
        
        return response()->json([
            'username' => $user->name,
            'email' => $user->email,
            'createdAt' => $user->created_at,
            'totalInspections' => $user->total_inspections,
            'totalBatteryInstallations' => $user->total_battery_installations
        ]);
    }
} 