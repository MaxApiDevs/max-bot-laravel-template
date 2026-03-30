<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Webhook Event</title>
        <style>
            :root {
                color-scheme: light;
                --bg: #f3efe8;
                --surface: #fffaf2;
                --line: #d5cec2;
                --text: #2f281f;
                --muted: #6d6558;
                --accent: #b65a15;
                --mono: "Cascadia Code", "SFMono-Regular", Consolas, monospace;
                --sans: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: var(--sans);
                background: linear-gradient(180deg, #f9f6f1 0%, var(--bg) 100%);
                color: var(--text);
            }

            .page {
                max-width: 1200px;
                margin: 0 auto;
                padding: 28px 20px 40px;
            }

            .back {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 18px;
                color: var(--accent);
                text-decoration: none;
                font-weight: 700;
            }

            .stack {
                display: grid;
                gap: 18px;
            }

            .card {
                background: var(--surface);
                border: 1px solid var(--line);
                border-radius: 18px;
                padding: 20px;
                box-shadow: 0 14px 30px rgba(70, 47, 15, 0.06);
            }

            .meta-list {
                display: grid;
                grid-template-columns: 170px 1fr;
                gap: 8px 14px;
                margin: 0;
            }

            .meta-list dt {
                color: var(--muted);
                font-weight: 700;
            }

            .meta-list dd {
                margin: 0;
                word-break: break-word;
            }

            pre,
            code {
                font-family: var(--mono);
            }

            pre {
                margin: 0;
                white-space: pre-wrap;
                word-break: break-word;
                background: #2a231c;
                color: #f8eedf;
                border-radius: 14px;
                padding: 14px;
                line-height: 1.55;
                overflow-x: auto;
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 18px;
            }

            h1,
            h2 {
                margin: 0 0 14px;
            }

            .mono {
                font-family: var(--mono);
            }

            @media (max-width: 720px) {
                .meta-list {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <main class="page stack">
            <a class="back" href="{{ route('max.debug.index') }}">← Назад к debug-странице</a>

            <section class="card">
                <h1>Webhook Event</h1>
                <dl class="meta-list">
                    <dt>ID</dt>
                    <dd class="mono">{{ $event['id'] }}</dd>

                    <dt>Файл</dt>
                    <dd class="mono">{{ $event['relative_path'] }}</dd>

                    <dt>Сохранён</dt>
                    <dd class="mono">{{ $event['stored_at']->format('Y-m-d H:i:s') }} UTC</dd>

                    <dt>Update Type</dt>
                    <dd><code>{{ $event['summary']['update_type'] ?? 'unknown' }}</code></dd>

                    <dt>User ID</dt>
                    <dd>{{ $event['summary']['user_id'] ?? '—' }}</dd>

                    <dt>Chat ID</dt>
                    <dd>{{ $event['summary']['chat_id'] ?? '—' }}</dd>

                    <dt>Parse Status</dt>
                    <dd>{{ $event['summary']['parse_status'] }}</dd>
                </dl>
            </section>

            <section class="grid">
                <article class="card">
                    <h2>Raw Payload</h2>
                    <pre>{{ $event['raw_pretty'] }}</pre>
                </article>

                <article class="card">
                    <h2>Normalized View</h2>
                    <pre>{{ $event['normalized_pretty'] }}</pre>
                </article>
            </section>
        </main>
    </body>
</html>
