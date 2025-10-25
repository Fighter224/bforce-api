<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        $items = InvoiceItem::with(['invoice', 'product.warrantyGroup.warranties'])
            ->whereHas('invoice', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        foreach ($items as $item) {
            \Log::debug('Warranty Group:', ['warrantyGroup' => $item->product->warrantyGroup]);
        }

        return response()->json($items);
    }

    // Controller method
    public function getUserInvoices(Request $request)
    {
        $userId = $request->user()->id; // From auth token

        $invoices = Invoice::with('items')
            ->where('user_id', $request->user()->id)
            ->get();


        return response()->json($invoices);
    }


    public function store(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'user_id' => 'required|uuid|exists:users,id',
            'discount' => 'nullable|numeric|min:0', // fixed amount discount
            // If percentage instead: 'discount' => 'nullable|numeric|min:0|max:100',

            'payment_method' => 'required|string|max:50', // cash, credit card, e-wallet, etc.
        ]);

        DB::beginTransaction();

        try {
            $datePart = Carbon::now()->format('ymd');
            $countToday = Invoice::whereDate('created_at', Carbon::today())->count() + 1;
            $invoiceNumber = 'IV-1' . $datePart . str_pad($countToday, 3, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'id' => Str::uuid(),
                'invoice_number' => $invoiceNumber,
                'invoice_date' => Carbon::now(),
                'total_amount' => $request->total_amount,
                'user_id' => $request->user_id,
                'discount' => $request->discount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'id' => Str::uuid(),
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Invoice created successfully.',
                'invoice' => $invoice
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        $datePart = Carbon::now()->format('ymd');
        $countToday = Invoice::whereDate('created_at', Carbon::today())->count() + 1;
        $invoiceNumber = 'IV-2' . $datePart . str_pad($countToday, 3, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'id' => Str::uuid(),
            'invoice_number' => $invoiceNumber,
            'invoice_date' => Carbon::now(),
            'total_amount' => $request->total_amount,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Invoice created successfully.',
            'invoice' => $invoice,
            'invoice_id' => $invoice->id
        ], 201);
    }

    public function updatePaymentStatus($invoiceId, $status)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->status = $status; // 'paid', 'failed', 'cancelled'
        $invoice->save();

        return response()->json([
            'message' => "Invoice status updated to {$status}",
            'invoice' => $invoice
        ]);
    }

    // public function returnUrl(Request $request)
    // {
    //     $statusId = $request->query('status_id');
    //     $orderId = $request->query('order_id');
    //     $transactionId = $request->query('transaction_id');

    //     return redirect('http://localhost:4200/payment-result?status_id=' . $statusId . '&order_id=' . $orderId . '&transaction_id=' . $transactionId);
    // }



}
