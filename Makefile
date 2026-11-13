# ==========================================
# HereWeGo — raccourcis Docker, qualité & tests
# (non interactif : utilisable par le hook pre-push)
# ==========================================

PROJECT_NAME = herewego
WWW = docker exec $(PROJECT_NAME)_www
# Couverture : pcov si l'image est rebuildée, sinon xdebug en mode coverage.
WWW_COV = docker exec -e XDEBUG_MODE=coverage $(PROJECT_NAME)_www

start:
	docker compose up -d

stop:
	docker compose stop

bash:
	docker exec -ti $(PROJECT_NAME)_www bash

cs:
	$(WWW) composer cs-check

cs-fix:
	$(WWW) composer cs-fix

phpstan:
	$(WWW) composer phpstan

lint:
	$(WWW) composer lint

audit:
	$(WWW) composer audit

# Base de test (suffixe _test ajouté automatiquement en env=test)
test-db:
	$(WWW) php bin/console doctrine:database:create --env=test --if-not-exists
	$(WWW) php bin/console doctrine:migrations:migrate -n --env=test

test:
	$(WWW) composer test

test-coverage:
	$(WWW_COV) composer test-coverage
	$(WWW) composer coverage-check

quality: cs phpstan lint

check: quality audit test-coverage

hooks-install:
	chmod +x .githooks/*
	git config core.hooksPath .githooks
	@echo "Hooks git installés (.githooks)."

.PHONY: start stop bash cs cs-fix phpstan lint audit test-db test test-coverage quality check hooks-install
