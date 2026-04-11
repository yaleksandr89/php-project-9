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
	composer exec ./vendor/bin/phpcs -- --standard=PSR12 config/ public/ templates/ src/ routes.php

lint-fix:
	composer exec --verbose phpcbf -- config/ public/ templates/ src/ routes.php
