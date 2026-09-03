<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Simulate charge request.
     */
    public function charge(float $amount, array $options = []): array
    {
        // Simulate a successful local mobile money or card checkout callback
        return [
            'success' => true,
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            'message' => 'Simulated checkout completed successfully.',
            'amount' => $amount,
            'gateway' => 'mock_gateway',
        ];
    }

    /**
     * Simulate refund request.
     */
    public function refund(string $transactionId, ?float $amount = null): array
    {
        return [
            'success' => true,
            'refund_id' => 'RFD-' . strtoupper(Str::random(12)),
            'message' => 'Simulated refund processed.',
            'amount' => $amount,
            'gateway' => 'mock_gateway',
        ];
    }
}
