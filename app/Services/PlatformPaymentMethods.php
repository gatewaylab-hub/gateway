<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Formas de pagamento habilitadas globalmente pela plataforma (admin / Financeiro).
 * O checkout usa apenas este teto — vendedores não escolhem métodos por produto.
 */
class PlatformPaymentMethods
{
    /** @var list<string> */
    public const METHOD_KEYS = [
        'pix',
        'card',
        'boleto',
        'pix_auto',
        'apple_pay',
        'google_pay',
        'open_finance',
        'paypal',
    ];

    /**
     * @return array<string, bool>
     */
    public static function defaults(): array
    {
        $out = [];
        foreach (self::METHOD_KEYS as $key) {
            $out[$key] = true;
        }

        return $out;
    }

    /**
     * @return array<string, bool>
     */
    public static function platformEnabled(): array
    {
        $raw = Setting::get('platform_payment_methods_enabled', null, null);
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        $base = self::defaults();
        if (! is_array($raw)) {
            return $base;
        }
        foreach (self::METHOD_KEYS as $key) {
            if (array_key_exists($key, $raw)) {
                $base[$key] = filter_var($raw[$key], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $base;
    }

    public static function isEnabled(string $methodKey): bool
    {
        $all = self::platformEnabled();

        return ($all[$methodKey] ?? true) !== false;
    }

    /**
     * Decisão global: só a plataforma habilita/desabilita.
     * O 2º argumento é ignorado (mantido por compatibilidade de assinatura).
     *
     * @param  array<string, bool>  $productEnabled
     */
    public static function isEnabledForCheckout(string $methodKey, array $productEnabled = []): bool
    {
        return self::isEnabled($methodKey);
    }

    /**
     * Flags efetivas no checkout (somente plataforma).
     *
     * @return array<string, bool>
     */
    public static function enabledForCheckout(): array
    {
        return self::platformEnabled();
    }

    /**
     * @return array<int, array{key: string, label: string, hint: string}>
     */
    public static function labelsForAdmin(): array
    {
        return [
            ['key' => 'pix', 'label' => 'PIX', 'hint' => 'QR Code e copia e cola'],
            ['key' => 'card', 'label' => 'Cartão de crédito', 'hint' => 'Checkout com cartão'],
            ['key' => 'boleto', 'label' => 'Boleto', 'hint' => 'Boleto bancário'],
            ['key' => 'pix_auto', 'label' => 'PIX automático', 'hint' => 'Assinaturas com débito recorrente'],
            ['key' => 'apple_pay', 'label' => 'Apple Pay', 'hint' => 'Wallet via CajuPay (qualquer dispositivo)'],
            ['key' => 'google_pay', 'label' => 'Google Pay', 'hint' => 'Wallet via CajuPay (qualquer dispositivo)'],
            ['key' => 'open_finance', 'label' => 'Open Finance', 'hint' => 'Pagamento autorizado no app do banco'],
            ['key' => 'paypal', 'label' => 'PayPal', 'hint' => 'Carteira PayPal (não altera PIX/cartão das outras adquirentes)'],
        ];
    }
}
