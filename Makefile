php:
	docker compose exec -it php bash

reset:
	docker compose exec php php bin/console d:d:d --force
	docker compose exec php php bin/console d:d:c
	docker compose exec php php bin/console d:m:m -n
	docker compose exec php php bin/console app:init-blockchain

push:
	docker build --push -t jonbk/symfony-ledger:latest --target production .