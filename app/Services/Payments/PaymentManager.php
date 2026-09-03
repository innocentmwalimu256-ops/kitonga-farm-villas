<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use Exception;

class PaymentManager
{
    protected $gateways = [];

    public function __construct()
    {
        // Register default mock gateway
        $this->register('mock', new MockPaymentGateway());
    }

    /**
     * Register a new gateway driver.
     */
    public function register(string $name, PaymentGatewayInterface $gateway)
    {
        $this->gateways[$name] = $gateway;
        return $this;
    }

    /**
     * Get a registered gateway driver.
     */
    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name = $name ?? config('services.payments.default', 'mock');

        if (!isset($this->gateways[$name])) {
            throw new Exception("Payment driver '{$name}' is not registered.");
        }

        return $this->gateways[$name];
    }
}
