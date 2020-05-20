include .env.local
export

.PHONY: cache-clear
cache-clear:
	docker-compose exec backend console cache:clear

.PHONY: doctrine-update
doctrine-update:
	docker-compose exec backend console doctrine:schema:update --force

.PHONY: dump-database
dump-database:
	docker-compose exec database mysqldump -q -u$(DATABASE_USER) -p$(DATABASE_PASSWORD) $(DATABASE_NAME) 2>/dev/null > $(to)
	sed -i '1d' $(to)

.PHONY: import-database-dump
import-database-dump:
	docker exec -i $(shell docker-compose ps -q database) mysql -u$(DATABASE_USER) -p$(DATABASE_PASSWORD) $(DATABASE_NAME) < $(from)