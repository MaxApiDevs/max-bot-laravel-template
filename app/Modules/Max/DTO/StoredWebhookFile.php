<?php

namespace App\Modules\Max\DTO;

use Carbon\CarbonImmutable;

readonly class StoredWebhookFile
{
    public function __construct(
        public string $id,
        public string $relativePath,
        public CarbonImmutable $storedAt,
        public string $rawJson,
    ) {
    }
}
