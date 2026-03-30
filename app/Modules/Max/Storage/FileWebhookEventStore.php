<?php

namespace App\Modules\Max\Storage;

use App\Modules\Max\Contracts\WebhookEventStoreInterface;
use App\Modules\Max\DTO\StoredWebhookFile;
use Carbon\CarbonImmutable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FileWebhookEventStore implements WebhookEventStoreInterface
{
    public function __construct(
        private readonly FilesystemManager $filesystem,
    ) {
    }

    public function storeRaw(string $rawPayload): StoredWebhookFile
    {
        $storedAt = CarbonImmutable::now('UTC');
        $relativePath = sprintf(
            '%s/%s/%s/%s_%s.json',
            $storedAt->format('Y'),
            $storedAt->format('m'),
            $storedAt->format('d'),
            $storedAt->format('Ymd_His_u'),
            Str::lower(Str::random(10)),
        );

        $this->disk()->put($this->qualifyPath($relativePath), $rawPayload);

        return new StoredWebhookFile(
            id: $this->encodeId($relativePath),
            relativePath: $relativePath,
            storedAt: $storedAt,
            rawJson: $rawPayload,
        );
    }

    public function latest(int $limit = 50): array
    {
        return $this->allQualifiedPaths()
            ->sortDesc()
            ->take(max(1, $limit))
            ->map(fn (string $path): ?StoredWebhookFile => $this->fileFromQualifiedPath($path))
            ->filter()
            ->values()
            ->all();
    }

    public function find(string $id): ?StoredWebhookFile
    {
        $relativePath = $this->decodeId($id);

        if ($relativePath === null) {
            return null;
        }

        return $this->fileFromQualifiedPath($this->qualifyPath($relativePath));
    }

    /**
     * @return Collection<int, string>
     */
    private function allQualifiedPaths(): Collection
    {
        return collect($this->disk()->allFiles($this->basePath()))
            ->filter(static fn (string $path): bool => str_ends_with($path, '.json'));
    }

    private function fileFromQualifiedPath(string $qualifiedPath): ?StoredWebhookFile
    {
        if (! $this->disk()->exists($qualifiedPath)) {
            return null;
        }

        $relativePath = $this->unqualifyPath($qualifiedPath);
        $lastModified = $this->disk()->lastModified($qualifiedPath);

        return new StoredWebhookFile(
            id: $this->encodeId($relativePath),
            relativePath: $relativePath,
            storedAt: CarbonImmutable::createFromTimestampUTC($lastModified),
            rawJson: (string) $this->disk()->get($qualifiedPath),
        );
    }

    private function disk(): FilesystemAdapter
    {
        return $this->filesystem->disk((string) config('max.webhook.storage_disk', 'local'));
    }

    private function basePath(): string
    {
        return trim((string) config('max.webhook.storage_path', 'max/webhooks'), '/');
    }

    private function qualifyPath(string $relativePath): string
    {
        return $this->basePath().'/'.trim($relativePath, '/');
    }

    private function unqualifyPath(string $qualifiedPath): string
    {
        $prefix = $this->basePath().'/';

        if (str_starts_with($qualifiedPath, $prefix)) {
            return substr($qualifiedPath, strlen($prefix));
        }

        return trim($qualifiedPath, '/');
    }

    private function encodeId(string $relativePath): string
    {
        return rtrim(strtr(base64_encode($relativePath), '+/', '-_'), '=');
    }

    private function decodeId(string $encodedPath): ?string
    {
        $padding = (4 - (strlen($encodedPath) % 4)) % 4;
        $decoded = base64_decode(strtr($encodedPath.str_repeat('=', $padding), '-_', '+/'), true);

        if (! is_string($decoded) || $decoded === '') {
            return null;
        }

        $normalized = trim($decoded, '/');

        if ($normalized === '' || str_contains($normalized, '..')) {
            return null;
        }

        return $normalized;
    }
}
