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

## rebuild	:	Down & rebuild stack.
.PHONY: rebuild
rebuild:
	@docker compose down && docker compose up --build -d

## drupset	:	Drupal settings.
.PHONY: drupset
drupset:
	@sudo chmod -R 775 "web/sites/default"
	@cp ".docker/files/settings.php.default" "web/sites/default/settings.php"
	@sudo chmod 664 "web/sites/default/settings.php"

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

## update	:	Update stack.
.PHONY: update
update:
	@make compinst
	@make fixperm
	@make drupset
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
	@docker compose up --build -d
	@docker exec -it "$(PHP_CONTAINER)" sudo chmod o+w /var/www/html
	@docker exec -it "$(PHP_CONTAINER)" git config --global --add safe.directory /var/www/html
	@make compinst
	@make fixperm
	@make drupset
	@make drush "site-install 'minimal' --config-dir=../config/sync --account-name='admin' --account-pass='admin' --yes"
	@make fulldeploy
	@make drush "fill-lotto-draws-data --all"
	@make drush cron
