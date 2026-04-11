[![Actions Status](https://github.com/yaleksandr89/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/yaleksandr89/php-project-9/actions)

# Анализатор страниц

Демо: https://php-project-9-4jym.onrender.com

## Описание

Веб-приложение для анализа сайтов.

Позволяет:

* добавлять URL
* проверять доступность страниц
* сохранять историю проверок
* выполнять базовый SEO-анализ (_h1_, _title_, _description_)

## Требования

* `PHP >= 8.4`
* `Composer`
* `Make`
* `PostgreSQL`

## Установка

```bash
make install
```

## Настройка окружения

Необходимо задать переменную окружения: `DATABASE_URL`

Пример: `postgresql://user:password@localhost:5432/dbname`

## Инициализация базы данных

```bash
psql -d "<DATABASE_URL>" -f database.sql
```

Пример: `psql -d "postgresql://user:password@localhost:5432/dbname" -f database.sql`

## Запуск

```bash
make start
```

Приложение будет доступно по адресу: http://localhost:8000

## Функциональность

* Добавление сайтов
* Проверка доступности (HTTP статус)
* Хранение истории проверок
* Отображение последней проверки
* SEO-анализ страницы:
  * _h1_
  * _title_
  * _meta description_

## Технологии

* `Slim Framework`
* `PDO`
* `PostgreSQL`
* `Guzzle` (HTTP-клиент)
* `Symfony DomCrawler` + `CSS Selector`

## Архитектура

* `Controllers` - обработка HTTP-запросов
* `Services` - бизнес-логика
* `Repositories` - работа с базой данных
* `Support` - вспомогательные классы (форматирование, подготовка данных)
