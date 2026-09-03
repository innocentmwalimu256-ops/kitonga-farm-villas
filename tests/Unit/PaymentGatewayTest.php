<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Payments\PaymentManager;
use App\Services\Payments\MockPaymentGateway;

class PaymentGatewayTest extends TestCase
{
    /**
     * Test PaymentManager container binding and MockPaymentGateway functionality.
     */
    public function test_payment_manager_resolves_default_driver_successfully()
    {
        $manager = app(PaymentManager::class);
        $this->assertInstanceOf(PaymentManager::class, $manager);

        $driver = $manager->driver();
        $this->assertInstanceOf(MockPaymentGateway::class, $driver);

        $result = $driver->charge(150000.00, ['description' => 'Test tour stay']);
        $this->assertTrue($result['success']);
        $this->assertStringStartsWith('TXN-', $result['transaction_id']);
        $this->assertEquals(150000.00, $result['amount']);
        $this->assertEquals('mock_gateway', $result['gateway']);
    }
}
