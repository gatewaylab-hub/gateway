<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Services\OrderChatService;

class OpenOrderChatOnOrderCompleted
{
    public function __construct(
        protected OrderChatService $chat,
    ) {}

    public function handle(OrderCompleted $event): void
    {
        $order = $event->order;
        $conversation = $this->chat->ensureConversation($order);
        if ($conversation) {
            $this->chat->postSystemMessage(
                $conversation,
                'Pagamento confirmado! Use este chat para combinar a entrega. Não envie contatos externos.'
            );
        }

        $this->chat->deliverAutomaticCode($order);
    }
}
