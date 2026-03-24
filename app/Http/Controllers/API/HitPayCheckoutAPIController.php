<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\Sale;
use App\Services\HitPay\HitPayClient;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class HitPayCheckoutAPIController extends AppBaseController
{
    public function __construct(private readonly HitPayClient $hitPayClient)
    {
    }

    public function store(Request $request, Sale $sale)
    {
        if ($sale->payment_status === Sale::PAID) {
            throw new UnprocessableEntityHttpException('This sale has already been paid.');
        }

        $validated = $request->validate([
            'payment_methods' => 'sometimes|array',
            'payment_methods.*' => 'string',
        ]);

        $customer = $sale->customer;

        $payload = [
            'amount' => round((float) $sale->grand_total, 2),
            'currency' => config('hitpay.currency'),
            'name' => $customer?->name,
            'email' => 'customer@smartshoppingsystem.com',
            'phone' => '09123456789',
                'purpose' => 'Smart Shopping System',
            'reference_number' => $sale->reference_code,
            'redirect_url' => config('hitpay.redirect_url'),
            'webhook' => config('hitpay.webhook_url'),
            'send_email' => $this->hitPayClient->booleanString((bool) config('hitpay.send_email')),
            'send_sms' => $this->hitPayClient->booleanString((bool) config('hitpay.send_sms')),
            'payment_methods' => $validated['payment_methods'] ?? config('hitpay.payment_methods'),
        ];

        $checkout = $this->hitPayClient->createPaymentRequest($payload);

        return $this->sendResponse([
            'sale_id' => $sale->id,
            'checkout_url' => $checkout['url'],
            'payment_request_id' => $checkout['id'] ?? null,
            'status' => $checkout['status'] ?? null,
        ], 'HitPay checkout created successfully.');
    }
}
