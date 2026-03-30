<?php

namespace App\Modules\Max\DTO;

readonly class BotInfo
{
    /**
     * @param  array<int, array<string, mixed>>  $commands
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int $userId,
        public string $firstName,
        public ?string $username,
        public bool $isBot,
        public ?int $lastActivityTime,
        public ?string $description,
        public ?string $avatarUrl,
        public ?string $fullAvatarUrl,
        public array $commands,
        public array $raw,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            userId: (int) ($payload['user_id'] ?? 0),
            firstName: (string) ($payload['first_name'] ?? ''),
            username: isset($payload['username']) ? (string) $payload['username'] : null,
            isBot: (bool) ($payload['is_bot'] ?? false),
            lastActivityTime: isset($payload['last_activity_time']) ? (int) $payload['last_activity_time'] : null,
            description: isset($payload['description']) ? (string) $payload['description'] : null,
            avatarUrl: isset($payload['avatar_url']) ? (string) $payload['avatar_url'] : null,
            fullAvatarUrl: isset($payload['full_avatar_url']) ? (string) $payload['full_avatar_url'] : null,
            commands: is_array($payload['commands'] ?? null) ? array_values($payload['commands']) : [],
            raw: $payload,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->raw;
    }
}
