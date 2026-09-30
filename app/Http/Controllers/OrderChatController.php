<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderConversation;
use App\Models\OrderMessage;
use App\Services\OrderChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderChatController extends Controller
{
    public function __construct(
        protected OrderChatService $chat,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $userId = (int) $user->id;
        $sellerOwnerId = $user->canAccessSellerPanel()
            ? (int) ($user->tenant_id ?: $user->id)
            : null;

        $conversations = OrderConversation::query()
            ->with(['order:id,amount,status,product_id', 'product:id,name', 'buyer:id,name', 'seller:id,name'])
            ->where(function ($q) use ($userId, $sellerOwnerId) {
                $q->where('buyer_id', $userId)->orWhere('seller_id', $userId);
                if ($sellerOwnerId && $sellerOwnerId !== $userId) {
                    $q->orWhere('seller_id', $sellerOwnerId);
                }
            })
            ->orderByDesc('last_message_at')
            ->paginate(30);

        return Inertia::render('Marketplace/ChatInbox', [
            'conversations' => $conversations,
            'use_account_shell' => $user->isCliente(),
            'pageTitle' => 'Chats',
        ]);
    }

    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();
        abort_unless($this->chat->userCanAccessOrder($user, $order), 403);

        $conversation = $this->chat->ensureConversation($order);
        abort_unless($conversation, 404);

        $conversation->load(['messages.user:id,name', 'product:id,name', 'buyer:id,name', 'seller:id,name', 'order']);

        OrderMessage::query()
            ->where('order_conversation_id', $conversation->id)
            ->whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', '!=', $user->id);
            })
            ->update(['read_at' => now()]);

        return Inertia::render('Marketplace/ChatShow', [
            'use_account_shell' => $user->isCliente(),
            'pageTitle' => 'Chat',
            'conversation' => [
                'id' => $conversation->id,
                'order_id' => $conversation->order_id,
                'product_name' => $conversation->product?->name,
                'buyer_name' => $conversation->buyer?->name,
                'seller_name' => $conversation->seller?->name,
                'messages' => $conversation->messages->map(fn (OrderMessage $m) => [
                    'id' => $m->id,
                    'type' => $m->type,
                    'body' => $m->body,
                    'user_id' => $m->user_id,
                    'user_name' => $m->user?->name,
                    'created_at' => $m->created_at?->toIso8601String(),
                    'created_human' => $m->created_at?->diffForHumans(),
                    'mine' => (int) $m->user_id === (int) $user->id,
                ]),
            ],
            'pollUrl' => route('chat.show', $order),
        ]);
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->chat->userCanAccessOrder($user, $order), 403);

        $conversation = $this->chat->ensureConversation($order);
        abort_unless($conversation, 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:4000'],
        ]);

        $this->chat->postUserMessage($conversation, $user, $data['body']);

        return back();
    }
}
