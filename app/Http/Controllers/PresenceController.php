<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->forceFill(['last_seen_at' => now()])->save();
        }

        return response()->json(['ok' => true, 'at' => now()->toIso8601String()]);
    }
}
