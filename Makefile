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

export-ssl:
	rm -rf certs
	@docker cp $(nginx_container_name):/etc/ssl ./certs

inspect:
	@docker inspect -f '{{ json .Mounts }}' $(postgres_container_name) | python -m json.tool

#https://devopsheaven.com/postgresql/pg_dump/databases/docker/backup/2017/09/10/backup-postgresql-database-using-docker.html
#https://gist.github.com/gilyes/525cc0f471aafae18c3857c27519fc4b
dump-sql:
	@docker exec $(postgres_container_name) pg_dump $(postgres_db) -U $(postgres_user) > dump/dump_`date +%Y-%m-%d"_"%H_%M_%S`.sql

#check: composer-validate cs-check phpstan psalm
#
#composer-validate: ensure composer-normalize-check
#	sh -c "${PHPQA_DOCKER_COMMAND} composer validate"

## Backup
#docker exec CONTAINER /usr/bin/mysqldump -u root --password=root DATABASE > backup.sql
#
## Restore
#cat backup.sql | docker exec -i CONTAINER /usr/bin/mysql -u root --password=root DATABASE
