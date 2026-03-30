<?php

namespace App\Modules\Max\Contracts;

use App\Modules\Max\DTO\StoredWebhookFile;

interface WebhookEventStoreInterface
{
    public function storeRaw(string $rawPayload): StoredWebhookFile;

    /**
     * @return array<int, StoredWebhookFile>
     */
    public function latest(int $limit = 50): array;

    public function find(string $id): ?StoredWebhookFile;
}
