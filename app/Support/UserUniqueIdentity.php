<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Libera e-mail/CPF/username e outros identificadores únicos antes da exclusão da conta,
 * para que o mesmo e-mail ou documento possa ser reutilizado no cadastro.
 */
final class UserUniqueIdentity
{
    public static function release(User $user): void
    {
        $id = (int) $user->id;
        $stamp = now()->format('YmdHis');

        $updates = [
            'email' => "deleted+{$id}.{$stamp}@deleted.invalid",
        ];

        foreach (['document', 'legal_representative_cpf', 'phone', 'username', 'referral_code'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $updates[$column] = null;
            }
        }

        $user->forceFill($updates)->saveQuietly();
    }
}
