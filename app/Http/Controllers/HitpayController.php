<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Sale;
use App\Events\SalePaidEvent;

class HitpayController extends Controller
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('HITPAY_BASE_URL', 'https://api.hit-pay.com/v1');
    }

    /**
     * ------------------------------------------------------------------
     *  1) CREATE PAYMENT (Create Sale + Payment + HitPay Request)
     * ------------------------------------------------------------------
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'customer_id' => 'required|integer',
            'warehouse_id'=> 'required|integer',
            'email'       => 'sometimes|email'
        ]);

        // RC-REF-yyyyMMddHHmmss
        $reference = 'RC-REF-' . Carbon::now()->format('YmdHis');
        $currency  = 'PHP';
        $amount    = (float) $request->amount;
        $amount = 1;

        /**
         * 1️⃣ Create SALE Record
         */
        $sale = Sale::create([
            'date'          => Carbon::now()->format('Y-m-d'),
            'customer_id'   => $request->customer_id,
            'warehouse_id'  => $request->warehouse_id,
            'grand_total'   => $amount,
            'paid_amount'   => 0,

            'discount_type' => 2,
            'discount_value'=> 0,
            'discount'      => 0,
            'tax_rate'      => 0,
            'shipping'      => 0,

            'payment_status'=> 0,
            'payment_type'  => null,
            'status'        => 1,
            'reference_code'=> $reference,
        ]);

        /**
         * 2️⃣ Create PAYMENT record
         */
        $payment = Payment::create([
            'sale_id'   => $sale->id,
            'reference' => $reference,
            'amount'    => $amount,
            'currency'  => $currency,
            'email'     => $request->email ?? 'minimart@gmail.com',
            'status'    => 'pending',
        ]);

        /**
         * 3️⃣ HitPay Request
         */
        $payload = [
            'amount'           => number_format($amount, 2, '.', ''),
            'currency'         => $currency,
            'purpose'          => 'RC Minimart Payment : ' . $reference,
            'reference_number' => $reference,
            'redirect_url'     => route('hitpay.callback'),
            'webhook'          => route('hitpay.webhook'),
            'email'            => $payment->email,
            'payment_methods'  => ['gcash', 'upay_online', 'qrph_netbank'],
            'send_email'       => true,
            'send_sms'         => false,
        ];

        $response = Http::asForm()
            ->withHeaders([
                'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY')
            ])->post($this->baseUrl.'/payment-requests', $payload);

        if (!$response->successful()) {
            $payment->meta = ['error' => $response->json()];
            $payment->save();

            return response()->json([
                'success' => false,
                'error'   => $response->json()
            ], 500);
        }

        $hp = $response->json();

        // Save HitPay ID + meta
        $payment->hitpay_payment_id = $hp['id'] ?? null;
        $payment->meta = $hp;
        $payment->save();

        return response()->json([
            'success'      => true,
            'redirect_url' => $hp['url'] ?? null,
            'reference'    => $reference,
            'payment_id'   => $payment->hitpay_payment_id,
            'sale_id'      => $sale->id,
        ]);
    }

    /**
     * ------------------------------------------------------------------
     *  2) CALLBACK (browser redirect)
     *  Always redirect to pos-callback view → will auto-redirect to POS
     * ------------------------------------------------------------------
     */
    public function callback(Request $request)
    {
        \Log::info('HITPAY CALLBACK RECEIVED', $request->all());

        // HitPay sometimes sends reference_number, sometimes reference (UUID)
        $referenceNumber = $request->query('reference_number'); // RC-REF-...
        $hitpayId = $request->query('reference'); // UUID

        \Log::info('CALLBACK ID MAPPING', [
            'reference_number' => $referenceNumber,
            'hitpay_id' => $hitpayId
        ]);

        // 1️⃣ Try using HitPay UUID (reference)
        $payment = null;
        if ($hitpayId) {
            $payment = Payment::where('hitpay_payment_id', $hitpayId)->first();
        }

        // 2️⃣ Fallback to RC-REF-XXXX
        if (!$payment && $referenceNumber) {
            $payment = Payment::where('reference', $referenceNumber)->first();
        }

        // Still nothing → failed
        if (!$payment) {
            \Log::error("HITPAY CALLBACK ERROR: PAYMENT NOT FOUND", [
                'reference_number' => $referenceNumber,
                'hitpay_id' => $hitpayId
            ]);
            return redirect('/pos?payment=failed');
        }

        $status = $payment->status === 'completed' ? 'success' : 'failed';

        return redirect("/pos?reference={$payment->reference}&status={$status}");
    }



    /**
     * ------------------------------------------------------------------
     *  3) WEBHOOK (HitPay → Server)
     *  Updates payment & sale, fires SalePaidEvent
     * ------------------------------------------------------------------
     */
    public function webhook(Request $request)
    {
        $reference = $request->reference_number ?? null;
        $hitpayId  = $request->payment_request_id ?? null;

        if (!$reference && !$hitpayId) {
            return response('Missing reference', 400);
        }

        $payment = Payment::where('reference', $reference)
            ->orWhere('hitpay_payment_id', $hitpayId)
            ->first();

        if (!$payment) return response('Payment not found', 404);

        // Verify via API
        $verify = Http::withHeaders([
            'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
        ])->get($this->baseUrl.'/payment-requests/'.$payment->hitpay_payment_id);

        if (!$verify->successful()) {
            return response('Verification failed', 400);
        }

        $hp     = $verify->json();
        $status = strtolower($hp['status'] ?? '');

        // Update payment
        $payment->status = $status;
        $payment->meta   = $hp;
        $payment->save();

        // Update sale
        $sale = $payment->sale;

        if ($sale) {
            if (in_array($status, ['completed', 'paid', 'succeeded'])) {

                $sale->payment_status = 1;
                $sale->paid_amount    = $sale->grand_total;
                $sale->payment_type   = 999;
                $sale->save();

                event(new SalePaidEvent($sale));
            }

            if (in_array($status, ['failed','cancelled','expired'])) {
                $sale->payment_status = -1;
                $sale->save();
            }
        }

        return response('OK', 200);
    }

    /**
     * ------------------------------------------------------------------
     *  4) STATUS (POS Polling Endpoint)
     * ------------------------------------------------------------------
     */
    public function status($reference)
    {
        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Not found'];
        }

        // Quick return for final statuses
        if (in_array($payment->status, ['completed', 'failed'])) {
            return [
                'success' => true,
                'status'  => $payment->status,
                'payment' => $payment
            ];
        }

        // Fetch latest from HitPay
        if ($payment->hitpay_payment_id) {
            $res = Http::withHeaders([
                'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
            ])->get($this->baseUrl.'/payment-requests/'.$payment->hitpay_payment_id);

            if ($res->successful()) {
                $hpStatus = $res['status'] ?? null;

                if (in_array($hpStatus, ['completed','paid'])) {
                    $payment->status = 'completed';
                } elseif (in_array($hpStatus, ['failed','cancelled','expired'])) {
                    $payment->status = 'failed';
                } else {
                    $payment->status = 'pending';
                }

                $payment->meta = array_merge($payment->meta ?? [], ['last_check' => $res->json()]);
                $payment->save();

                return [
                    'success' => true,
                    'status'  => $payment->status,
                    'payment' => $payment
                ];
            }

            return ['success' => false, 'error' => $res->json()];
        }

        return [
            'success' => true,
            'status'  => $payment->status,
            'payment' => $payment
        ];
    }
}
