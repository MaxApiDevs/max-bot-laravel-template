# MAX Bot Laravel Template

Пример Laravel-проекта для интеграции с чат-ботом MAX.

Сейчас репозиторий сфокусирован на двух задачах:

- показать минимальный и понятный пример Laravel-интеграции с MAX Bot API;
- дать удобный debug-flow: подключение webhook, удаление webhook и просмотр истории входящих payload.

## Что уже реализовано

- debug-страница для работы с webhook: `http://localhost:8000/debug/max`
- webhook endpoint в формате `max/{MAX_BOT_TOKEN}/webhook`
- работа с MAX API через `/me` и `/subscriptions`
- сохранение каждого входящего webhook в отдельный JSON-файл
- просмотр raw payload и normalized view в debug UI

## Требования

- Docker Desktop или совместимый Docker с `docker compose`

Локально `php` и `composer` на хосте не обязательны, если вы запускаете проект только через Docker.

## Быстрый старт

1. Скопируйте пример env:

```bash
cp .env.example .env
```

Если вы на Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

2. Заполните минимум эти переменные в `.env`:

```env
MAX_BOT_TOKEN=ваш_токен_бота
MAX_WEBHOOK_URL=https://ваш-публичный-домен/max/ваш_токен_бота/webhook
MAX_WEBHOOK_SECRET=любой_секрет_для_проверки_вебхука
MAX_DEBUG_UI_ENABLED=true
```

3. Запустите проект:

```bash
docker compose up --build
```

4. Откройте:

- приложение: `http://localhost:8000`
- debug-страницу: `http://localhost:8000/debug/max`

## Как настроить webhook правильно

В проекте входящий маршрут строится автоматически из `MAX_BOT_TOKEN`:

```text
max/{MAX_BOT_TOKEN}/webhook
```

Пример:

```text
MAX_BOT_TOKEN=my_bot_token
```

Тогда локальный путь webhook будет таким:

```text
/max/my_bot_token/webhook
```

Переменная `MAX_WEBHOOK_URL` должна содержать полный публичный URL, который вы регистрируете в MAX.

Пример:

```env
MAX_BOT_TOKEN=my_bot_token
MAX_WEBHOOK_URL=https://example.ngrok-free.app/max/my_bot_token/webhook
```

Важно:

- `MAX_WEBHOOK_URL` должен совпадать с реальным публичным адресом, по которому MAX сможет достучаться до Laravel;
- если URL в `.env` не совпадает с реальным маршрутом, webhook не будет работать корректно;
- для работы webhook нужен публичный HTTPS URL.

## Рекомендуемая локальная схема

Обычно удобно так:

1. Поднять Laravel в Docker.
2. Поднять публичный tunnel через ngrok, cloudflared или аналог.
3. Взять публичный HTTPS URL.
4. Подставить его в `MAX_WEBHOOK_URL` с путём `/max/{MAX_BOT_TOKEN}/webhook`.
5. Открыть `/debug/max`.
6. Нажать `Подключить вебхук`.

## Основные env-переменные

### MAX API

```env
MAX_BOT_TOKEN=
MAX_BOT_API_BASE_URL=https://platform-api.max.ru
MAX_BOT_TIMEOUT=10
MAX_BOT_CONNECT_TIMEOUT=5
```

### Webhook

```env
MAX_WEBHOOK_URL=
MAX_WEBHOOK_SECRET=
MAX_WEBHOOK_UPDATE_TYPES=message_created,message_callback,user_added,bot_started
```

### Хранение входящих webhook

```env
MAX_WEBHOOK_STORAGE_DISK=local
MAX_WEBHOOK_STORAGE_PATH=max/webhooks
MAX_WEBHOOK_HISTORY_LIMIT=50
```

### Debug UI

```env
MAX_DEBUG_UI_ENABLED=true
```

## Как работает история webhook

- каждый входящий webhook сохраняется как отдельный JSON-файл;
- сохраняется только raw payload от MAX, без дополнительной служебной обёртки;
- файлы пишутся в `storage/app/private/max/webhooks`, если используется стандартный диск `local`;
- debug-страница читает эти файлы и показывает:
  - краткую сводку по событию;
  - raw JSON;
  - normalized view для удобного разбора.

## Что делает debug-страница

Страница `/debug/max` показывает:

- задан ли `MAX_BOT_TOKEN`
- какой `MAX_WEBHOOK_URL` сейчас настроен
- включён ли secret
- список `update_types`
- информацию о боте через `GET /me`
- текущие подписки через `GET /subscriptions`
- кнопки:
  - `Подключить вебхук`
  - `Удалить вебхук`
- историю последних входящих webhook

## Docker и база данных

В `docker-compose.yml` сейчас остаётся MariaDB-контейнер, но текущая история webhook от неё не зависит.

Для webhook debug-flow данные входящих событий сохраняются в JSON-файлы, а не в БД.

## Полезные ссылки

- MAX docs: [Подготовка и настройка бота](https://dev.max.ru/docs/chatbots/bots-coding/prepare)
- MAX API: [GET /me](https://dev.max.ru/docs-api/methods/GET/me)
- MAX API: [GET /subscriptions](https://dev.max.ru/docs-api/methods/GET/subscriptions)
- MAX API: [POST /subscriptions](https://dev.max.ru/docs-api/methods/POST/subscriptions)
- MAX API: [DELETE /subscriptions](https://dev.max.ru/docs-api/methods/DELETE/subscriptions)
- MAX API: [Update object](https://dev.max.ru/docs-api/objects/Update)

## Текущее ограничение

Проект пока сфокусирован на debug/example-слое вокруг webhook и MAX API. Это не готовая библиотека и не production framework для сложной бот-логики.

Следующая цель развития репозитория: добавить минимальный bot flow поверх уже готового webhook/debug слоя и сохранить архитектуру удобной для будущего выноса MAX API слоя в отдельный open-source проект.
