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

## Проверка TypeScript

```powershell
cd infra
docker compose exec node npm run check
```
