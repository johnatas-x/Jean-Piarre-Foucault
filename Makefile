include ./.docker/docker.mk

.PHONY: test

DRUPAL_VER ?= 11
PHP_VER ?= 8.4

test:
	cd ./tests/$(DRUPAL_VER) && PHP_VER=$(PHP_VER) ./run.sh
