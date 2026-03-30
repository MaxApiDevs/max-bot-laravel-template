<?php

namespace App\Http\Controllers;

use App\Modules\Max\Contracts\MaxApiClientInterface;
use App\Modules\Max\Contracts\WebhookEventStoreInterface;
use App\Modules\Max\DTO\StoredWebhookFile;
use App\Modules\Max\Exceptions\MaxApiException;
use App\Modules\Max\Webhooks\RawMaxUpdateNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaxDebugController extends Controller
{
    public function __construct(
        private readonly MaxApiClientInterface $maxApiClient,
        private readonly WebhookEventStoreInterface $eventStore,
        private readonly RawMaxUpdateNormalizer $normalizer,
    ) {
    }

    public function index(): View
    {
        [$botInfo, $subscriptions, $apiErrors] = $this->loadApiData();

        return view('max-debug.index', [
            'configStatus' => $this->configStatus(),
            'botInfo' => $botInfo,
            'subscriptions' => $subscriptions,
            'apiErrors' => $apiErrors,
            'events' => array_map(fn (StoredWebhookFile $file): array => $this->eventListItem($file), $this->eventStore->latest(
                (int) config('max.webhook.history_limit', 50)
            )),
        ]);
    }

    public function subscribe(): RedirectResponse
    {
        $webhookUrl = $this->webhookUrl();

        if ($webhookUrl === null) {
            return $this->redirectWithMessage('error', 'Не задан MAX_WEBHOOK_URL.');
        }

        try {
            $result = $this->maxApiClient->subscribeWebhook(
                $webhookUrl,
                (array) config('max.webhook.update_types', []),
                (string) config('max.webhook.secret', ''),
            );
        } catch (MaxApiException $exception) {
            return $this->redirectWithMessage('error', $exception->getMessage());
        }

        $message = $result['message'] ?? 'Запрос на подключение вебхука выполнен.';

        return $this->redirectWithMessage(
            ($result['success'] ?? true) ? 'success' : 'error',
            is_string($message) ? $message : 'Запрос на подключение вебхука выполнен.',
        );
    }

    public function unsubscribe(): RedirectResponse
    {
        $webhookUrl = $this->webhookUrl();

        if ($webhookUrl === null) {
            return $this->redirectWithMessage('error', 'Не задан MAX_WEBHOOK_URL.');
        }

        try {
            $result = $this->maxApiClient->deleteWebhook($webhookUrl);
        } catch (MaxApiException $exception) {
            return $this->redirectWithMessage('error', $exception->getMessage());
        }

        $message = $result['message'] ?? 'Запрос на удаление вебхука выполнен.';

        return $this->redirectWithMessage(
            ($result['success'] ?? true) ? 'success' : 'error',
            is_string($message) ? $message : 'Запрос на удаление вебхука выполнен.',
        );
    }

    public function showEvent(string $event): View
    {
        $storedFile = $this->eventStore->find($event);

        abort_if($storedFile === null, 404);

        $decoded = $this->normalizer->decode($storedFile->rawJson);

        return view('max-debug.event', [
            'event' => [
                'id' => $storedFile->id,
                'relative_path' => $storedFile->relativePath,
                'stored_at' => $storedFile->storedAt,
                'summary' => $this->normalizer->summarizeFromRaw($storedFile->rawJson),
                'raw_pretty' => $this->prettyJson($decoded) ?? $storedFile->rawJson,
                'normalized_pretty' => $this->prettyJson($this->normalizer->normalizeFromRaw($storedFile->rawJson)) ?? '',
            ],
        ]);
    }

    /**
     * @return array{0: array<string, mixed>|null, 1: array<int, array<string, mixed>>, 2: array<string, string>}
     */
    private function loadApiData(): array
    {
        $botInfo = null;
        $subscriptions = [];
        $errors = [];

        if (! $this->hasConfiguredToken()) {
            return [$botInfo, $subscriptions, $errors];
        }

        try {
            $botInfo = $this->maxApiClient->getMe()->toArray();
        } catch (MaxApiException $exception) {
            $errors['bot_info'] = $exception->getMessage();
        }

        try {
            $subscriptions = $this->maxApiClient->getSubscriptions();
        } catch (MaxApiException $exception) {
            $errors['subscriptions'] = $exception->getMessage();
        }

        return [$botInfo, $subscriptions, $errors];
    }

    /**
     * @return array<string, mixed>
     */
    private function configStatus(): array
    {
        $webhookUrl = $this->webhookUrl();

        return [
            'token_configured' => $this->hasConfiguredToken(),
            'webhook_url' => $webhookUrl,
            'webhook_url_https' => $webhookUrl !== null && str_starts_with($webhookUrl, 'https://'),
            'secret_configured' => (string) config('max.webhook.secret', '') !== '',
            'update_types' => (array) config('max.webhook.update_types', []),
            'storage_disk' => (string) config('max.webhook.storage_disk', 'local'),
            'storage_path' => (string) config('max.webhook.storage_path', 'max/webhooks'),
            'history_limit' => (int) config('max.webhook.history_limit', 50),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventListItem(StoredWebhookFile $file): array
    {
        return [
            'id' => $file->id,
            'relative_path' => $file->relativePath,
            'stored_at' => $file->storedAt,
            'summary' => $this->normalizer->summarizeFromRaw($file->rawJson),
        ];
    }

    private function hasConfiguredToken(): bool
    {
        return (string) config('max.api.token', '') !== '';
    }

    private function webhookUrl(): ?string
    {
        $webhookUrl = trim((string) config('max.webhook.url', ''));

        if ($webhookUrl === '') {
            return null;
        }

        return $webhookUrl;
    }

    private function redirectWithMessage(string $level, string $message): RedirectResponse
    {
        return redirect()
            ->route('max.debug.index')
            ->with('max_debug_status_level', $level)
            ->with('max_debug_status_message', $message);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function prettyJson(?array $payload): ?string
    {
        if ($payload === null) {
            return null;
        }

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : null;
    }
}
