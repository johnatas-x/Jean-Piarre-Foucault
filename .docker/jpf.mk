## quality	:	Executes grumphp to check quality.
.PHONY: quality
quality:
	docker exec $(shell docker ps --filter name='^/$(PROJECT_NAME)_php' --format "{{ .ID }}") vendor/bin/grumphp run
