SHELL := bash

.PHONY: install start lint lint-fix docker-init docker-build docker-up docker-db-init docker-down docker-logs docker-shell
	
# Для работы с локальным сервером

PORT ?= 8000

install:
	composer install && composer dump-autoload -o

validate:
	composer validate

composer-optimization:
	composer dump-autoload -o

start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

lint:
	composer exec ./vendor/bin/phpcs -- --standard=PSR12 config/ public/ templates/ src/

lint-fix:
	composer exec --verbose phpcbf -- config/ public/ templates/ src/

# Работа с Docker

# Путь к env-файлу Docker
DOCKER_ENV_FILE := docker/.env
DOCKER_ENV_EXAMPLE := docker/.env.example

# Команда docker compose с созданным .env файлом
DOCKER_COMPOSE := docker compose --env-file $(DOCKER_ENV_FILE)

# Инициализация окружения Docker
# Создает файл docker/.env из шаблона, если его еще нет
docker-init:
	if [ ! -f "$(DOCKER_ENV_FILE)" ]; then cp $(DOCKER_ENV_EXAMPLE) $(DOCKER_ENV_FILE); fi

# Инициализация базы данных
# Прогоняет файл database.sql вручную
# Используется после первого запуска контейнеров или на новой/пустой БД
docker-db-init: docker-init
	$(DOCKER_COMPOSE) exec app bash docker/postgres/init.sh

# Сборка Docker-образов
# Нужна при первом запуске или после изменения Dockerfile / docker-compose / зависимостей
docker-build: docker-init
	$(DOCKER_COMPOSE) build

# Запуск контейнеров приложения и базы данных
docker-up: docker-init
	$(DOCKER_COMPOSE) up -d --build

# Остановка и удаление контейнеров, сети
docker-down: docker-init
	$(DOCKER_COMPOSE) down --remove-orphans

# Просмотр логов всех сервисов
docker-logs: docker-init
	$(DOCKER_COMPOSE) logs -f

# Вход внутрь контейнера приложения
docker-shell: docker-init
	$(DOCKER_COMPOSE) exec app bash
