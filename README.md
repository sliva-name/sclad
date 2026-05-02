# ShopDad Inventory MVP

MVP системы учета товара на `Laravel 13` + `MoonShine` с Docker и PostgreSQL.

## Что реализовано

- Учет товаров с ценами, фото, характеристиками (`JSON`) и мягким удалением/восстановлением.
- Учет движений склада: приход, продажа, корректировка.
- Продажи со списанием остатка и автоматической записью движения.
- Простой отчет по продажам за период + топ товаров.
- Админка MoonShine (`/admin`) с ресурсами:
  - Products
  - Stock movements
  - Sales

## Запуск в Docker

1. (Рекомендуется) выровнять UID/GID контейнера под пользователя хоста:

```bash
echo "UID=$(id -u)" >> .env
echo "GID=$(id -g)" >> .env
```

1. Поднять контейнеры:

```bash
docker compose up -d --build
```

1. Установить PHP-зависимости внутри контейнера `app` (если не установлены):

```bash
docker compose exec app composer install
```

1. Сгенерировать ключ приложения:

```bash
docker compose exec app php artisan key:generate
```

1. Применить миграции:

```bash
docker compose exec app php artisan migrate
```

1. Создать пользователя MoonShine:

```bash
docker compose exec app php artisan moonshine:user
```

## Точки входа

- Приложение: `http://localhost:8080`
- MoonShine: `http://localhost:8080/admin`
- Отчеты: `http://localhost:8080/admin/page/sales-report-page`

## Единая среда: хост + контейнер

- Файлы из `storage/app/public` общие для хоста и контейнера (bind mount), поэтому загрузки и фото видны в обоих окружениях.
- В `.env` по умолчанию `DB_HOST=127.0.0.1` (удобно для запуска команд на хосте).
- Для контейнера `app` `DB_HOST` переопределяется в `docker-compose` на `postgres`.

## Бизнес-поток MVP

1. Создать товар (`Products`).
2. Зафиксировать приход в `Stock movements` (`type = inbound`).
3. Фиксировать продажи в `Sales`.
4. Остаток товара уменьшается автоматически.
5. Смотреть сводку в `Sales reports`.
