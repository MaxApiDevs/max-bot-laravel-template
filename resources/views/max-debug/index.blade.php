<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MAX Debug</title>
        <style>
            :root {
                color-scheme: light;
                --bg: #f4f1ea;
                --surface: #fffaf2;
                --surface-strong: #ffffff;
                --line: #d6cfc4;
                --text: #2d261d;
                --muted: #6d6558;
                --accent: #b65a15;
                --accent-strong: #8e430b;
                --success-bg: #e8f7ea;
                --success-text: #24633b;
                --error-bg: #fdeaea;
                --error-text: #922c2c;
                --mono: "Cascadia Code", "SFMono-Regular", Consolas, monospace;
                --sans: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: var(--sans);
                background:
                    radial-gradient(circle at top left, rgba(182, 90, 21, 0.10), transparent 28%),
                    linear-gradient(180deg, #f7f2ea 0%, var(--bg) 100%);
                color: var(--text);
            }

            .page {
                max-width: 1200px;
                margin: 0 auto;
                padding: 32px 20px 48px;
            }

            .hero {
                display: grid;
                gap: 12px;
                margin-bottom: 24px;
            }

            .eyebrow {
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                color: var(--accent-strong);
                font-weight: 700;
            }

            h1,
            h2,
            h3 {
                margin: 0;
            }

            h1 {
                font-size: clamp(2rem, 3vw, 3.2rem);
                line-height: 1;
            }

            .subhead {
                max-width: 760px;
                color: var(--muted);
                line-height: 1.6;
                margin: 0;
            }

            .grid {
                display: grid;
                gap: 18px;
            }

            .grid.two {
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            }

            .card {
                background: linear-gradient(180deg, var(--surface-strong), var(--surface));
                border: 1px solid var(--line);
                border-radius: 18px;
                padding: 20px;
                box-shadow: 0 14px 32px rgba(70, 47, 15, 0.08);
            }

            .card h2 {
                margin-bottom: 14px;
                font-size: 1.1rem;
            }

            .meta-list {
                display: grid;
                grid-template-columns: 180px 1fr;
                gap: 8px 14px;
                margin: 0;
            }

            .meta-list dt {
                color: var(--muted);
                font-weight: 600;
            }

            .meta-list dd {
                margin: 0;
                word-break: break-word;
            }

            .status-pill {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border-radius: 999px;
                font-size: 0.9rem;
                font-weight: 700;
            }

            .status-pill.success {
                background: var(--success-bg);
                color: var(--success-text);
            }

            .status-pill.error {
                background: var(--error-bg);
                color: var(--error-text);
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

            button,
            .link-button {
                border: 0;
                border-radius: 12px;
                padding: 12px 16px;
                font: inherit;
                font-weight: 700;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: transform 120ms ease, opacity 120ms ease;
            }

            button:hover,
            .link-button:hover {
                transform: translateY(-1px);
                opacity: 0.95;
            }

            .button-primary {
                background: var(--accent);
                color: white;
            }

            .button-secondary {
                background: #ece6dd;
                color: var(--text);
            }

            .button-danger {
                background: #5c2929;
                color: white;
            }

            .notes {
                margin: 0;
                padding-left: 18px;
                color: var(--muted);
                line-height: 1.6;
            }

            .notes li + li {
                margin-top: 8px;
            }

            .table-wrap {
                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                text-align: left;
                padding: 12px 10px;
                border-bottom: 1px solid var(--line);
                vertical-align: top;
            }

            th {
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--muted);
            }

            code,
            pre,
            .mono {
                font-family: var(--mono);
            }

            pre {
                margin: 0;
                white-space: pre-wrap;
                word-break: break-word;
                background: #2a231c;
                color: #f9f0df;
                border-radius: 14px;
                padding: 14px;
                line-height: 1.55;
                overflow-x: auto;
            }

            details + details {
                margin-top: 12px;
            }

            summary {
                cursor: pointer;
                font-weight: 700;
            }

            .muted {
                color: var(--muted);
            }

            .stack {
                display: grid;
                gap: 14px;
            }

            @media (max-width: 720px) {
                .meta-list {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <main class="page">
            <section class="hero">
                <div class="eyebrow">MAX Bot Laravel</div>
                <h1>MAX Debug</h1>
                <p class="subhead">
                    Первый рабочий срез под webhook-based интеграцию: управление подпиской,
                    просмотр входящих raw payload и on-the-fly normalizer для реальных MAX Update.
                </p>
            </section>

            @if (session('max_debug_status_message'))
                <div class="card" style="margin-bottom: 18px;">
                    <div class="status-pill {{ session('max_debug_status_level', 'success') === 'error' ? 'error' : 'success' }}">
                        {{ session('max_debug_status_level', 'success') === 'error' ? 'Ошибка' : 'Готово' }}
                        <span>{{ session('max_debug_status_message') }}</span>
                    </div>
                </div>
            @endif

            <section class="grid two">
                <article class="card">
                    <h2>Конфигурация</h2>
                    <dl class="meta-list">
                        <dt>Токен</dt>
                        <dd>{{ $configStatus['token_configured'] ? 'Сконфигурирован' : 'Не задан' }}</dd>

                        <dt>Webhook URL</dt>
                        <dd>{{ $configStatus['webhook_url'] ?: 'Не задан MAX_WEBHOOK_URL' }}</dd>

                        <dt>HTTPS</dt>
                        <dd>{{ $configStatus['webhook_url_https'] ? 'Да' : 'Нет' }}</dd>

                        <dt>Secret</dt>
                        <dd>{{ $configStatus['secret_configured'] ? 'Сконфигурирован' : 'Не задан' }}</dd>

                        <dt>Update types</dt>
                        <dd><code>{{ implode(', ', $configStatus['update_types']) }}</code></dd>

                        <dt>Storage</dt>
                        <dd><code>{{ $configStatus['storage_disk'] }}:{{ $configStatus['storage_path'] }}</code></dd>

                        <dt>История</dt>
                        <dd>{{ $configStatus['history_limit'] }} последних webhook</dd>
                    </dl>
                </article>

                <article class="card stack">
                    <div>
                        <h2>Управление Webhook</h2>
                        <p class="muted">
                            Действия ходят в MAX API через <code>/subscriptions</code>.
                            В первой версии debug-панель доступна только когда включён <code>MAX_DEBUG_UI_ENABLED</code>.
                        </p>
                    </div>

                    <div class="actions">
                        <form method="post" action="{{ route('max.debug.subscriptions.connect') }}">
                            @csrf
                            <button class="button-primary" type="submit">Подключить вебхук</button>
                        </form>

                        <form method="post" action="{{ route('max.debug.subscriptions.disconnect') }}">
                            @csrf
                            <button class="button-danger" type="submit">Удалить вебхук</button>
                        </form>
                    </div>

                    <ul class="notes">
                        <li>MAX ожидает быстрый <code>200 OK</code> на webhook endpoint.</li>
                        <li>Для production рекомендуется HTTPS-endpoint и проверка <code>X-Max-Bot-Api-Secret</code>.</li>
                        <li>Файловое хранилище сохраняет только raw payload, без служебной обёртки.</li>
                        <li>Входящий маршрут строится как <code>max/{MAX_BOT_TOKEN}/webhook</code>.</li>
                    </ul>
                </article>
            </section>

            <section class="grid two" style="margin-top: 18px;">
                <article class="card stack">
                    <div>
                        <h2>Информация о боте</h2>
                        @if (isset($apiErrors['bot_info']))
                            <div class="status-pill error">{{ $apiErrors['bot_info'] }}</div>
                        @elseif ($botInfo)
                            <dl class="meta-list">
                                <dt>User ID</dt>
                                <dd>{{ $botInfo['user_id'] ?? '—' }}</dd>

                                <dt>Имя</dt>
                                <dd>{{ $botInfo['first_name'] ?? '—' }}</dd>

                                <dt>Username</dt>
                                <dd>{{ $botInfo['username'] ?? '—' }}</dd>

                                <dt>Description</dt>
                                <dd>{{ $botInfo['description'] ?? '—' }}</dd>
                            </dl>
                        @else
                            <p class="muted">Информация о боте появится здесь после настройки токена.</p>
                        @endif
                    </div>

                    @if ($botInfo)
                        <details>
                            <summary>Показать raw ответ <code>/me</code></summary>
                            <pre>{{ json_encode($botInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    @endif
                </article>

                <article class="card stack">
                    <div>
                        <h2>Подписки</h2>
                        @if (isset($apiErrors['subscriptions']))
                            <div class="status-pill error">{{ $apiErrors['subscriptions'] }}</div>
                        @elseif ($subscriptions === [])
                            <p class="muted">MAX API пока не вернул активных подписок или токен ещё не настроен.</p>
                        @else
                            @foreach ($subscriptions as $subscription)
                                <details>
                                    <summary>{{ $subscription['url'] ?? 'Webhook subscription' }}</summary>
                                    <pre>{{ json_encode($subscription, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endforeach
                        @endif
                    </div>
                </article>
            </section>

            <section class="card" style="margin-top: 18px;">
                <h2>История входящих webhook</h2>

                @if ($events === [])
                    <p class="muted">История пока пустая. Как только MAX пришлёт первый webhook, здесь появятся raw payload.</p>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Время</th>
                                    <th>Update Type</th>
                                    <th>User ID</th>
                                    <th>Chat ID</th>
                                    <th>Статус</th>
                                    <th>Файл</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($events as $event)
                                    <tr>
                                        <td class="mono">{{ $event['stored_at']->format('Y-m-d H:i:s') }} UTC</td>
                                        <td><code>{{ $event['summary']['update_type'] ?? 'unknown' }}</code></td>
                                        <td>{{ $event['summary']['user_id'] ?? '—' }}</td>
                                        <td>{{ $event['summary']['chat_id'] ?? '—' }}</td>
                                        <td>{{ $event['summary']['parse_status'] }}</td>
                                        <td class="mono">{{ $event['relative_path'] }}</td>
                                        <td>
                                            <a class="link-button button-secondary" href="{{ route('max.debug.events.show', $event['id']) }}">
                                                Открыть
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </main>
    </body>
</html>
