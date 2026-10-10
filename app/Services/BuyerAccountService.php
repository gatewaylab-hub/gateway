<?php

namespace App\Services;

use App\Models\User;

class BuyerAccountService
{
    /**
     * Garante utilizador de compra por e-mail: conta global (tenant_id null) e papel cliente
     * quando aplicável. Não altera papel de infoprodutor ou equipe.
     *
     * @return array{user: User, was_recently_created: bool}
     */
    public function ensureBuyerFromCheckout(
        string $email,
        string $name,
        string $passwordHash,
        bool $isMemberAreaProduct,
        ?string $phone = null,
        ?string $document = null,
    ): array {
        $createPayload = [
            'name' => $name !== '' ? $name : $email,
            'password' => $passwordHash,
            'role' => User::ROLE_CLIENTE,
            'tenant_id' => null,
            'phone' => $phone,
            'document' => $document,
            'person_type' => $document ? 'pf' : null,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'must_change_password')) {
            $createPayload['must_change_password'] = true;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            $createPayload
        );

        $wasRecentlyCreated = $user->wasRecentlyCreated;

        if ($user->isCliente()) {
            $updates = ['tenant_id' => null];
            if ($name !== '' && $user->name !== $name) {
                $updates['name'] = $name;
            }
            if ($phone && blank($user->phone)) {
                $updates['phone'] = $phone;
            }
            if ($document && blank($user->document)) {
                $updates['document'] = $document;
                $updates['person_type'] = 'pf';
            }
            if ($isMemberAreaProduct && ! $wasRecentlyCreated) {
                $updates['password'] = $passwordHash;
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'must_change_password')) {
                    $updates['must_change_password'] = true;
                }
            }
            $user->update($updates);
        }

        return ['user' => $user->fresh(), 'was_recently_created' => $wasRecentlyCreated];
    }
}
