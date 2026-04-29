[![Actions Status](https://github.com/yaleksandr89/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/yaleksandr89/php-project-9/actions)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=yaleksandr89_php-project-9&metric=bugs)](https://sonarcloud.io/summary/new_code?id=yaleksandr89_php-project-9)

---

# Анализатор страниц

## Описание

Веб-приложение для анализа сайтов.

Позволяет:

* добавлять URL
* проверять доступность страниц
* сохранять историю проверок
* выполнять базовый SEO-анализ (*h1*, *title*, *description*)

## Требования

* `PHP >= 8.4`
* `Composer`
* `Make`
* `PostgreSQL`

---

## Установка (локально)

Для корректной работы на ПК должны быть установлены PHP и PostgreSQL.

```bash
make install
```

## Настройка окружения

Необходимо задать переменную окружения: `DATABASE_URL`

Пример:

```
postgresql://user:password@localhost:5432/dbname
```

## Инициализация базы данных

```bash
psql -d "<DATABASE_URL>" -f database.sql
```

Пример:

```bash
psql -d "postgresql://user:password@localhost:5432/dbname" -f database.sql
```

## Запуск

```bash
make start
```

Приложение будет доступно по адресу: http://localhost:8000

---

## Запуск через Docker

Если на ПК не установлены PHP и PostgreSQL, проект можно запустить через Docker.

### Первый запуск

**Поднять контейнеры:**

```bash
make docker-up
```

**Инициализировать базу данных:**

```bash
make docker-db-init
```

После этого приложение будет доступно по адресу:

http://localhost:8000

### Повторный запуск

```bash
make docker-up
```

### Остановка контейнеров

```bash
make docker-down
```

### Просмотр логов

```bash
make docker-logs
```

### Вход в контейнер приложения

```bash
make docker-shell
```

### Когда нужно выполнять `docker-db-init`

Команду нужно запускать только если:

* это первый запуск проекта
* база данных пустая
* был удалён Docker volume
* база была пересоздана

---

## Функциональность

* Добавление сайтов
* Проверка доступности (HTTP статус)
* Хранение истории проверок
* Отображение последней проверки
* SEO-анализ страницы:
  * *h1*
  * *title*
  * *meta description*

---

## Технологии

* `Slim Framework`
* `PDO`
* `PostgreSQL`
* `Guzzle` (HTTP-клиент)
* `Symfony DomCrawler` + `CSS Selector`

---

## Архитектура

* `Controllers` — обработка HTTP-запросов
* `Services` — бизнес-логика
* `Repositories` — работа с базой данных
* `Support` — вспомогательные классы

---

## Демонстрация работы

### Успешный сценарий

Демонстрация валидного сценария:

* Яндекс.Диск: https://disk.yandex.ru/i/X7tV9hu9GM5ysg
* Google Drive: https://drive.google.com/file/d/1tXdQczdzpKv2FxxWfvPHegZQ1l0I76-Y/view

---

### Невалидные сценарии

Демонстрация невалидных сценариев:
* 
* Яндекс.Диск: https://disk.yandex.ru/i/miBr4q1A8qQHhA
* Google Drive: https://drive.google.com/file/d/1AFUGtV1J_JVHIYBG8xxpnwCdm1vd_LF7/view?usp=sharing
