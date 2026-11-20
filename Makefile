# Convenience wrapper around docker compose (OrbStack / Docker Desktop).
.PHONY: up down logs ps build admin db shell reset emails

up:            ## Start db, API and Vite dev server (http://localhost:5173)
	docker compose up -d --build
	@echo "Frontend (Vite, hot reload): http://localhost:5173"
	@echo "API:                         http://localhost:8080/api/"
	@echo "First run installs composer + npm deps - follow with: make logs"

down:          ## Stop containers (keeps the database volume)
	docker compose down

logs:
	docker compose logs -f --tail=100

ps:
	docker compose ps

build:         ## Production build of the frontend into public_html (served at http://localhost:8080)
	docker compose run --rm frontend sh -c "npm ci --no-audit --no-fund && npx vite build"

admin:         ## make admin EMAIL=you@example.com PASSWORD=secret123
	docker compose exec app php docker/bin/create-admin.php "$(EMAIL)" "$(PASSWORD)" "$(or $(FIRST),Admin)" "$(or $(LAST),User)"

db:            ## MySQL shell
	docker compose exec db mysql -umissions -pmissions idea-village-sandbox

shell:         ## Shell in the PHP container
	docker compose exec app bash

emails:        ## Show the latest queued e-mails (they are not actually sent locally)
	docker compose exec db mysql -umissions -pmissions idea-village-sandbox -e "SELECT email_queue_id, created_at, subject, recipients FROM kf_email_queue ORDER BY email_queue_id DESC LIMIT 20"

reset:         ## Destroy the database volume and start fresh
	docker compose down -v
	docker compose up -d --build
