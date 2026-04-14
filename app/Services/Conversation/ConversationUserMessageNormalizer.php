<?php

namespace App\Services\Conversation;

class ConversationUserMessageNormalizer
{
    public function normalize(string $message): string
    {
        $normalized = trim($message);

        if ($normalized === '') {
            return $normalized;
        }

        $normalized = preg_replace('/(?<=\p{L})(?=\d)|(?<=\d)(?=\p{L})/u', ' ', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
