<?php

namespace Tests\Feature;

use App\Models\GatewayCredential;
use App\Models\Setting;
use App\Services\PaymentService;
use App\Services\PlatformPaymentMethods;
use Tests\TestCase;

class PaymentMethodsPerProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('gateway_order', [
            'pix' => ['efi'],
            'card' => [],
            'boleto' => [],
            'pix_auto' => [],
        ], null);
        Setting::set('platform_payment_methods_enabled', PlatformPaymentMethods::defaults(), null);
    }

    public function test_available_methods_ignores_product_disable_and_uses_platform(): void
    {
        $product = $this->createTestProduct([
            'tenant_id' => 1,
            'checkout_config' => [
                'payment_methods_enabled' => [
                    'pix' => false,
                    'card' => false,
                    'boleto' => false,
                    'pix_auto' => false,
                ],
            ],
        ]);

        $cred = new GatewayCredential([
            'tenant_id' => 1,
            'gateway_slug' => 'efi',
            'is_connected' => true,
        ]);
        $cred->setEncryptedCredentials(['payee_code' => '123', 'sandbox' => true]);
        $cred->save();

        $methods = app(PaymentService::class)->availablePaymentMethodsForCheckout($product, null, null);
        $ids = array_column($methods, 'id');
        $this->assertContains('pix', $ids);
    }

    public function test_available_methods_excludes_pix_when_disabled_on_platform(): void
    {
        Setting::set('platform_payment_methods_enabled', array_merge(
            PlatformPaymentMethods::defaults(),
            ['pix' => false]
        ), null);

        $product = $this->createTestProduct([
            'tenant_id' => 1,
            'checkout_config' => [
                'payment_methods_enabled' => [
                    'pix' => true,
                    'card' => true,
                    'boleto' => true,
                ],
            ],
        ]);

        $cred = new GatewayCredential([
            'tenant_id' => 1,
            'gateway_slug' => 'efi',
            'is_connected' => true,
        ]);
        $cred->setEncryptedCredentials(['payee_code' => '123', 'sandbox' => true]);
        $cred->save();

        $methods = app(PaymentService::class)->availablePaymentMethodsForCheckout($product, null, null);
        $ids = array_column($methods, 'id');
        $this->assertNotContains('pix', $ids);
    }

    public function test_boleto_unavailable_when_platform_order_has_no_boleto_gateways(): void
    {
        Setting::set('gateway_order', [
            'pix' => ['efi'],
            'card' => [],
            'boleto' => [],
            'pix_auto' => [],
        ], null);

        $product = $this->createTestProduct(['tenant_id' => 1]);

        $cred = new GatewayCredential([
            'tenant_id' => null,
            'gateway_slug' => 'efi',
            'is_connected' => true,
        ]);
        $cred->setEncryptedCredentials(['payee_code' => '123', 'sandbox' => true]);
        $cred->save();

        $global = app(PaymentService::class)->globallyAvailablePaymentMethodKeys($product, null);

        $this->assertTrue($global['pix']);
        $this->assertFalse($global['boleto']);
    }

    public function test_available_methods_includes_pix_when_enabled_on_platform(): void
    {
        $product = $this->createTestProduct([
            'tenant_id' => 1,
            'checkout_config' => [
                'payment_methods_enabled' => [
                    'pix' => true,
                    'card' => false,
                    'boleto' => false,
                    'pix_auto' => false,
                ],
            ],
        ]);

        $cred = new GatewayCredential([
            'tenant_id' => 1,
            'gateway_slug' => 'efi',
            'is_connected' => true,
        ]);
        $cred->setEncryptedCredentials(['payee_code' => '123', 'sandbox' => true]);
        $cred->save();

        $methods = app(PaymentService::class)->availablePaymentMethodsForCheckout($product, null, null);
        $ids = array_column($methods, 'id');
        $this->assertContains('pix', $ids);
    }
}
