include .env

## quality	:	Executes grumphp to check quality.
.PHONY: quality
quality:
	docker exec $(shell docker ps --filter name='^/$(PROJECT_NAME)_php' --format "{{ .ID }}") vendor/bin/grumphp run

## fixperm	:	Fix perm on Windows (fpm root).
.PHONY: fixperm
fixperm:
	sudo chown -R $(WODBY_USER_ID):$(WODBY_GROUP_ID) .
