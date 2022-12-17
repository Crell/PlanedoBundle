compose_command = docker-compose run -u $(id -u ${USER}):$(id -g ${USER}) --rm php81

.PHONY: build
build:
	docker-compose build

.PHONY: shell
shell: build
	$(compose_command) bash

.PHONY: destroy
destroy:
	docker-compose down -v

.PHONY: composer
composer: build
	$(compose_command) composer install

.PHONY: test
test: build
	$(compose_command) vendor/bin/phpunit

.PHONY: phpstan
phpstan: build
	$(compose_command) vendor/bin/phpstan
