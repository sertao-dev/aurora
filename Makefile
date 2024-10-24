# Makefile para automatizar setup do projeto PHP com Docker

.PHONY: up install_dependencies generate_proxies migrate_database load_fixtures install_frontend compile_frontend generate_keys

# Inicia os serviços Docker em modo detached
up:
	docker compose up -d

# Para os serviços Docker
down:
	docker compose down

# Instala dependências dentro do contêiner PHP
install_dependencies:
	docker compose exec -T php bash -c "composer install"

# Gera os arquivos de Proxies do MongoDB
generate_proxies:
	docker compose exec -T php bash -c "php bin/console doctrine:mongodb:generate:proxies"

# Executa as migrations do banco de dados
migrate_database:
	docker compose exec -T php bash -c "php bin/console doctrine:migrations:migrate -n"

# Executa as fixtures de dados
load_fixtures:
	docker compose exec -T php bash -c "php bin/console doctrine:fixtures:load -n"

# Instala dependências do frontend
install_frontend:
	docker compose exec -T php bash -c "php bin/console importmap:install"

# Compila os arquivos do frontend
compile_frontend:
	docker compose exec -T php bash -c "php bin/console asset-map:compile"

# Executa as fixtures de dados e os testes de front-end
tests_front:
	docker compose exec -T php bash -c "php bin/console doctrine:fixtures:load -n" && docker compose up cypress

# Executa as fixtures de dados e os testes de back-end
tests_back:
	docker compose exec -T php bash -c "php bin/console doctrine:fixtures:load -n && bin/phpunit"

# Limpa o cache do projeto
reset:
	docker compose exec -T php bash -c "php bin/console cache:clear"

# Executa o php cs fixer
style:
	docker compose exec -T php bash -c "php bin/console app:code-style"

# Gera as chaves de autenticação JWT
generate_keys:
	docker compose exec -T php bash -c "php bin/console lexik:jwt:generate-keypair --overwrite"

# Comando para rodar todos os passos juntos
setup: up install_dependencies generate_proxies migrate_database load_fixtures install_frontend compile_frontend generate_keys
