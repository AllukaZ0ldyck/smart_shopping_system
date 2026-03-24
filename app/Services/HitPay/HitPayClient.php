<?php

namespace App\Services\HitPay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class HitPayClient
{
    public function __construct(private readonly Client $client = new Client())
    {
    }

    public function createPaymentRequest(array $payload): array
    {
        $apiKey = (string) config('hitpay.api_key');

        if ($apiKey === '') {
            throw new UnprocessableEntityHttpException('HitPay API key is not configured.');
        }

        $payload = Arr::where($payload, function ($value) {
            if (is_array($value)) {
                return ! empty($value);
            }

            return $value !== null && $value !== '';
        });

        try {
            $response = $this->client->post(rtrim(config('hitpay.api_url'), '/') . '/payment-requests', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-BUSINESS-API-KEY' => $apiKey,
                ],
                'json' => $payload,
            ]);
        } catch (GuzzleException $exception) {
            throw new UnprocessableEntityHttpException('Unable to create HitPay payment request: ' . $exception->getMessage());
        }

        $data = json_decode((string) $response->getBody(), true);

        if (! is_array($data) || empty($data['url'])) {
            throw new UnprocessableEntityHttpException('HitPay did not return a checkout URL.');
        }

        return $data;
    }

    public function verifySignature(string $payload, ?string $signature): bool
    {
        $salt = (string) config('hitpay.webhook_salt');

        if ($salt === '' || $signature === null || $signature === '') {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $salt);

        return hash_equals($computed, $signature);
    }

    public function booleanString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
