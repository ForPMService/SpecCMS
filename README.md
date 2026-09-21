# SpecCMS

Демонстрационная CMS для проверки подхода к замене Tilda.

## Локальные сервисы

| Сервис | Адрес |
|---|---|
| Laravel | http://localhost:8000 |
| Filament | http://localhost:8000/admin |
| Silex | http://localhost:6805 |
| Live / Nginx | http://localhost:8080 |
| PostgreSQL | localhost:5433 |

## Запуск

```powershell
.\infra\up.ps1
```

Остановка:

```powershell
.\infra\down.ps1
```

## Первый запуск после клонирования

Для чистого клона на Windows с установленными Docker Desktop и Git выполните:

```bash
Copy-Item backend/.env.example backend/.env
docker compose -f infra/docker-compose.yml build
docker compose -f infra/docker-compose.yml run --rm php composer install
docker compose -f infra/docker-compose.yml run --rm php php artisan key:generate
docker compose -f infra/docker-compose.yml run --rm node npm ci
.\infra\up.ps1
docker compose -f infra/docker-compose.yml exec php php artisan migrate
```

При последующих запусках достаточно выполнить `.\infra\up.ps1`.

Рабочая база PostgreSQL: `speccms`.

Тестовая база PostgreSQL: `speccms_testing`. `.\infra\up.ps1` обеспечивает её наличие.

Для остановки используйте `.\infra\down.ps1`.

Каталоги `vendor`, `node_modules` и локальный `.env` в Git не хранятся.

Первый пользователь Filament создаётся отдельно:

```bash
docker compose -f infra/docker-compose.yml exec php php artisan make:filament-user
```

## Основные версии

* PHP 8.4.25
* Composer 2.10.3
* Laravel 13.32.0
* PostgreSQL 18.6
* Node 24.21.0
* npm 11.19.0
* Silex 3.9.0
* Nginx 1.30.5
* Filament 5.8.4

## Проверка PHP

```powershell
cd infra
docker compose exec php composer check
```

Эта команда запускает PHPUnit против `speccms_testing`.

## Проверка TypeScript

```powershell
cd infra
docker compose exec node npm run check
```
