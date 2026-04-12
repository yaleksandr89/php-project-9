[![Actions Status](https://github.com/yaleksandr89/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/yaleksandr89/php-project-9/actions)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=bugs)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)

---

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

## Демонстрация работы

### Успешный сценарий

Youtube:

[![Демонстрация валидного сценария](https://img.youtube.com/vi/2oLmo9_yi_8/0.jpg)](https://www.youtube.com/watch?v=2oLmo9_yi_8)

Альтернативные ссылки:
* Яндекс.Диск: https://disk.yandex.ru/i/X7tV9hu9GM5ysg
* Google Drive: https://drive.google.com/file/d/1tXdQczdzpKv2FxxWfvPHegZQ1l0I76-Y/view

### Невалидный сценарий

Youtube:

[![Демонстрация невалидного сценария](https://img.youtube.com/vi/GKXIsw_aYZQ/0.jpg)](https://youtu.be/GKXIsw_aYZQ)

Альтернативные ссылки:
* Яндекс.Диск: https://disk.yandex.ru/i/miBr4q1A8qQHhA
* Google Drive: https://drive.google.com/file/d/1AFUGtV1J_JVHIYBG8xxpnwCdm1vd_LF7/view?usp=sharing

