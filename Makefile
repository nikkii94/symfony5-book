app_container_name=sf5_app
nginx_container_name=sf5_nginx

build:
	@docker-compose -f docker-compose.yml build

start:
	@docker-compose -f docker-compose.yml up -d && start https://symfony5book.local/

stop:
	@docker-compose stop

down:
	@docker-compose down

config:
	@docker-compose -f docker-compose.yml config

ssh:
	@docker exec -it $(app_container_name) bash

export-ssl:
	rm -rf certs
	@docker cp $(nginx_container_name):/etc/ssl ./certs

#check: composer-validate cs-check phpstan psalm
#
#composer-validate: ensure composer-normalize-check
#	sh -c "${PHPQA_DOCKER_COMMAND} composer validate"
