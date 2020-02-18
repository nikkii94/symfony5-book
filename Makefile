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

sql:
	@docker exec -it $(postgres_container_name) psql -U $(postgres_user) -W $(postgres_pw) $(postgres_db)

# make:migration
create-migration:
	@make exec cmd="php bin/console m:m"

# doctrine:migrations:migrate
migrate:
	@make exec cmd="php bin/console d:m:m"

phpstan:
	@make exec cmd="php vendor/bin/phpstan analyze -c phpstan.neon src --level 7"

test:
	@make exec cmd="php bin/phpunit"


#check: composer-validate cs-check phpstan psalm
#
#composer-validate: ensure composer-normalize-check
#	sh -c "${PHPQA_DOCKER_COMMAND} composer validate"

## Backup
#docker exec CONTAINER /usr/bin/mysqldump -u root --password=root DATABASE > backup.sql
#
## Restore
#cat backup.sql | docker exec -i CONTAINER /usr/bin/mysql -u root --password=root DATABASE
