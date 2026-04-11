### Статус Hexlet tests и линтера:

[![Actions Status](https://github.com/yaleksandr89/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/yaleksandr89/php-project-9/actions)

# Анализатор страниц

Демо: https://php-project-9-4jym.onrender.com

## Описание

Веб-приложение для анализа сайтов. Позволяет добавлять URL, выполнять проверки доступности и проводить базовый SEO-анализ (h1, title, description).

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

Перед запуском необходимо создать таблицы:

```bash
psql -d "<DATABASE_URL>" -f database.sql
```

Пример:

```bash
psql -d "postgresql://user:password@localhost:5432/dbname" -f database.sql
```

## Функциональность

* Добавление сайтов
* Проверка доступности (HTTP статус)
* Хранение истории проверок
* Отображение последней проверки в списке сайтов
* SEO-анализ страницы:

    * h1
    * title
    * meta description

## Технологии

* Slim Framework
* PDO
* PostgreSQL
* Guzzle (HTTP-клиент)
* Symfony DomCrawler + CSS Selector

## Примечание

Для корректной работы с helper-функциями необходимо выполнить:

```bash
composer dump-autoload
```
