# Running Missions.dev locally (OrbStack / Docker)

This repository ships no install guide of its own (see the root `README.md`). This setup
runs the whole stack in containers so you don't need PHP 7.4, MySQL or Node on your Mac.
It works with [OrbStack](https://orbstack.dev/) and with any other Docker engine.

## What runs

| Service    | Image                         | Port (host)  | Purpose |
|------------|-------------------------------|--------------|---------|
| `db`       | `mysql:8.0`                   | `3307`       | MySQL with `backend/src/Database/schema.sql`, an `environments` DB and a seed platform |
| `app`      | `docker/php/Dockerfile` (PHP 7.4 + Apache) | `8080` | PHP API at `/api`, and the built frontend (`public_html`) at `/` |
| `frontend` | `node:22-bookworm-slim`       | `5173`       | Vite dev server with hot reload; proxies `/api` to `app` |

```
browser ──► :5173 Vite (frontend/src, HMR)
              └─ /api/* ──proxy──► app:80  Apache (docroot public_html/)
                                     └─ public_html/api/index.php
                                          └─ require ../../backend/bootstrap.php (Silex, live from source)
                                                                     └─► db:3306 MySQL
```

OrbStack also gives each container a hostname, e.g. `http://app.missions-dev.orb.local/api/`.

## First start

```bash
make up
```

```bash
make logs
```

On the first run:

1. The `app` container creates `/config.env` from `docker/config.env.docker`, with a random
   `platform_secret_key`. `config.env` is gitignored.
2. It installs the backend's Composer dependencies into `backend/vendor`, using
   `--no-security-blocking`; see "Dependencies and security advisories" below. There is no
   `composer.lock` in the repo (`*.lock` is gitignored), so versions are resolved against the
   PHP 7.4 platform pin in `backend/composer.json`. This takes a few minutes on the first run.
   It also copies `frontend/public/api/` to `public_html/api/` if that's missing (as `vite build` would).
3. MySQL runs `schema.sql`, then `docker/mysql/20-environments.sql` and `docker/mysql/30-seed.sql`.
4. The `frontend` container runs `npm ci` and starts Vite.

Then open <http://localhost:5173>.

### Create an admin user

The schema has no users, and the seeded platform is `members_only` with registration closed,
so you need an admin to get past the login screen. Create one (`group_id = 1`), the same way
`PlatformNewAction` does:

```bash
make admin EMAIL=you@example.com PASSWORD=change-me-123
```

Then log in through the UI.

## Everyday commands

| Command        | What it does |
|----------------|--------------|
| `make up` / `make down` | Start / stop (the database volume is kept) |
| `make logs`    | Follow the logs of all containers |
| `make db`      | MySQL shell (`missions` / `missions`, DB `idea-village-sandbox`) |
| `make shell`   | Bash in the PHP container |
| `make emails`  | Show the latest rows in `kf_email_queue` (mail is queued, not sent, locally) |
| `make build`   | Production build of the frontend into `public_html/`, served at <http://localhost:8080> |
| `make reset`   | Delete the database volume and re-seed from scratch |

You can also reach MySQL from the host at `127.0.0.1:3307` (TablePlus, DBeaver, etc.).

## How it maps onto production

- Production document root is `public_html/`, which `vite build` produces (`frontend/vite.config.js`
  → `outDir: '../public_html'`). Vite copies `frontend/public/` into it, including
  `api/index.php` and the `.htaccess` files.
- `public_html/api/index.php` just does `require __DIR__.'/../../backend/bootstrap.php'`.
- In production mode, `frontend/src/main.js` calls the API at `/api`. In development mode it uses
  `VITE_API_URL` (default `http://localhost:8888`); compose sets it to `/api` so the Vite proxy
  keeps everything on one origin. The backend sends no CORS headers, so a cross-origin
  setup would not work.
- In the container, the `.htaccess` rewrite rules live in `docker/php/vhost.conf` instead.
  `public_html/api/index.php` just requires `backend/bootstrap.php`, so backend edits apply on the next request.

## Files added for this setup

| File | Purpose |
|------|---------|
| `docker-compose.yml` | The three services and volumes |
| `docker/php/Dockerfile` | PHP 7.4 + Apache image. Points apt at `archive.debian.org`, since Debian 11 (the base of all `php:7.4` images) has been archived |
| `docker/php/vhost.conf` | Apache vhost: `public_html` docroot, SPA fallback, `/api` rewrite to `index.php`, Authorization header passthrough |
| `docker/php/entrypoint.sh` | Creates `config.env`, installs Composer deps, places `public_html/api/`, makes `backend/cache` and `backend/temp` writable |
| `docker/config.env.docker` | Template for local environment variables |
| `docker/mysql/20-environments.sql` | Creates the `environments` schema that `bootstrap.php` always connects to |
| `docker/mysql/30-seed.sql` | Inserts the `sandbox` row into `kf_platforms`. Without it, `bootstrapme.php` fails on `getPlatformByKey()` |
| `docker/bin/create-admin.php` | CLI to create an admin user |
| `Makefile` | Shortcuts |
| `frontend/vite.config.js` | Adds an `/api` proxy, only when `API_PROXY_TARGET` is set (no effect otherwise) |
| `backend/composer.json` | `name` changed from `"Missions.dev backend"` to `eastofmoon/missions-backend`. Composer 2 rejects the old value as invalid |
| `backend/src/PushToFront.php`, `backend/src/Event/PushToFrontEndSubscription.php` | Skip the Pusher call when `pusher_api_key` is empty. Before, every save that fired a realtime event returned a 500 *after* the data had been written |

## What doesn't work locally (by design of the code)

- **Image and attachment uploads** go straight to AWS S3 (`$app['s3']` in `bootstrapme.php`,
  `src/Image/ImageFileRepository.php`). They need real AWS credentials and a bucket.
- **E-mail** is written to `kf_email_queue`, then pushed to AWS SQS (`send_email_queue_url`).
  The `workers/email` app (deployed as an Elastic Beanstalk worker) sends it through SES.
  Locally the SQS push fails and is logged; read mail with `make emails`.
- **Realtime** (Pusher) needs `pusher_*` keys; without them pushes are skipped, so pages don't live-update. **Invoicing** (Payday) needs `payday_*` keys.
- **Mapbox** components need a Mapbox token.

## Dependencies and security advisories

Current Composer (2.9+) refuses by default to install package versions that have published
security advisories. The backend pins old major versions of many libraries, and almost all of
them now have advisories: `twig/twig ~1`, `symfony/http-foundation ^3`, `symfony/yaml ^3`,
`symfony/twig-bridge ^3`, `league/commonmark ^0.11`, `paragonie/random_compat ^1`,
`nategood/httpful`, `ueberdosis/tiptap-php`, and the older `aws/aws-sdk-php` releases that still
support PHP 7.4. A plain `composer install` therefore fails with
*"Your requirements could not be resolved"*.

The Docker entrypoint installs with `composer update --no-security-blocking`. Composer then
reports **41 advisories affecting 11 packages** (`docker compose exec app composer audit -d backend`
lists them). That's acceptable for a local sandbox. **Never deploy a build made this way.**
The real fix is upgrading the dependencies. Silex 1.x is end-of-life and ties the code to
Symfony 3 and Twig 1, so in practice this means porting `backend/silex/*` to a maintained
micro-framework (e.g. Slim 4) on PHP 8.x. See `docs/findings.md`.

## Verified

On OrbStack (Apple Silicon), from a clean `make reset`:
- MySQL init and seed; the API boots (`GET /api/platform/`).
- `make admin`, then logging in through the UI at http://localhost:5173 lands on the feed.
- Through the Vite proxy: create a submission → set its title → publish → comment → read comments → delete,
  plus the profile, member list, groups, tags and calendar endpoints. No PHP errors apart from the expected SQS failure for e-mail.
