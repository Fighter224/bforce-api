<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $userId = $user->id;

        // Get invoice items for this user’s invoices
        $invoiceItems = InvoiceItem::whereHas('invoice', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        return response()->json($invoiceItems);
    }
}
