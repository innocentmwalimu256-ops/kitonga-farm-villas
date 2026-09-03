<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Charge a payment.
     */
    public function charge(float $amount, array $options = []): array;

    /**
     * Refund a previous charge.
     */
    public function refund(string $transactionId, ?float $amount = null): array;
}
