<?php

namespace App\Modules\Max\Http;

use App\Modules\Max\Contracts\MaxApiClientInterface;
use App\Modules\Max\DTO\BotInfo;
use App\Modules\Max\Exceptions\MaxApiException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class MaxApiClient implements MaxApiClientInterface
{
    public function __construct(
        private readonly Factory $http,
    ) {
    }

    public function getMe(): BotInfo
    {
        $response = $this->baseRequest()->get('/me');

        $this->ensureSuccessful($response, 'Не удалось получить информацию о боте');

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return BotInfo::fromArray($payload);
    }

    public function getSubscriptions(): array
    {
        $response = $this->baseRequest()->get('/subscriptions');

        $this->ensureSuccessful($response, 'Не удалось получить список подписок');

        $subscriptions = $response->json('subscriptions', []);

        return is_array($subscriptions) ? array_values($subscriptions) : [];
    }

    public function subscribeWebhook(string $url, array $updateTypes, ?string $secret = null): array
    {
        $payload = [
            'url' => $url,
            'update_types' => array_values(array_filter($updateTypes, static fn (mixed $type): bool => is_string($type) && $type !== '')),
        ];

        if ($secret !== null && $secret !== '') {
            $payload['secret'] = $secret;
        }

        $response = $this->baseRequest()->post('/subscriptions', $payload);

        $this->ensureSuccessful($response, 'Не удалось подключить вебхук');

        /** @var array<string, mixed> $result */
        $result = $response->json();

        return $result;
    }

    public function deleteWebhook(string $url): array
    {
        $response = $this->baseRequest()->send('DELETE', '/subscriptions', [
            'query' => ['url' => $url],
        ]);

        $this->ensureSuccessful($response, 'Не удалось удалить вебхук');

        /** @var array<string, mixed> $result */
        $result = $response->json();

        return $result;
    }

    private function baseRequest(): PendingRequest
    {
        $token = (string) config('max.api.token', '');

        if ($token === '') {
            throw new MaxApiException('Не задан MAX_BOT_TOKEN.');
        }

        return $this->http
            ->baseUrl((string) config('max.api.base_url'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Authorization' => $token,
            ])
            ->timeout((int) config('max.api.timeout', 10))
            ->connectTimeout((int) config('max.api.connect_timeout', 5));
    }

    private function ensureSuccessful(Response $response, string $operation): void
    {
        if ($response->successful()) {
            return;
        }

        $message = $response->json('message');

        if (! is_string($message) || $message === '') {
            $message = trim($response->body());
        }

        if ($message === '') {
            $message = 'MAX API вернул неизвестную ошибку.';
        }

        throw new MaxApiException(sprintf('%s: %s', $operation, $message), $response->status());
    }
}
