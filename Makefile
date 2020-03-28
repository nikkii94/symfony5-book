SHELL := /bin/bash
app_container_name=sf5_app
nginx_container_name=sf5_nginx
postgres_container_name=symfony5-book_database_1

postgres_user=main
postgres_pw=main
postgres_db=main

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

exec:
	@docker exec -it $(app_container_name) $$cmd

export-ssl:
	rm -rf certs
	@docker cp $(nginx_container_name):/etc/ssl ./certs

inspect:
	@docker inspect -f '{{ json .Mounts }}' $(postgres_container_name) | python -m json.tool

#https://devopsheaven.com/postgresql/pg_dump/databases/docker/backup/2017/09/10/backup-postgresql-database-using-docker.html
#https://gist.github.com/gilyes/525cc0f471aafae18c3857c27519fc4b
dump-sql:
	@docker exec $(postgres_container_name) pg_dump $(postgres_db) -U $(postgres_user) > dump/dump_`date +%Y-%m-%d"_"%H_%M_%S`.sql

dump-data:
	@docker exec $(postgres_container_name) pg_dump $(postgres_db) -U $(postgres_user) --data-only > dump/dump_data_`date +%Y_%m_%d"__"%H_%M_%S`.sql

# make restore-data DUMP_FILE_NAME=dump_data_2020-02-17_23_41_00.sql
restore-data:
	@docker exec $(postgres_container_name) psql $(postgres_db) -U $(postgres_user) < dump/$(DUMP_FILE_NAME).sql

sql:
	@docker exec -it $(postgres_container_name) psql -U $(postgres_user) -W $(postgres_pw) $(postgres_db)

# make:migration
migration:
	@make exec cmd="php bin/console m:m"

# doctrine:migrations:migrate
migrate:
	@make exec cmd="php bin/console d:m:m"

phpstan:
	@make exec cmd="php vendor/bin/phpstan analyze -c phpstan.neon src --level 7"

test:
	@make fixtures
	@make test-only

fixtures:
	@make exec cmd="php bin/console doctrine:fixtures:load -n"

test-only:
	@make exec cmd="php bin/phpunit"

# open rabbitmq gui
rabbitmq:
	start http://localhost:15672

# show message queue
messages:
	@make exec cmd="php bin/console messenger:consume async -vv"

.PHONY: tests


#check: composer-validate cs-check phpstan psalm
#
#composer-validate: ensure composer-normalize-check
#	sh -c "${PHPQA_DOCKER_COMMAND} composer validate"

## Backup
#docker exec CONTAINER /usr/bin/mysqldump -u root --password=root DATABASE > backup.sql
#
## Restore
#cat backup.sql | docker exec -i CONTAINER /usr/bin/mysql -u root --password=root DATABASE
