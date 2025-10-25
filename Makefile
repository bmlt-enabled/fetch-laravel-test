.PHONY: help install serve dev test lint api-docs clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies
	composer install
	npm install
	cp -n .env.example .env || true
	php artisan key:generate

serve: ## Start development server
	php artisan serve

dev: ## Start full development environment (server + queue + logs + vite)
	composer dev

test: ## Run tests
	php artisan test

lint: ## Run code formatter (Pint)
	./vendor/bin/pint

api-docs: ## Generate OpenAPI/Swagger documentation
	php artisan l5-swagger:generate

clean: ## Clear caches
	php artisan config:clear
	php artisan cache:clear
	php artisan route:clear
	php artisan view:clear
