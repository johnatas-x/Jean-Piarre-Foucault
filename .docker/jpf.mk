include .env

PHP_CONTAINER = "$(PROJECT_NAME)_php"

## zsh	:	Access `php` container via zsh.
.PHONY: zsh
zsh:
	@docker exec -it "$(PHP_CONTAINER)" zsh

## quality	:	Executes grumphp to check quality.
.PHONY: quality
quality:
	@docker exec "$(PHP_CONTAINER)" vendor/bin/grumphp run

## fixperm	:	Fix perm on Windows (fpm root).
.PHONY: fixperm
fixperm:
	@sudo chown -R $(WODBY_USER_ID):$(WODBY_GROUP_ID) .
	@find web -type d -exec chmod 755 '{}' \;
	@find web -type f -exec chmod 644 '{}' \;
	@find web/sites -type d -name files -exec chmod 775 '{}' \; || true
	@find web/sites/*/files -type d -exec chmod 775 '{}' \; || true
	@find web/sites/*/files -type f -exec chmod 664 '{}' \; || true
	@find web/sites/*/settings* -type f -exec chmod 444 '{}' \; || true

## rebuild	:	Down & rebuild stack.
.PHONY: rebuild
rebuild:
	@docker compose down && docker compose up --build -d

## compinst	:	Composer install.
.PHONY: compinst
compinst:
	@make composer "install --no-progress --prefer-dist --optimize-autoloader"

## fulldeploy	:	Drupal full deploy.
.PHONY: fulldeploy
fulldeploy:
	@make drush deploy
	@make drush locale-check
	@make drush locale-update
	@make drush cr

## pup	:	Project update.
.PHONY: pup
pup:
	@make compinst
	@sudo cp ".docker/files/settings.php.default" "web/sites/default/settings.php"
	@make fixperm
	@make fulldeploy

## init	:	Init project.
.PHONY: init
init:
	@if [ ! -f ".env" ]; then \
		cp ".env.example" ".env"; \
	fi
	@make prune
	@sudo rm -Rf vendor
	@sudo rm -Rf web/modules/contrib
	@sudo rm -Rf web/themes/contrib
	@sudo rm -Rf web/core
	@sudo rm -Rf web/sites
	@cp "python/jean-pyarre/templates/v5.html.example" "python/jean-pyarre/templates/v5.html"
	@cp "python/jean-pyarre/versions/v5.py.example" "python/jean-pyarre/versions/v5.py"
	@docker compose up --build -d
	@docker exec -it "$(PHP_CONTAINER)" sudo chmod o+w /var/www/html
	@docker exec -it "$(PHP_CONTAINER)" git config --global --add safe.directory /var/www/html
	@make compinst
	@make fixperm
	@mkdir -p "web/sites/default" && cp ".docker/files/settings.php.default" "web/sites/default/settings.php"
	@make fixperm
	@make drush "site-install 'minimal' --config-dir=../config/sync --account-name='admin' --account-pass='admin' --yes"
	@make fulldeploy
	@make drush "fill-lotto-draws-data --all"
	@make drush cron
	@make drush cr
