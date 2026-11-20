# Missions.dev: developer documentation

Missions.dev (also *Hugmyndaþorp* / "Idea Village", internal codename *Karolina*) is a co-creation
platform by [East of Moon](https://www.eastofmoon.com/). A community gets its own site where members
post ideas, challenges, projects and other **submissions**, discuss them, form teams and **groups**,
run **events**, and, in coworking deployments, book rooms and get invoiced. One codebase serves many
community sites (**platforms**, i.e. tenants) from one database.

These documents were written from a full read of the code at upstream commit `ae208d2`.
The upstream repo has no install guide and no tests, so treat this as the reference.

| Document | What's in it |
|---|---|
| [local-development.md](local-development.md) | Run the stack locally with OrbStack / Docker |
| [backend.md](backend.md) | PHP API: request lifecycle, the full route table (about 171 routes), domain model, database, auth, integrations |
| [frontend.md](frontend.md) | Vue 3 app: tooling, routing, stores, features, and every API call it makes |
| [findings.md](findings.md) | Tech debt, bugs, dependency status, and recommended next steps |

---

## Architecture at a glance

```
                       ┌───────────────────────── public_html/ (web root, built by `vite build`) ─┐
 Browser ── HTTPS ──►  │  index.html + assets/   ← Vue 3 SPA (frontend/src)                        │
                       │  api/index.php          ← require backend/bootstrap.php                   │
                       └──────────────┬────────────────────────────────────────────────────────────┘
                                      │  /api/*  (Authorization: Bearer <JWT>)
                                      ▼
                     backend/  Silex 1.3 routes (backend/silex/*Routes.php)
                               → Interactors / Editors → Repositories + domain entities (backend/src)
                               → Eloquent (illuminate/database) → MySQL (`kf_*` tables, partitioned by `platform`)
                                      │
            ┌──────────────┬──────────┼───────────┬──────────────┬─────────────┐
            ▼              ▼          ▼           ▼              ▼             ▼
         AWS S3      AWS SES/SQS   Pusher      Payday        Slack hooks   Twilio (deprecated)
        (images,    (e-mail; SQS → (realtime  (Icelandic    (new-submission
        attachments) workers/email) to SPA)    invoicing)     notices)
```

| Layer | Technology | Where |
|---|---|---|
| Frontend | Vue 3.4, Vue Router 4, Pinia, Vite 7, Bootstrap 4 CSS, TipTap, FullCalendar, Mapbox, Pusher JS | `frontend/` |
| API | PHP 7.4, Silex 1.3 (EOL), Eloquent 5.x standalone, Twig 1 (e-mail templates) | `backend/` |
| Database | MySQL 5.7+ (JSON columns), schema dump without seed data | `backend/src/Database/schema.sql` |
| Platform config | JSON/YAML files merged at runtime, plus per-tenant DB overrides | `config/` |
| UI strings | `en.json`, `is.json`, plus per-tenant DB overrides | `translations/` |
| E-mail worker | Separate small PHP app (Elastic Beanstalk worker; SQS → SES) | `workers/email/` |

## Repository layout

```
backend/
  bootstrap.php          env loading (config.env or `environments` DB), DB connections, then silex/App.php
  silex/                 App.php, bootstrapme.php (container, middleware, error handler), *Routes.php
  src/                   domain code, namespace Karolina\ (DDD-ish: Entity / Repository / Interactor / Editor)
    API/v1/              response mappers (domain → JSON)
    Database/            schema.sql, Eloquent table models (Table/*), PlatformScope (tenant filter)
  templates/default/twig e-mail templates
  platform_config/       legacy duplicate of config/sandbox.yaml (not read by the code)
config/                  ui_config*.json, sandbox.yaml, public.json, access_control_rules.json, JSON schemas
frontend/
  public/api/index.php   API entry point (copied to public_html/api/)
  src/                   main.js, routes.js, backend.js (axios), stores/, pages/, components/, mixins/
translations/            en.json, is.json
workers/email/           SQS → SES e-mail sender
principles.md            East of Moon's engineering principles (KISS, avoid lock-in, small increments…)
config.env.example       environment variable template (copy to config.env)
```

## Domain vocabulary

The code and the UI use different names for the same things:

| In the code / API | In the UI | Notes |
|---|---|---|
| Platform | site / community | Tenant, identified by `platform_key`. Rows carry a `platform` column; `PlatformScope` filters on it. |
| Experiment | Submission | Its `stage` is really its **type**: 0 challenge, 1 idea, 2 project, 3 insight, 4 topic, 5 issue, 6 guide, 7 policy, 8 organisation, 9 portfolio item, 10 question (`config/ui_config.json` → `stageHandles`). |
| Happening | Group | Registrations, applications, schedule, its own discussion and calendar. |
| Topic / Comment | Discussion / post | The topic id is the UUID of the owning submission or group. Posts are TipTap JSON. Comments can carry Six Thinking Hats tags and emoji reactions. |
| CalendarEvent | Event / session | Lives in named calendars: `activities`, `happening-{id}`, `submission-{id}`. |
| Space | Board / mesh / canvas | A hex-grid board where submissions are placed. |
| Network / Relationship | like / follow / help / related | A generic follower graph in `kf_relationships`. |
| Account / Subscription / Resource | Membership billing / room booking | Coworking features; invoices go out through Payday. |
| Language fields | — | All translatable text, in `kf_lang_fields`. |

## How a request flows

1. Apache serves `public_html/`. Non-file paths fall back to `index.html` (SPA). `/api/*` is rewritten to `api/index.php`.
2. `backend/bootstrap.php`:
   - loads env vars from `/config.env`, or by hostname from the `environments` DB when `load_variables_by_hostname=yes`;
   - validates the required ones;
   - opens the Eloquent connections;
   - includes `silex/App.php`.
3. `silex/bootstrapme.php`:
   - loads the tenant (`kf_platforms` row for `platform_key`) and its config. Config is every file in `/config/` merged, then `kf_platform_config` overrides;
   - registers services and middleware. JSON body parsing, and bearer token → `$app['currentUser']`;
   - registers the domain event subscribers: DB activity log, Pusher, e-mail, Slack;
   - mounts the seven route files.
4. A route closure checks access (`$adminOnly`, `$authRequired`, or inline), calls an Interactor or Editor, and returns `{"status":"success", ...}`.
5. Saving an aggregate re-serialises a denormalised `document` JSON column (submissions, profiles). It also publishes a domain event, whose side effects (Pusher, e-mail, Slack) run synchronously inside the request.

## Configuration layering

1. **Environment** (`config.env`, or the `environments` DB): secrets, DB credentials, `platform_key`, `protocol`, AWS/Pusher/Payday keys.
2. **Files** in `/config/`, merged by `Noodlehaus\Config`: branding, languages, the whole UI config (`platform.ui.*`: stages, menus, reactions, admin sections…), access-control rules.
3. **Database** (`kf_platform_config`), edited by admins in the UI: overrides `platform.*` keys per tenant. Changes made with `PUT /platform/config/` are validated against `config/config_item_schemas/*.json`.
4. **Translations**: `translations/*.json` as defaults, overridden per tenant by `kf_translations`.

The frontend gets the merged public view of all of this from `GET /api/platform/` at startup.
