<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderConversation extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'buyer_id',
        'seller_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class)->orderBy('created_at');
    }

    public function isParticipant(int $userId): bool
    {
        if ((int) $this->buyer_id === $userId || (int) $this->seller_id === $userId) {
            return true;
        }

        $user = User::query()->find($userId);
        if (! $user || ! $user->canAccessSellerPanel()) {
            return false;
        }

        $ownerId = (int) ($user->tenant_id ?: $user->id);

        return $ownerId === (int) $this->seller_id;
    }
}
