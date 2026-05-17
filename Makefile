include .env
include .env.$(APP_ENV)

.PHONY: help build up down logs shell db-shell clean test

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# Docker commands
build: ## Build containers
	docker compose build

up: ## Start containers
	docker compose up -d

down: ## Stop containers
	docker compose down

restart: down up ## Restart containers

logs: ## Show logs
	docker compose logs -f

logs-api: ## Show API logs only
	docker compose logs -f php nginx

shell: ## Access PHP container shell
	docker compose exec php sh

db-shell: ## Access database shell
	docker compose exec database psql -U $(POSTGRES_USER) -d $(POSTGRES_DB)

# Composer commands
composer-install: ## Install composer dependencies
	docker compose exec -u appuser php composer install

composer-update: ## Update composer dependencies
	docker compose exec -u appuser php composer update

composer-require: ## Add package (usage: make composer-require PKG=vendor/package)
	docker compose exec -u appuser php composer require $(PKG)

# Framework-specific (Laravel)
ifeq ($(FRAMEWORK),laravel)
key-generate: ## Generate application key
	docker compose exec -u appuser php php artisan key:generate

migrate: ## Run database migrations
	docker compose exec -u appuser php php artisan migrate

migrate-fresh: ## Fresh migration with seeds
	docker compose exec -u appuser php php artisan migrate:fresh --seed

seed: ## Run database seeders
	docker compose exec -u appuser php php artisan db:seed

cache-clear: ## Clear all caches
	docker compose exec -u appuser php php artisan optimize:clear

cache-config: ## Cache configuration
	docker compose exec -u appuser php php artisan config:cache

cache-routes: ## Cache routes
	docker compose exec -u appuser php php artisan route:cache

test: ## Run tests
	docker compose exec -u appuser php php artisan test

test-feature: ## Run feature tests
	docker compose exec -u appuser php php artisan test --testsuite=Feature

test-unit: ## Run unit tests
	docker compose exec -u appuser php php artisan test --testsuite=Unit

tinker: ## Start Laravel Tinker
	docker compose exec -u appuser php php artisan tinker

queue-work: ## Start queue worker
	docker compose exec -u appuser php php artisan queue:work

queue-listen: ## Listen to queues
	docker compose exec -u appuser php php artisan queue:listen

horizon: ## Start Horizon
	docker compose exec -u appuser php php artisan horizon

schedule-work: ## Run schedule worker
	docker compose exec -u appuser php php artisan schedule:work

# Framework-specific (Symfony)
else ifeq ($(FRAMEWORK),symfony)
cache-clear: ## Clear Symfony cache
	docker compose exec -u appuser php php bin/console cache:clear

migrate: ## Run database migrations
	docker compose exec -u appuser php php bin/console doctrine:migrations:migrate --no-interaction

seed: ## Load fixtures
	docker compose exec -u appuser php php bin/console doctrine:fixtures:load --no-interaction

test: ## Run tests
	docker compose exec -u appuser php php bin/phpunit

debug-router: ## Debug routes
	docker compose exec -u appuser php php bin/console debug:router

make-entity: ## Create new entity (ENTITY=Name)
	docker compose exec -u appuser php php bin/console make:entity $(ENTITY)
endif

# Database commands
db-dump: ## Dump database
	docker compose exec database pg_dump -U $(POSTGRES_USER) $(POSTGRES_DB) > dump_$(shell date +%Y%m%d_%H%M%S).sql

db-restore: ## Restore database (FILE=dump.sql)
	docker compose exec -T database psql -U $(POSTGRES_USER) $(POSTGRES_DB) < $(FILE)

# Testing & Quality
phpstan: ## Run PHPStan
	docker compose exec -u appuser php vendor/bin/phpstan analyse

phpcs: ## Run PHP CodeSniffer
	docker compose exec -u appuser php vendor/bin/phpcs

phpcbf: ## Fix code style
	docker compose exec -u appuser php vendor/bin/phpcbf

# Production commands
prod-up: ## Start production environment
	APP_ENV=prod docker compose -f docker-compose.yaml -f docker-compose.prod.yaml up -d

prod-build: ## Build production environment
	APP_ENV=prod docker compose -f docker-compose.yaml -f docker-compose.prod.yaml build

prod-down: ## Stop production environment
	APP_ENV=prod docker compose -f docker-compose.yaml -f docker-compose.prod.yaml down

# Maintenance
clean: ## Remove everything
	docker compose down -v --rmi all --remove-orphans

dev-reset: ## Complete reset of development environment
	docker compose down -v
	docker compose build --no-cache
	docker compose up -d
	@sleep 5
	make composer-install
ifeq ($(FRAMEWORK),laravel)
	make key-generate
endif
	make migrate
	make seed

wait-for-db: ## Wait for database
	@echo "Waiting for database..."
	@docker compose exec php sh -c 'until pg_isready -h database -U $(POSTGRES_USER) -d $(POSTGRES_DB); do sleep 1; done'
	@echo "Database is ready!"

# API-specific commands
api-test: ## Quick API test
	curl -s http://localhost:$(NGINX_PORT)/ | jq .

api-health: ## Check API health
	curl -s -o /dev/null -w "%{http_code}" http://localhost:$(NGINX_PORT)/health

api-docs: ## Generate API documentation (if using Scribe/Swagger)
ifeq ($(FRAMEWORK),laravel)
	docker compose exec -u appuser php php artisan scribe:generate
endif

# Logs
logs-json: ## Pretty JSON logs
	docker compose logs -f nginx | jq .