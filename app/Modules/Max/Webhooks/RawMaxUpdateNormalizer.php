<?php

namespace App\Modules\Max\Webhooks;

use Carbon\CarbonImmutable;

class RawMaxUpdateNormalizer
{
    /**
     * @return array<string, mixed>|null
     */
    public function decode(string $rawJson): ?array
    {
        $decoded = json_decode($rawJson, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function summarizeFromRaw(string $rawJson): array
    {
        $payload = $this->decode($rawJson);

        if ($payload === null) {
            return [
                'parse_status' => 'invalid_json',
                'update_type' => 'unknown',
                'chat_id' => null,
                'user_id' => null,
                'message_text' => null,
                'callback_payload' => null,
            ];
        }

        return [
            'parse_status' => 'parsed',
            'update_type' => (string) ($payload['update_type'] ?? 'unknown'),
            'chat_id' => $this->extractChatId($payload),
            'user_id' => $this->extractUserId($payload),
            'message_text' => data_get($payload, 'message.body.text'),
            'callback_payload' => data_get($payload, 'callback.payload'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeFromRaw(string $rawJson): array
    {
        $payload = $this->decode($rawJson);

        if ($payload === null) {
            return [
                'error' => 'Некорректный JSON payload.',
            ];
        }

        $timestampMs = $this->extractTimestampMs($payload);

        return [
            'raw_update_type' => $payload['update_type'] ?? null,
            'timestamp_ms' => $timestampMs,
            'timestamp_iso' => $timestampMs !== null
                ? CarbonImmutable::createFromTimestampMsUTC($timestampMs)->toIso8601String()
                : null,
            'user_locale' => $payload['user_locale'] ?? null,
            'chat' => [
                'id' => $this->extractChatId($payload),
                'type' => data_get($payload, 'message.recipient.chat_type'),
            ],
            'from_user' => [
                'id' => $this->extractUserId($payload),
                'name' => $this->extractUserName($payload),
                'is_bot' => $this->extractUserIsBot($payload),
            ],
            'message' => [
                'id' => data_get($payload, 'message.body.mid'),
                'seq' => data_get($payload, 'message.body.seq'),
                'text' => data_get($payload, 'message.body.text'),
                'attachments' => data_get($payload, 'message.body.attachments', []),
            ],
            'callback_query' => [
                'id' => data_get($payload, 'callback.callback_id'),
                'data' => data_get($payload, 'callback.payload'),
                'user_id' => data_get($payload, 'callback.user.user_id'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractChatId(array $payload): int|string|null
    {
        return data_get($payload, 'message.recipient.chat_id', $payload['chat_id'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractUserId(array $payload): int|string|null
    {
        return data_get($payload, 'callback.user.user_id')
            ?? data_get($payload, 'message.sender.user_id')
            ?? data_get($payload, 'user.user_id');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractUserName(array $payload): ?string
    {
        return data_get($payload, 'callback.user.name')
            ?? data_get($payload, 'message.sender.name')
            ?? data_get($payload, 'user.name')
            ?? data_get($payload, 'callback.user.first_name')
            ?? data_get($payload, 'message.sender.first_name')
            ?? data_get($payload, 'user.first_name');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractUserIsBot(array $payload): ?bool
    {
        $value = data_get($payload, 'callback.user.is_bot')
            ?? data_get($payload, 'message.sender.is_bot')
            ?? data_get($payload, 'user.is_bot');

        return is_bool($value) ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractTimestampMs(array $payload): ?int
    {
        $timestamp = $payload['timestamp'] ?? data_get($payload, 'message.timestamp');

        if (! is_int($timestamp) && ! is_string($timestamp)) {
            return null;
        }

        $normalized = (int) $timestamp;

        if ($normalized <= 0) {
            return null;
        }

        if ($normalized < 1_000_000_000_000) {
            return $normalized * 1000;
        }

        return $normalized;
    }
}
