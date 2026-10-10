<?php

namespace App\Support;

final class OrderShippingStatus
{
    public const PREPARING = 'preparing';

    public const COLLECTED = 'collected';

    public const IN_TRANSIT = 'in_transit';

    public const DELIVERED = 'delivered';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            self::PREPARING,
            self::COLLECTED,
            self::IN_TRANSIT,
            self::DELIVERED,
        ];
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::PREPARING => 'Pedido sendo preparado',
            self::COLLECTED => 'Pedido coletado',
            self::IN_TRANSIT => 'Pedido em trânsito',
            self::DELIVERED => 'Pedido entregue',
            default => 'Aguardando envio',
        };
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public static function steps(): array
    {
        return array_map(
            fn (string $key) => ['key' => $key, 'label' => self::label($key)],
            self::values()
        );
    }

    public static function resolve(?string $raw): ?string
    {
        $value = is_string($raw) ? strtolower(trim($raw)) : '';

        return in_array($value, self::values(), true) ? $value : null;
    }

    public static function stepIndex(?string $status): int
    {
        $resolved = self::resolve($status);
        if ($resolved === null) {
            return -1;
        }

        return array_search($resolved, self::values(), true);
    }
}
