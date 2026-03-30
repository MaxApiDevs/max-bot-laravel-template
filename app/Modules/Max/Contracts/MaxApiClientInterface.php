<?php

namespace App\Modules\Max\Contracts;

use App\Modules\Max\DTO\BotInfo;

interface MaxApiClientInterface
{
    public function getMe(): BotInfo;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSubscriptions(): array;

    /**
     * @param  array<int, string>  $updateTypes
     * @return array<string, mixed>
     */
    public function subscribeWebhook(string $url, array $updateTypes, ?string $secret = null): array;

    /**
     * @return array<string, mixed>
     */
    public function deleteWebhook(string $url): array;
}
