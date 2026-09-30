<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderConversation;
use App\Models\OrderMessage;
use App\Models\Product;
use App\Models\ProductDeliveryCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderChatService
{
    public function __construct(
        protected ContactLeakFilter $filter,
    ) {}

    /**
     * Comprador do pedido ou dono/equipe do tenant vendedor.
     */
    public function userCanAccessOrder(User $user, Order $order): bool
    {
        $userId = (int) $user->id;
        if ((int) ($order->user_id ?? 0) === $userId) {
            return true;
        }

        $sellerOwnerId = (int) ($order->tenant_id ?? 0);
        if ($sellerOwnerId <= 0) {
            return false;
        }

        if ($userId === $sellerOwnerId) {
            return true;
        }

        if ($user->canAccessSellerPanel()) {
            $myTenant = (int) ($user->tenant_id ?: $user->id);

            return $myTenant === $sellerOwnerId;
        }

        return false;
    }

    public function ensureConversation(Order $order): ?OrderConversation
    {
        if (! Schema::hasTable('order_conversations')) {
            return null;
        }

        $existing = OrderConversation::query()->where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $buyerId = (int) ($order->user_id ?? 0);
        $sellerId = (int) ($order->tenant_id ?? 0);
        if ($buyerId <= 0 || $sellerId <= 0) {
            return null;
        }

        return OrderConversation::query()->create([
            'order_id' => $order->id,
            'product_id' => $order->product_id,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'last_message_at' => now(),
        ]);
    }

    public function postSystemMessage(OrderConversation $conversation, string $body, string $type = OrderMessage::TYPE_SYSTEM): OrderMessage
    {
        $message = OrderMessage::query()->create([
            'order_conversation_id' => $conversation->id,
            'user_id' => null,
            'type' => $type,
            'body' => $body,
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();

        return $message;
    }

    public function postUserMessage(OrderConversation $conversation, User $user, string $body): OrderMessage
    {
        if (! $this->userCanAccessConversation($user, $conversation)) {
            abort(403);
        }

        $this->filter->assertAllowed($body);

        $message = OrderMessage::query()->create([
            'order_conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'type' => OrderMessage::TYPE_USER,
            'body' => trim($body),
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();

        return $message;
    }

    public function userCanAccessConversation(User $user, OrderConversation $conversation): bool
    {
        $userId = (int) $user->id;
        if ((int) $conversation->buyer_id === $userId || (int) $conversation->seller_id === $userId) {
            return true;
        }

        if ($user->canAccessSellerPanel()) {
            $myTenant = (int) ($user->tenant_id ?: $user->id);

            return $myTenant === (int) $conversation->seller_id;
        }

        return false;
    }

    /**
     * Reserva um código disponível e publica no chat.
     */
    public function deliverAutomaticCode(Order $order): ?ProductDeliveryCode
    {
        if (! Schema::hasTable('product_delivery_codes')) {
            return null;
        }

        $product = $order->product ?? Product::query()->find($order->product_id);
        if (! $product || ! $product->usesAutomaticDelivery()) {
            return null;
        }

        return DB::transaction(function () use ($order, $product) {
            /** @var ProductDeliveryCode|null $code */
            $code = ProductDeliveryCode::query()
                ->where('product_id', $product->id)
                ->where('status', ProductDeliveryCode::STATUS_AVAILABLE)
                ->lockForUpdate()
                ->orderBy('id')
                ->first();

            if (! $code) {
                $conversation = $this->ensureConversation($order);
                if ($conversation) {
                    $this->postSystemMessage(
                        $conversation,
                        'Pagamento confirmado. Estoque automático esgotado — o vendedor entregará pelo chat.'
                    );
                }

                return null;
            }

            $code->forceFill([
                'status' => ProductDeliveryCode::STATUS_SOLD,
                'order_id' => $order->id,
                'sold_at' => now(),
            ])->save();

            $conversation = $this->ensureConversation($order);
            if ($conversation) {
                $payload = $code->getPayload();
                $this->postSystemMessage(
                    $conversation,
                    "Entrega automática:\n\n{$payload}",
                    OrderMessage::TYPE_DELIVERY
                );
            }

            return $code;
        });
    }
}
