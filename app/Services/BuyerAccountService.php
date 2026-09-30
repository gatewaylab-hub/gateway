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
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name !== '' ? $name : $email,
                'password' => $passwordHash,
                'role' => User::ROLE_CLIENTE,
                'tenant_id' => null,
                'phone' => $phone,
                'document' => $document,
                'person_type' => $document ? 'pf' : null,
            ]
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
            }
            $user->update($updates);
        }

        return ['user' => $user->fresh(), 'was_recently_created' => $wasRecentlyCreated];
    }
}
