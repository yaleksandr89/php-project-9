### Статус Hexlet tests и линтера:

[![Actions Status](https://github.com/yaleksandr89/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/yaleksandr89/php-project-9/actions)

# Анализатор страниц

Демо: https://php-project-9-4jym.onrender.com

## Требования

* PHP >= 8.4
* Composer
* Make
* PostgreSQL

## Переменные окружения

Для работы приложения требуется переменная окружения:

`DATABASE_URL` - строка подключения к PostgreSQL

Пример: `postgresql://user:password@localhost:5432/dbname`

## Установка и запуск

```bash
make install
make start
```

Открыть в браузере: http://localhost:8000

## Инициализация базы данных

Перед запуском необходимо создать таблицы: `psql -d <DATABASE_URL> -f database.sql`
