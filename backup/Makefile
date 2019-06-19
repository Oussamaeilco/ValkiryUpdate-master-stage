.DEFAULT_GOAL=help
.PHONY=all

#-----------------------------------------------#
#                                               #
#    PHP configuration (edit only if needed)    #
#                                               #
#-----------------------------------------------#

# PHP Executable
PHP=php

# Host to serve
HOST=localhost

# Port to listen
PORT=3000

# Public directory (root dir for the server)
PUBLIC_DIR=public

# PHP Router file
ROUTER=$(PUBLIC_DIR)/index.php

#-----------------------------------------------#
#                                               #
#                   Recipes                     #
#                                               #
#-----------------------------------------------#

help: ## Show this help.
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "make \033[36m%-30s\033[0m %s\n", $$1, $$2}'

composer.lock: composer.json ## Build composer dependencies list
	composer update

vendor: composer.lock ## Build composer vendor
	composer install

install: vendor ## Install project dependencies

fix: install ## Fix PHP coding standards
	php vendor/bin/php-cs-fixer fix ./

serve: install ## Run the PHP Server
	$(PHP) -S $(HOST):$(PORT) -t $(PUBLIC_DIR) $(ROUTER)

run: serve ## Run the server

run-clean: clean run ## Cleaning before runnig (force build)

clean: ## Clean cache and temp files
	rm -rf tmp
	rm -f .php_cs.cache

deep-clean: clean ## Clean cache, temp files & remove vendore direcyory
	rm -rf vendor

update: deep-clean install ## Remove vendor directory and run make install