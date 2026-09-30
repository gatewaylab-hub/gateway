<?php

namespace App\Services;

class ContactLeakFilter
{
    public function blockedReason(string $text): ?string
    {
        $normalized = trim($text);
        if ($normalized === '') {
            return 'Mensagem vazia.';
        }

        if (preg_match('/https?:\/\/|www\./i', $normalized)) {
            return 'Não é permitido enviar links externos.';
        }

        if (preg_match('/\b(wa\.me|whatsapp|t\.me|telegram|discord\.gg|discord\.com|instagram\.com|facebook\.com|fb\.com|mailto:)\b/i', $normalized)) {
            return 'Não é permitido enviar contatos externos (WhatsApp, Discord, redes sociais, etc.).';
        }

        if (preg_match('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $normalized)) {
            return 'Não é permitido enviar e-mails.';
        }

        // Telefone BR: 10–13 dígitos (com ou sem máscara)
        $digitsOnly = preg_replace('/\D+/', '', $normalized) ?? '';
        if (preg_match('/(?:\+?55)?\s*\(?\d{2}\)?\s*\d{4,5}[-\s]?\d{4}/', $normalized)
            || (strlen($digitsOnly) >= 10 && strlen($digitsOnly) <= 13 && preg_match('/\d{2}.{0,3}\d{4,5}.{0,2}\d{4}/', $normalized))) {
            return 'Não é permitido enviar números de telefone.';
        }

        // Sequência longa de dígitos (possível telefone colado)
        if (preg_match('/\d{10,}/', $digitsOnly) && strlen($digitsOnly) >= 10 && strlen($digitsOnly) <= 13) {
            // Only flag if the message is mostly phone-like
            $alphaLen = strlen(preg_replace('/[\d\s\-\(\)\+\.]/', '', $normalized) ?? '');
            if ($alphaLen < 8) {
                return 'Não é permitido enviar números de telefone.';
            }
        }

        return null;
    }

    public function assertAllowed(string $text): void
    {
        $reason = $this->blockedReason($text);
        if ($reason !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'body' => $reason,
            ]);
        }
    }
}
