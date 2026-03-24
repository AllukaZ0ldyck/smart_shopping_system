<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SalesPayment;
use App\Repositories\SalesPaymentRepository;
use App\Services\HitPay\HitPayClient;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HitPayWebhookController extends Controller
{
    public function __construct(
        private readonly HitPayClient $hitPayClient,
        private readonly SalesPaymentRepository $salesPaymentRepository
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Hitpay-Signature');
        $webhookSalt = (string) config('hitpay.webhook_salt');

        if ($webhookSalt !== '' && ! $this->hitPayClient->verifySignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid HitPay signature.'], 401);
        }

        $data = $request->json()->all();
        $status = strtolower((string) ($data['status'] ?? ''));
        $referenceNumber = $data['reference_number'] ?? null;

        if (empty($referenceNumber)) {
            return response()->json(['message' => 'Missing sale reference.'], 422);
        }

        /** @var Sale|null $sale */
        $sale = Sale::whereReferenceCode($referenceNumber)->first();

        if (! $sale) {
            return response()->json(['message' => 'Sale not found.'], 404);
        }

        if (! in_array($status, ['completed', 'paid', 'succeeded'], true)) {
            return response()->json(['success' => true, 'message' => 'Ignored non-success HitPay status.']);
        }

        $paymentReference = $data['id'] ?? $data['payment_request_id'] ?? $referenceNumber;

        if (SalesPayment::whereSaleId($sale->id)->whereReference($paymentReference)->exists()) {
            return response()->json(['success' => true, 'message' => 'HitPay payment already captured.']);
        }

        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid payment amount.'], 422);
        }

        $this->salesPaymentRepository->storeSalePayment([
            'reference' => $paymentReference,
            'payment_date' => Carbon::parse($data['updated_at'] ?? now())->toDateString(),
            'payment_type' => config('hitpay.payment_method_id'),
            'amount' => $amount,
            'received_amount' => $sale->grand_total,
        ], $sale);

        return response()->json(['success' => true, 'message' => 'HitPay payment captured successfully.']);
    }
}
