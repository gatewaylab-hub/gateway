<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ProductDeliveryCode extends Model
{
    public const STATUS_AVAILABLE = 'available';

    public const STATUS_SOLD = 'sold';

    public const STATUS_RESERVED = 'reserved';

    protected $fillable = [
        'product_id',
        'tenant_id',
        'payload_encrypted',
        'status',
        'order_id',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function setPayload(string $plain): void
    {
        $this->payload_encrypted = Crypt::encryptString($plain);
    }

    public function getPayload(): string
    {
        return Crypt::decryptString((string) $this->payload_encrypted);
    }
}
