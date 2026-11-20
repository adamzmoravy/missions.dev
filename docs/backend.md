# Backend (PHP API): architecture and reference

Paths are relative to the repo root; `file:line` refers to commit `ae208d2`. There is no `composer.lock` in the repo, so library versions are the ranges in `backend/composer.json`.

---

## 0. Orientation

| Item | Location / fact |
|---|---|
| Real bootstrap file | `backend/bootstrap.php`. It is not a web entry point by itself. |
| Web entry point | `frontend/public/api/index.php` (one line: `require_once __DIR__.'/../../backend/bootstrap.php'`). `vite build` copies it to `public_html/api/index.php`; `public_html/` is the web root and is gitignored. `frontend/public/api/.htaccess` rewrites every request to `index.php`. |
| API base URL | Production: `/api` (`frontend/src/main.js:58`). Development: `VITE_API_URL`, default `http://localhost:8888` (`main.js:53`). Note that `silex/Routes.php:528` reads `'../../config/...'` relative to the working directory, i.e. it assumes the script runs from `public_html/api/`. |
| PHP namespace | `Karolina\` maps to `backend/src/` (an old codename). `Communicator\` maps to `src/Communicator/`. `Controllers\` maps to `controllers/`, which doesn't exist (`composer.json` autoload). |
| Framework | Silex 1.3 (EOL), Pimple 1. Eloquent from `illuminate/database ^5.3` is used standalone as the ORM. Twig 1.x. |
| Architecture claimed in README | DDD-ish: Route → Interactor/Editor → Repository + Domain entity. In practice much logic sits in the route closures. |

---

## 1. Request lifecycle

### 1.1 Boot sequence
1. **`backend/bootstrap.php`**
   - `:4` loads `vendor/autoload.php`. This is a relative include; PHP resolves it via the calling script's directory.
   - `:6-8` creates a Symfony Stopwatch and a `$eventTimes` global, neither of which is used.
   - **Environment loading (`:12-25`):**
     - If the server env var `load_variables_by_hostname=yes`:
       - `Dotenv` is pointed at `backend/` (default file `.env`) and loaded with `safeLoad`.
       - `Environment::loadEnvVariablesByHostname($_SERVER['HTTP_HOST'])` (`src/Environment.php:16-55`) opens a raw PDO connection to a separate MySQL DB called **`environments`**.
       - It selects from `variables LEFT JOIN hosts ON collection` twice: once for hostname `default`, once for the request's Host. Results are merged, then `putenv()`'d (`:80-92`).
       - This is how **one deployment serves many tenants by hostname**.
     - Otherwise it loads **`config.env` from the repo root** (`__DIR__.'/../'`); see `config.env.example`.
   - `:30-38` sets Dotenv requirements: `platform_config`, `environment ∈ {production, development}`, `protocol ∈ {http, https}`, `platform_secret_key`, `database_{name,hostname,user,pass}`, `allow_api_access ∈ {yes, no}`.
     - `platform_config` and `allow_api_access` are required but **never used** anywhere else.
   - `:41` calls `date_default_timezone_set(getenv('timezone'))`.
   - `:43-61` redirects to HTTPS when `protocol=https`. It uses `X-Forwarded-Proto` and builds the target from `SERVER_NAME`.
   - `:63-68` defines constants. They are unused, and `DATABASE_PASSWORD` reads the nonexistent `database_password`.
   - `:75-78` uses `Database\DatabaseConnector` (Eloquent Capsule, `src/Database/DatabaseConnector.php`). It registers connection `default` (`database_name`) and connection `environments`, then calls `setAsGlobal()` and `bootEloquent()`.
   - `:80` includes `silex/App.php`.
2. **`silex/App.php`**: `new Silex\Application()`, `$app['ci'] = false` (a CodeIgniter leftover), `require 'bootstrapme.php'`, `$app->run()`.
3. **`silex/bootstrapme.php`** sets up the container, middleware and routes.

### 1.2 Platform and configuration layering
- `bootstrapme.php:38-44` calls `PlatformRepository::getPlatformByKey(getenv('platform_key'))` (`src/PlatformRepository.php:43-48`).
  - This uses `firstOrFail` on `kf_platforms`. The `schema.sql` has **no seed rows**, so a fresh install must insert a `kf_platforms` row (e.g. `sandbox`) or bootstrap throws before the error handler exists.
  - It then loads `kf_settings` into the platform's settings group.
- **Config is layered** in `src/Platform.php`:
  1. **Files.** `Platform::setupConfig()` (`:164-173`) runs `Noodlehaus\Config::load()` on **every file in the repo-root `/config/` directory** (`getConfigPath()` returns `'/../../config/'`, `:70-74`).
     - That covers `config/sandbox.yaml`, `public.json`, `ui_config*.json`, `access_control_rules.json`, `ui_quill_editor.json` and `config_fields_users.json`, merged into one tree.
     - **`backend/platform_config/sandbox.yaml` is a duplicate and is not read by the code.** The README's claim that the file is "selected based on `platform_config`" is outdated.
  2. **Per-tenant DB overrides.** Rows in `kf_platform_config` (`name`, JSON `value`, `platform`) are loaded in `PlatformRepository::modelToPlatform` (`:64-76`). They are applied with `Platform::setDBConf()` (`:464-469`), which also sets `platform.<name>` in the config tree, so DB values override file values under the `platform.*` namespace.
     - Writes go through `setConf()` / `setConfPublic()` (`:413-439`).
  3. **Env vars** are read directly with `getenv()` throughout (≈54 calls for `platform_key` alone).
- Config keys that matter:
  - `platform.ui.stages`, `platform.ui.stageHandles` (stage → type name), `platform.access_control_rules`.
  - `platform.groups.default` (auto-join group), `users_to_events_subscriptions`, `external_to_events_subscriptions` (Slack).
  - `aws_region`, `email_noreply`, `email_info`, `currency`, `is_sandbox`, `experiments`.

### 1.3 Service container (Pimple 1)
Defined in `bootstrapme.php:174-267`. In Pimple 1, closures are **factories, not shared**, so every `$app['x']` access builds a new instance.

| Service | What it builds |
|---|---|
| `s3` | `Aws\Sdk` S3 client in region `aws_region`, using default credential chain env `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` |
| `userRepository` | Eager instance |
| `userInteractor`, `notificationService` | `Karolina\Notification`, with Twig and userInteractor injected |
| `experimentInteractor`, `happeningInteractor`, `experimentEditor`, `userEditor`, `authInteractor` | Domain services |
| `currentUser` | Set by middleware |
| `platform` | The loaded `Platform` |
| `twig`, `translator` | Providers |

### 1.4 Middleware
- **Before #1** (`:51-60`):
  - Forces the header `Content-Type: application/json` on everything, including routes that return HTML.
  - If the request body is `application/json`, decodes it into `$request->request`.
- **Before #2, authentication** (`:68-99`):
  - If an `Authorization` header is present, calls `UserRepository::getWithToken()`.
  - Any exception returns `401 {"status":"invalid_token"}`. The frontend logs out on that status (`frontend/src/backend.js:26-31`).
  - Without a header, `currentUser` is `false`.
- **Before #3, event subscriptions** (`:318-331`) registers per request on the singleton `Event\Publisher`:
  - `SaveEventSubscription` writes `kf_events`.
  - `PushToFrontEndSubscription` sends to Pusher.
  - `NotifyUsersSubscription` sends email.
  - `ExternalServiceSubscription` posts to Slack.
- **Route guards** (closures attached with `->before()`):
  - `$adminOnly` (`:285-295`) returns 401 `admin_only` unless `currentUser->isAdmin()`.
  - `$authRequired` (`:298-307`) returns 401 `authorization_required` unless logged in.
  - Many routes use neither and check inside the handler, or not at all (see the route table).
- An empty after-middleware is registered (`:275-277`).
- **Debug**: `$app['debug'] = (environment == development)` (`:107-115`).
- **Error handler** (`:337-381`): JSON `{status, message}`.
  - For `KarolinaException` it uses `getStatusKey()` and `getCode()`.
  - For `Karolina\Exception` (`src/Exception.php`: message, statusKey, httpCode defaulting to 500) it uses those plus `exception_type`.
  - Anything else gives a generic message unless debug is on.
  - It only catches `\Exception`, so PHP 7 `Error`s become raw fatals. That includes TypeErrors from passing `false` as `User`, which is how many routes "deny" anonymous users by accident.
- **Routes are mounted** at `/` from seven files (`:392-398`).
  - `/sandbox/*` is mounted from `silex/sandbox/SandboxRoutes.php` (gitignored, absent) when `is_sandbox=yes` or debug is on (`:401-410`).
- **CORS**: none. There are no `Access-Control-*` headers anywhere, so same origin (`/api`) or web-server CORS is assumed.
- **Sessions / cookies**: none. Auth is stateless via `Authorization: Bearer <JWT>`. `src/Http/Cookie.php` and `DiscourseSSO.php` exist but are unused.
- Response convention: `{"status":"success", ...}`. Errors return `{"status":"<key>","message":...}` with inconsistent HTTP codes; access denials come back as 500 and sometimes 200, e.g. `ExperimentsRoutes.php:123,864,876`.

---

## 2. API surface

Guard legend:
- **pub**: no auth.
- **auth**: `$authRequired`.
- **admin**: `$adminOnly`.
- **login(h)**: login checked inside the handler.
- **implicit**: no check, but an anonymous call crashes (TypeError or null dereference).
- **team/admin**: domain check in `ExperimentEditor`/`Experiment::canEdit*` (`src/Experiment/Experiment.php:854-889`).
- **env-mo**: blocked for anonymous users only if the env var `access=members_only` (see §5).

Routes total about 171 active.

### `silex/Routes.php` (platform, tags, calendar, stats, misc)
| Method | Path | Line | Guard | Purpose |
|---|---|---|---|---|
| GET | `/page/metadata/?url=` | 14 | pub (global limit 120/min) | Fetch and parse OG/meta for a URL (link previews); DB cache |
| GET | `/platform/stats/{day}/` | 50 | admin | Daily "ping" active-user stats |
| GET | `/platform/ping/` | 73 | pub | Record today's ping for the current user |
| POST | `/tags/{langCode}` | 86 | admin | Create tag (label, types) |
| POST | `/custom_tags/{langCode}` | 105 | auth | Any user creates a tag |
| PUT | `/tags/{tagId}/{langCode}` | 124 | admin | Edit tag label/type/types |
| DELETE | `/tags/{tagId}/` | 152 | admin | Soft-disable tag |
| GET | `/tags/{langCode}` | 167 | pub | All tags |
| POST | `/send_email_in_queue/` | 193 | pub | AWS SNS HTTP endpoint: confirm subscription, or send queued email by uuid |
| (GET) | `/send_email/{uuid}/` | 229 | — | Commented out |
| POST | `/survey_results/` | 247 | pub | Store survey answers |
| GET | `/survey_results/{surveyName}/` | 268 | auth | Read all results of a survey |
| DELETE | `/calendar/events/{eventId}/` | 284 | auth + owner/admin (silent no-op otherwise) | Deactivate event |
| POST | `/calendar/activites/events/` | 302 | auth | Create event in global "activities" calendar (typo in path) |
| GET | `/calendar/events/participate/` | 335 | pub | Upcoming "activities" events |
| POST | `/calendar/events/attendance/{eventId}/me/` | 352 | auth | Attend |
| POST | `/calendar/events/attendance/{eventId}/me/{plus}/` | 366 | auth | Set +N guests |
| DELETE | `/calendar/events/attendance/{eventId}/me/` | 381 | auth | Unattend |
| GET | `/calendar/make_events/` | 397 | pub | **Test route** that creates a hard-coded 2022 event |
| GET | `/stats/{scope}/` | 418 | pub | Experiment/comment/user counts; `global` removes the tenant scope |
| PATCH | `/platform/` | 436 | admin | Title, label, mission, languages, header elements, logo, site access, secret access code toggle |
| GET | `/platforms/` | 459 | pub | Site labels of **all** tenants |
| POST | `/platform/` | 476 | **pub** | Provision a new tenant + admin user (`src/PlatformNewAction.php`) |
| GET | `/platform/` | 492 | pub | Frontend bootstrap: public config, `ui`, translations |
| GET | `/platform/config/` | 505 (duplicate at 561) | admin | Raw merged config |
| PUT | `/platform/config/` | 517 | admin | Set one config key, validated against `config/config_item_schemas/{key}.json` (opis) |
| PUT | `/platform/translations/{lang}/` | 572 | admin | Upsert/delete a UI translation (`kf_translations`) |
| GET | `/platform/admin/` | 583 | admin | Public config + secret access code + subscription offerings |
| GET | `/sdg/tags/{langCode}` | 593 | pub | Tags of type `sdg` |
| GET | `/tags/{type}/{langCode}` | 617 | pub | Tags by type |
| POST | `/events/last_activity_fetch/` | 641 | optional | Store `last_activity_fetch` in user settings |
| GET | `/events/` | 658 | pub | Activity feed from `kf_events` (`EventResponse`) |
| GET | `/events/since/{time}/` | 668 | pub (`my_last_fetched` + anonymous → fatal) | Count new events in the last 7 days |
| GET | `/dashboard/` | 694 | pub | `Stats\Dashboard` counts |
| POST | `/files/images/` | 702 | auth | Upload image to S3; returns filename + URL |
| POST | `/contact/` | 727 | **pub** | Contact form: emails `email_info` |

### `silex/UsersRoutes.php`
| Method | Path | Line | Guard | Purpose |
|---|---|---|---|---|
| GET | `/users/settings/{token}/` | 15 | **pub** | Debug leftover: writes `go_fetcher` on **user #1**, echoes timing |
| GET | `/users/identifier/{email}/` | 32 | pub | Does this email exist? |
| GET | `/users/identifier/?email=` | 53 | pub | Same |
| GET | `/users/tags/{langCode}` | 76 | pub | All tags |
| GET | `/users/skills/` | 101 | pub | Skills list |
| POST | `/users/tokens/` | 116 | pub | **Login**: email + password → JWT valid **6 months** |
| POST | `/users/` | 139 | pub if registration is enabled, or with a valid `access_code`, or admin | **Register**: returns 12 h JWT; auto-joins `platform.groups.default` |
| DELETE | `/users/{userId}/` | 203 | admin(h) | Disable user |
| POST | `/users/me/tags/` | 226 | implicit | Replace profile tags |
| POST | `/users/me/tags/{tagId}/` | 243 | implicit | Add typed tag (`tag_type`) |
| DELETE | `/users/me/tags/{tagId}/{tagType}/` | 261 | implicit | Remove typed tag |
| POST | `/users/me/skills/` | 277 | implicit | Replace skills |
| POST | `/users/me/links/` | 293 | auth | Replace links |
| GET | `/users/preview/` | 309 | pub, env-mo | Member directory. Filters: `tag`, `ids`, `limit`, `offset`, `profile_image_has`, `unsubscribed_to_newsletter`. Admins also get email/meta. |
| GET | `/all_platforms/users/preview/` | 343 | pub | Member directory across 7 hard-coded platform keys |
| GET | `/users/rewritedocuments/` | 382 | **pub** | Maintenance: rebuild every `kf_profiles.document` |
| POST | `/users/{userId}/contact/` | 395 | login(h) + per-user rate limits | Email another member |
| GET | `/users/{userId}/profile` | 444 | pub, env-mo | Profile + submissions + comment stats (also returns a debug `timings` field) |
| PATCH | `/users/me/profile` | 501 | auth | Update profile; inline base64 `<img>` uploaded to S3 |
| PATCH | `/users/me/profile/geographic_location` | 536 | auth | |
| POST | `/users/me/profile/contact_information` | 559 | auth | |
| GET | `/users/{userId}/profile/contact_information` | 582 | auth (any user) | Another user's contact info |
| PATCH | `/users/me/profile/image_collection` | 602 | auth | Set profile image filename |
| POST | `/users/me/password` | 626 | auth | Change password. |
| POST | `/users/password_resets/` | 645 | pub | Email a reset JWT (1 h) |
| POST | `/users/password_changes/` | 660 | pub + token | Reset with email + token + new password |
| GET | `/users/{userId}/experiments/preview/{langCode}` | 678 | pub (`me` needs login, but `:688` calls the nonexistent `getUserId()` → fatal) | Submissions where the user is on the team |

### `silex/ExperimentsRoutes.php` (submissions)
| Method | Path | Line | Guard | Purpose |
|---|---|---|---|---|
| POST | `/experiments/{id}/share/` | 17 | auth (global limit 50/10 min) | Email a submission to user ids with a message |
| GET | `/experiments/spaces/` | 40 | pub | List spaces |
| GET | `/experiments/unsubscribe/{token}` | 76 | pub + signed token | Newsletter unsubscribe (returns HTML) |
| POST | `/experiments/{id}/photo_stream/` | 98 | auth + team/admin | Add photo record |
| POST | `/experiments/{id}/calendar/events/` | 114 | auth (any user) | Create event in calendar `submission-{id}` |
| GET | `/experiments/{id}/photo_stream/{limit}/` | 158 | pub (view-restriction check only) | Photo stream |
| GET | `/experiments/emailout/{action}/{newer_than}/{min_relevance}/` | 192 | **pub** | `send` mails the newsletter to all users (limited to once per 6 days); any other action renders an HTML preview |
| POST | `/experiments/from_comment/` | 215 | login(h); comment owner or admin | Turn a comment + replies into a new published submission |
| POST | `/experiments/spaces/` | 241 | admin | Create space |
| DELETE | `/experiments/spaces/{spaceId}/` | 256 | admin | Soft-delete space |
| PUT | `/experiments/spaces/` | 268 | admin | **Broken** (see §8) |
| GET | `/experiments/{id}/video_chat/` | 288 | login(h) | Twilio Video access token |
| GET | `/experiments/admin/{langCode}` | 329 | admin | Previews with interaction counts |
| GET | `/experiments/preview/{langCode}` | 363 | pub, env-mo | List submissions (filters in §4.3); stage ACL and `private` filtering applied in PHP |
| POST | `/experiments/tags/{langCode}` | 425 | admin | Create experiment tag |
| PUT | `/experiments/tags/{tagId}/{langCode}` | 442 | admin | |
| DELETE | `/experiments/tags/{tagId}/` | 456 | admin | |
| GET | `/experiments/tags/{langCode}` | 471 | pub | Tags of type `experiment` |
| POST | `/experiments/{id}/publish/` | 494 | team/admin | Sets `show_in.index` and `browse_exp`; emits `new_experiment` |
| POST | `/experiments/{id}/attachments/file/` | 508 | auth + team/admin | Upload PDF (≤60 MB) to S3 `attachments-bucket` |
| DELETE | `/experiments/{id}/attachments/{attachmentId}/` | 524 | auth + team/admin | |
| POST | `/experiments/{id}/attachments/file_request/` | 540 | auth (any user) | Presigned S3 GET URL, 20 min |
| POST | `/experiments/{id}/team/` | 557 | team/admin **or submission is "joinable" (stage 8)** | Add member by email |
| POST | `/experiments/{id}/team/me/` | 577 | same | Join |
| POST | `/experiments/{id}/team/{userId}/` | 594 | same | Add member by id |
| DELETE | `/experiments/{id}/team/{userId}/` | 614 | team/admin | Remove member |
| DELETE | `/experiments/{id}/publish/` | 634 | team/admin | Unpublish |
| DELETE | `/experiments/{id}/` | 649 | team/admin | Soft-disable |
| POST / DELETE | `/experiments/{id}/like/` | 664 / 821 | login(h) | Like / unlike |
| POST / DELETE | `/experiments/{id}/help/` | 688 / 711 | login(h) | "Want to help" relationship |
| POST / DELETE | `/experiments/{id}/updates/` | 734 / 757 | login(h) | Follow updates |
| POST | `/experiments/{id}/related_experiments/` | 778 | auth (no team check) | Relate submissions |
| DELETE | `/experiments/{id}/related_experiments/{relId}/` | 803 | auth (no team check) | |
| GET | `/experiments/{id}/relevance/` | 842 | pub | Raw relevance score (not JSON) |
| GET | `/experiments/{id}/{langCode}` | 854 | pub; view restrictions; stage ACL; `private` → team/admin | Full submission + `acl` + `my_relationships` |
| PATCH | `/experiments/{id}/funding/` | 905 | admin (`canEditFunding`) | Funding goal and sources |
| POST | `/experiments/{id}/tags/` | 919 | auth + team/admin | Replace tags |
| POST | `/experiments/{id}/tag/` | 934 | auth + team/admin | Add tag |
| DELETE | `/experiments/{id}/tag/{tagId}/` | 948 | auth + team/admin | |
| POST | `/experiments/{id}/stagemoves/` | 961 | team/admin | `direction` = next / previous |
| POST | `/experiments/{id}/links/` | 990 | team/admin | Replace links |
| GET | `/experiments/rewritedocuments/` | 1005 | **pub** | Maintenance: rebuild all `kf_experiments.document` |
| GET | `/experiments/resaveallexperiments/` | 1017 | **pub** | Maintenance: re-save every submission (fires events) |
| POST | `/experiments/` | 1034 | auth | Create (`stage`, `visibility`); autopublishes if env `experiments_publish=on_creation` |
| PATCH | `/experiments/{id}/settings/` | 1073 | auth + team/admin (`show_in` admin only) | status, restrictions, geo, stage, disabled, show_in, show_in_placement |
| PATCH | `/experiments/{id}/image_collection/` | 1097 | auth + team/admin | |
| PATCH | `/experiments/{id}/custom_language/{langCode}/` | 1118 | auth + team/admin | Custom rich fields; base64 images uploaded to S3 |
| PATCH | `/experiments/{id}/language/{langCode}/` | 1153 | auth + team/admin | title / short_description / owner_name |
| PATCH | `/experiments/{id}/language/` | 1181 | auth + team/admin | Several languages at once |

### `silex/TopicsRoutes.php` (comments)
| Method | Path | Line | Guard | Purpose |
|---|---|---|---|---|
| POST / DELETE | `/comments/{commentId}/upvote/` | 13 / 62 | implicit | Upvote |
| POST / DELETE | `/comments/{commentId}/reaction/` | 29 / 45 | auth | Emoji reaction |
| DELETE | `/comments/{commentId}/` | 79 | owner/admin (anonymous → fatal) | Soft-delete |
| POST | `/experiments/{id}/comment/{commentId}/reply/` | 101 | login(h) | Reply; emails thread participants (crashes at `:133` and `:145`, see §8) |
| POST | `/experiments/{id}/topic/` | 165 | login(h) | New comment; emails team, likers, helpers, followers, commenters and mentions |
| GET | `/experiments/{id}/topic/` | 242 | pub | Comments for a submission |

### `silex/HappeningsRoutes.php` (groups, events, video)
| Method | Path | Line | Guard | Purpose |
|---|---|---|---|---|
| GET | `/calendar/events/` | 15 | implicit | Upcoming events of groups the user belongs to |
| GET | `/happenings/{id}/calendar/events/` | 38 | implicit + member/admin | Group events |
| GET | `/happenings/{id}/calendar/past_events/{backToDate}/` | 65 | implicit + member/admin | |
| GET | `/monitor/haus/calendar/events/` | 95 | pub | Hard-coded group 37 (hafnar.haus wall monitors) |
| POST | `/happenings/{id}/calendar/events/` | 117 | auth + member/admin | Create group event |
| PATCH | `/happenings/{id}/custom_language/{langCode}/` | 162 | admin | |
| GET | `/happenings/{id}/video_chat/` | 189 | login(h) | Deprecated Twilio token |
| POST | `/video_chat/webhook/` | 231 | **pub** | Deprecated Twilio status callback → Pusher trigger |
| GET | `/video_chat/{retrieve}/` | 264 | **pub** | Deprecated: lists live Twilio rooms (+participants) |
| GET | `/happenings/` | 316 | pub, env-mo | List groups |
| POST | `/happenings/{id}/photo_stream/` | 340 | implicit + registered | Add photo |
| GET | `/happenings/{id}/photo_stream/{limit}/` | 375 | pub | |
| GET | `/happenings/topics/` | 421 | optional | Latest comments across the user's groups |
| GET | `/happenings/{id}/topic/` | 444 | pub; private group → members/admin | Group discussion |
| POST | `/happenings/{id}/comment/{commentId}/reply/` | 490 | login(h) | Reply + email |
| POST | `/happenings/{id}/topic/` | 553 | login(h) | Post; `email_announcement=1` mails all registrants; optional .ics invite |
| GET | `/happenings/{id}/` | 616 | pub, env-mo; limited view if private | Group detail + last 5 comments |
| GET | `/happenings/{id}/registrations/` | 663 | admin(h) | Registrations with answers |
| POST | `/happenings/{id}/config/` | 685 | admin | access / registrations / name / notify settings |
| PATCH | `/happenings/{id}/registrations/{userId}/` | 700 | admin(h) | Change registration type |
| DELETE | `/happenings/{id}/registrations/{userId}/` | 722 | admin or self | Unregister |
| DELETE | `/happenings/{id}/` | 743 | admin(h) (anonymous → fatal) | Deactivate group |
| POST | `/happenings/{id}/schedule/` | 765 | admin (interactor) | Set schedule |
| POST | `/happenings/{id}/applications/` | 785 | pub (anonymous then crashes in `announceApplication`) | Apply when `registrations=applications` |
| POST | `/happenings/{id}/registrations/` | 811 | login(h) | Self-register when `registrations=open` |
| POST | `/happenings/{id}/registrations/{userId}/` | 841 | admin | Register a user |
| POST | `/happenings/` | 852 | admin | Create group |

### `silex/AccountsRoutes.php` (coworking billing; all **admin**)
| Method | Path | Line | Purpose |
|---|---|---|---|
| GET | `/accounts/` | 11 | List accounts + field metadata |
| GET | `/accounts/{id}/invoices/{month}/` | 33 | Preview monthly invoice payload |
| POST | `/accounts/{id}/custom_invoices/` | 46 | Create Payday customer + send custom invoice |
| POST | `/accounts/{id}/invoices/` | 89 | Send monthly invoice via Payday |
| GET | `/accounts/invoiced/{month}/` | 126 | Invoiced/paid summary |
| GET | `/accounts/updated/{month}/` | 148 | Pull invoices from Payday into accounts; returns debug data (state change on GET) |
| GET | `/accounts/invoices/{dateFrom}/{dateTo}/` | 236 | Same sync by date range (state change on GET) |
| POST | `/accounts/import_accounts/` | 295 | Import .xlsx (PhpSpreadsheet); hard-coded "Hlemmur" items and prices; dead code after the `return` |
| POST | `/accounts/{id}/subscriptions/` | 379 | Add subscription line |
| POST | `/accounts/` | 402 | Create account (+subscriptions) |
| DELETE | `/accounts/{id}/` | 447 | Disable |
| DELETE | `/accounts/{id}/subscriptions/{sid}/` | 461 | |
| PATCH | `/accounts/{id}/subscriptions/{sid}/` | 477 | |
| PATCH | `/accounts/{id}/` | 508 | |

### `silex/ResourcesRoutes.php` (bookable rooms)
| Method | Path | Line | Guard | Purpose |
|---|---|---|---|---|
| PATCH | `/resources/{id}/` | 11 | admin | name, price, image, description |
| GET | `/resources/{id}/` | 39 | pub | Resource + bookings |
| DELETE | `/resources/{id}/` | 52 | admin | |
| GET | `/resources/type/{type}/` | 67 | pub | List by type |
| DELETE | `/resources/{id}/bookings/{bookingId}/` | 89 | auth + owner/admin | |
| POST | `/resources/{id}/bookings/` | 110 | auth | Book. If the resource has a price and the user-supplied `accounting_id` is not in `free_for_accounts`, a **Payday invoice** is created for that `accounting_id`. |
| POST | `/resources/` | 174 | admin | Create |

### Response classes in `backend/src/API/v1/`
These are plain mappers from domain objects or stored JSON documents to public shapes. `Response.php` is an empty base class.

| Class | Shape it produces |
|---|---|
| `ExperimentResponse` | `getPreview($showTeamEmails)`, `get()`, `getAdmin()`, `getACL()` (`view`/`edit`/`admin`), `getMyRelationships()` |
| `PlatformResponse` | `get()` public, `getConfigRaw()`, `getUi()`, `getAdmin()` (adds the secret access code). `geo_provider_key` is sent `strrev`'d (`:40`). |
| `UserPreviewResponse` | Email/meta only when private info is allowed |
| `HappeningResponse` | `get` / `getPreview` / `getLimited` |
| `CommentsResponse` | |
| `TeamResponse`, `TagsResponse`, `LanguageResponse`, `ImageCollectionResponse`, `ExperimentFundingResponse` | |
| `CalendarEventResponse`, `EventResponse` | |
| `AccountResponse`, `AccountInvoicesResponse`, `SubscriptionResponse` | Hard-coded Hlemmur item options |
| `ResourceResponse`, `SpaceResponse`, `PageMetadataResponse` | |

---

## 3. Domain model and vocabulary

### Vocabulary
| Code term | Product meaning |
|---|---|
| **Platform / tenant** | One community site. Identified by `platform_key` (env), a `kf_platforms` row, and `kf_platform_config` overrides. |
| **Experiment** | A **submission**. The routes say "experiment"; the UI and templates say "submission". Its **stage** is really its *type*. Stage handles come from `config/ui_config.json` → `platform.ui.stageHandles`: 0 challenge, 1 idea, 2 project, 3 insight, 4 topic, 5 issue, 6 guide, 7 policy, 8 organisation, 9 portfolio_item, 10 question. "Ideation stages" are 3, 0, 1, 2. `moveToNextStage` refuses to go past 5 (`Experiment.php:304-338`). Stage 8 is **"joinable"** (`:88-96`). |
| **Happening** | A **group** (the UI calls them groups). Has a registration list, applications, schedule, config (`access` private/public, `registrations` open/applications, `notify_on_registrations`, which defaults to `["2"]`, a hard-coded user id; `Happening.php:78-91`), and its own discussion topic and calendar. |
| **CalendarEvent** | A dated event in one or more calendars, named by string: `activities`, `happening-{id}`, `submission-{id}`. Has attendance, +N guests, price, max participants. |
| **Topic / Comment** | A discussion thread. The **topic id is the UUID** of the owning experiment or happening. Comments (`kf_comments`) have UUID `comment_id`, `parent_id` for replies, `format` plaintext or `tiptap-json`, images, reactions, linked submissions, and mentions (`entityMention` nodes, `Comment.php:355-385`). |
| **Network / Root / Node / Relationship** | Generic follower graph in `kf_relationships`: `(follower_table, follower_id) → (leader_table, leader_id, relationship_type)`. Used for like / help / updates / related / upvote. `NetworkRepository::saveNetwork` deletes and reinserts all followers of a root (`:76-106`). |
| **Team** | Members of an object with roles `creator` / `member` (`kf_team_members`, `object_type` + `object_id`). Team membership grants edit rights. |
| **Language fields** | Every translatable text lives in `kf_lang_fields`, keyed by `(object_type, object_id, type default/custom, content_key, lang_code)`. Formats: plaintext / html / json. `LanguageFields::getDocument()` emits both purified `html` and raw `value` (`LanguageFields.php:67-86`). Custom keys include `{stage}_long_description`. |
| **Space** | Experiment "space" grouping (`kf_spaces`); half-implemented. |
| **Tag** | Multilingual label (`label_language` JSON), with `type` and `types`. Attached to experiments and profiles through JSON arrays, and to profiles with a typed relation in `kf_tags_to_entities.tag_type` (degrees, roles, interests, …; see `ui_config.json` `profileTagTypes`). |
| **Account / Subscription / Resource / Booking** | Coworking-space billing (Hafnar.haus / "Hlemmur"). An Account is a paying company (kennitala, billing emails). Subscriptions are recurring line items (units × unit price, start/end, pro-rated). Resources are bookable rooms (`kf_resources`); bookings sit in `kf_resources_bookings`. Invoices go out through Payday. |
| **Question / Answer / Survey** | Q&A attached to experiments (`kf_questions` / `kf_answers` + lang fields), plus free-form survey results (`kf_survey_results`). |
| **Event** (`src/Event`) | Domain events and pub/sub. Not to be confused with calendar events. |

### Modules (`backend/src/`)
| Module | What it holds and how it's built |
|---|---|
| `Platform.php` | Tenant aggregate: config access, site access and registration rules, secret access code, `visibleSubmissionStagesToUser()` via `AccessControl` (`:39-56`), rate limiting (`limit()` → `Event\RateLimiter`), event publishing, image storage URL (hard-coded S3 bucket, `:203-207`). |
| `PlatformRepository`, `PlatformNewAction` | Tenant load and provisioning. Provisioning creates the platform, an admin user, env vars `platform_key` and `platform_secret_key` in the `environments` DB, host `<key>.missions.dev`, and a notification email. |
| `Environment*` | Multi-tenant env loading from the `environments` DB. |
| `AccessControl.php` | Rule engine. Rules are `{target, action, roles, permission, priority}`; `*` wildcard per `::` segment; roles `all` / `authenticated` / `admin`; highest priority wins; default deny. Rules come from `platform.access_control_rules`; the default file allows `submission-types::*` read for all (`config/access_control_rules.json`). **Only used for submission-stage read visibility.** |
| `Experiment/` | `Experiment` entity (traits: Language, CustomLanguage, Team, ImageCollection, Setting); `ExperimentRepository`; `ExperimentRepositoryFilter`; `ExperimentInteractor` (load, likes/follows, view restrictions, sharing); `ExperimentEditor` (all mutating use cases with `canEdit*` checks); `FundingSources`; `NewExperimentsNewsletter`; events `NewExperimentEvent` / `ExperimentUpdatedEvent`; `Space` + `SpaceRepository`. |
| `User/` | `User` entity (admin when `group_id === 1`, `UserRepository.php:222`; settings JSON; profile meta; tags; skills; links; contact info; geo); `UserRepository` (hydration, JWT lookup, create); `UserEditor`; `AuthInteractor`; `Hashers/`; `Action/GetProfile`; `UserRepositoryFilter`; `Profile`; `UserPreview`; events. |
| `Happening/` | `Happening`, `HappeningRepository` (users and applications stored as JSON columns), `HappeningInteractor` (registration, applications, notifications), `CalendarEvent` + `CalendarInteractor`. `HappeningMockRepository` is unused. |
| `Discussion/` | `Topic`, `Comment`, `DiscussionRepository` (persists and pushes to Pusher on every save, `:22-79`), `CommentToTopicAction` (create comment, build notification, announce), `CommentEvent`. |
| `Accounting/` | Described in the vocabulary table; plus `InvoicingService` (Payday) and `MembersSource` (xlsx import). `Invoices.php` is broken and unused. |
| `Network/`, `Team/`, `Tag/`, `Setting/` | `Setting/` holds `SettingsGroup` / trait; `SettingRepository` covers `kf_settings` and `kf_translations`. |
| `Language/` | `LanguageFields`, `Field` (HTMLPurifier for html, `JsonToHtml`, markdown), `Base64InlineEncodingToFile` (extracts `data:` images to S3), `LanguageTrait`, `CustomLanguageTrait`. `Language.php` and `LanguageFieldsDocument.php` are unused. |
| `Content/` | `Link`, `LinkMeta` (scraper), `GeographicLocation` (parses client-supplied Google-Places-style JSON; no server-side geocoding), `ContactInformation`, `AttachmentFileRepository`, `TiptapEntityMentionExtension`. |
| `Image/` | `Image`, `ImageCollectionTrait`, `ImageFileRepository` (S3), `ImageRecordRepository` (`kf_images` photo streams). |
| `Question/`, `Stats/` | `Stats/Record` holds daily pings in `kf_stats`; `Stats/Dashboard`. |
| `Event/` | `Publisher` singleton, `Subscriber` interface, the four subscriptions, `RateLimiter`, `SlackWebhook`, and `Pulse` / `EveryMinuteEvent` (cron-like, **never wired up**). |
| `Communicator/` | `SignedJwt` (HS256 helper); `Event` (SNS publish, unused). |
| `Http/` | `Cookie`, `DiscourseSSO`: unused. |
| `Notification.php` | Email composition, queueing (DB + SQS), SES sending, .ics attachments, Mailtrap test mode. |
| Misc | `CacheRepository` (DB cache in `kf_cache`), `Currency`, `Keyname` (identifier validation: alnum plus `._-`), `UUID`, `Timings` (profiling, partly returned in the profile response), `CustomFields` (unused), `PushToFront` (direct Pusher wrapper). |

### Recurring patterns
- **Entity + Repository + Interactor/Editor + Response.** Editors take `$currentUser` and enforce `Experiment::canEdit*`. Experiment's own comment says "Outside clients need to enforce access control" (`Experiment.php:~820`).
- **Denormalised JSON documents.** On every save the full aggregate is serialised into a `document` JSON column: `kf_experiments.document` (`ExperimentRepository.php:285-321`) and `kf_profiles.document` (`UserRepository.php:549+`). List and read endpoints serve these documents, and filters query JSON paths inside them. That is why the `rewritedocuments` maintenance routes exist.
- **Domain events.** Each `repository->save()` publishes an event. Subscribers then do synchronous side effects inside the HTTP request: DB insert, Pusher call, SES email, Slack call.
- **Heavy hydration.** `getFromEloquentModel` loads profile, tags, happenings, skills and network for every user. Teams load each member that way. The result is N+1 query storms.

---

## 4. Database

### 4.1 Schema (`backend/src/Database/schema.sql`, MySQL 5.7 dump, DB `idea-village-sandbox`, no seed data)
There are **34 tables**, all prefixed `kf_`. There are **no foreign keys**; links are by convention.

| Table | Key columns | Role |
|---|---|---|
| `kf_platforms` | `platform_key` (unique), `uuid`, `status` | Tenant registry |
| `kf_platform_config` | `name`, JSON `value`, `platform` | Per-tenant config overrides |
| `kf_settings` | `variable`, `value` | Legacy settings |
| `kf_translations` | `key`, `value`, `language` | UI string overrides |
| `kf_users` | `id`, `email`, `password`, `hash_type` (`ion`/`native`), `salt`, `group_id` (1 = admin), `active`, JSON `settings`, Ion Auth leftovers (`activation_code`, `forgotten_password_code`, `remember_code`, `ip_address`, `username`=UUID) | Accounts |
| `kf_profiles` | `user_id`, names, company, phone, `document_number`, `description`, `content`, JSON images/links/tags/meta, JSON **`document`** | Public profile + denormalised doc |
| `kf_skills`, `kf_skill_categories`, `kf_profiles_skills` | | Skills |
| `kf_groups`, `kf_roles` | | Legacy, unused |
| `kf_experiments` | `uuid`, `stage`, JSON document/images/show_in/show_in_placement/tags/links/funding/settings/attachments, `disabled` | Submissions |
| `kf_lang_fields` | `content_key`, `lang_code`, `object_id`, `object_type`, `content`, `format`, `type` | All translatable text |
| `kf_team_members` | `object_type`, `object_id`, `user_id`, JSON `roles` | Teams |
| `kf_relationships` | `follower_*`, `leader_*`, `relationship_type` | Graph |
| `kf_comments` | `comment_id` (UUID), `topic_id`, `parent_id`, `value` / `value_json`, `format`, JSON images/reactions/submissions/attachments, `active` | Discussion |
| `kf_happenings` | `uuid`, `name`, JSON `users_registered` / `applications` / `schedule` / `settings`, `active` | Groups |
| `kf_calendar_events` | `start`, `end`, JSON `calendars` / `data`, `name`, `location`, `user_id`, `max_participants` | Calendar |
| `kf_images` | `filename`, `context`, `user_id` | Photo streams |
| `kf_tags`, `kf_tags_to_entities` | | Tags |
| `kf_questions`, `kf_answers`, `kf_survey_results` (column typo `survery_name`) | | Q&A / surveys |
| `kf_events` | `event_type`, JSON `data`, `occurred_on`, `actor_id` | Activity log / feed |
| `kf_email_queue` | `uuid`, recipients/cc/bcc/attachments JSON, `html_message`, `started_sending_at`, `finished_sending_at` | Outbound mail log |
| `kf_rate_limited_events` | `key`, `set_time`, `expire_time`, `allowed_occurances` | Rate limiter |
| `kf_cache` | `key` (unique), JSON `stash`, `store_until` | Cache |
| `kf_stats` | `day`, JSON `ping` | Daily active users |
| `kf_accounts`, `kf_subscriptions`, `kf_resources`, `kf_resources_bookings` | | Billing / booking |
| `kf_spaces` | `uuid`, `key`, JSON fields, soft delete | Spaces |

The separate **`environments` DB** (tables `variables(name, value, collection)` and `hosts(hostname, collection)`) is **not in `schema.sql`**. Its models are `Database/Table/EnvironmentVariables.php` and `Hosts.php`.

### 4.2 Multi-tenancy
- **Tenants share one database.** Rows are partitioned by a `platform` varchar column.
  - Each Eloquent model in `src/Database/Table/*` sets `platform = getenv('platform_key')` on create.
  - Each adds the global scope `PlatformScope` (`src/Database/PlatformScope.php:18-22`): `platform = <current> OR platform = 'global'`. Rows tagged `global` are visible to every tenant.
- Exceptions:
  - `EmailQueue` has no scope.
  - `ProfilesSkills` has its scope commented out.
  - `Platform` and `PlatformConfig.configs()` call `withoutGlobalScopes`.
  - Explicit bypasses: `/stats/global/` and `/all_platforms/users/preview/` (`UserRepository.php:605-614`).
- Some tables add a second scope: `active` / `disabled` / `status != disabled`.
- Column defaults like `'thingeyri'` or `'sandbox'` are leftovers from old tenants.
- Several deployment modes are possible: one DB per tenant via `database_name`, or a shared DB plus `load_variables_by_hostname`. The code supports both.

### 4.3 How queries are built
- **Eloquent ORM** everywhere (Capsule). One raw PDO query is used for env loading (`Environment.php:21-40`, prepared).
- JSON features: `whereJsonContains`, `whereJsonLength` (`HappeningRepository.php:76`), and `->` JSON paths (`document->team->{id}->in_team`, `show_in->{place}`, `settings->...`). The use of `whereJsonLength` implies Illuminate ≥ 5.7.
- Raw SQL fragments:
  - `whereRaw` in `ExperimentRepositoryFilter.php` (tag filter).
  - `ExperimentRepository.php:537`, called only with the constant `'index'`.
  - `orderByRaw` with constants (`:88`, `:92`).
  - `DB::raw('count(*)')`.
  - `UserRepositoryFilter.php:68` passes a SQL expression to `where()` as a column name. It gets backtick-escaped, so it is broken but not injectable.
- Submission list filters (`ExperimentRepositoryFilter::fromArguments`, `:17-97`):
  - `team_member_id`, `place`, `stage`, `tag`, `limit`, `offset`, `relevance_more_than`, `has_been_sent_in_newsletter`, `newer_than_seconds`, `newer_than_days`, `older_than_days`, `order_by` (`most_relevant` / `last_major_update`).
  - `/experiments/preview` loads **all** matching documents with no default limit, then filters by stage ACL in PHP.
- **No transactions anywhere** (no matches for `transaction`). Multi-step saves (team, lang fields, network, document) are not atomic.

---

## 5. Auth and security

### 5.1 How authentication works
- **Passwords**
  - New passwords use `NativeHasher`: `password_hash(PASSWORD_DEFAULT)`, bcrypt. The unused `$cost=12` option is ignored (`src/User/Hashers/NativeHasher.php:11-23`).
  - Legacy CodeIgniter Ion Auth hashes (`salt[10] + sha1(salt.password)`) are still accepted (`IonAuthLegacyHasher.php:23-41`). The hasher is chosen by `hash_type` (`UserRepository.php:226-239`). Hashes are **not upgraded on login**.
  - Minimum length is 6 (`User.php:533`, `AuthInteractor.php:49`).
- **Tokens**
  - `Communicator\SignedJwt` (`src/Communicator/SignedJwt.php`) uses lcobucci/jwt 3.3.3 with HS256 and `platform_secret_key`, which must be at least 32 characters (`:45-55`). It checks signature and expiry. In lcobucci 3.x, `verify()` rejects a mismatched `alg`.
  - Claims:
    - Login: `user_id` (`AuthInteractor.php:182-195`, TTL **6 months**, `UsersRoutes.php:124`).
    - Registration: 12 h (`UserRepository.php:142`).
    - Password reset: `change_pass_for_user_id`, 1 h (`AuthInteractor.php:103-111`).
    - Newsletter unsubscribe: `unsubscribe_user_id` / `unsubscribe_to_topic`, 180 days (`NewExperimentsNewsletter.php`).
    - `getWithToken` also accepts a legacy `user_email` claim (`UserRepository.php:402-437`).
  - All token types share **one key** and carry no audience or purpose claim.
  - There is no revocation or logout. Disabling a user works, because the `enabled` scope makes lookup fail.
  - The frontend stores the token in `localStorage` (`frontend/src/backend.js:28-29`).
  - Apache/FastCGI often strips the `Authorization` header unless configured; worth mentioning in deployment docs.
- **Roles**
  - Admin: `kf_users.group_id === 1` (`UserRepository.php:222`, `User::makeAdmin`). Everyone else is a "member".
  - Object-level rights:
    - Team membership → edit content and settings, disable.
    - Admin only → funding, `show_in`.
  - `AccessControl` rules govern only which submission stages a user can read.
  - Groups: private/public access and member checks are written per route.
- **Registration gating** (`UsersRoutes.php:139-162`):
  - `site_registration_enabled` defaults to true only when site access is `public` (`Platform.php:260-276`).
  - Otherwise registration needs an admin or the secret access code (`Platform::getSecretAccessCode`, 128-bit hex).
- **CSRF**: not applicable for the API, since auth is a bearer header and no cookies are used.

### 5.2 Security review

A security review of this code was done along with this documentation. To avoid public disclosure, its findings are **not** in this public repository. They go to the maintainers through the channel in `SECURITY.md`.

---

## 6. External integrations

| Service | Where | How |
|---|---|---|
| **AWS S3** | `bootstrapme.php:174-182`; `ImageFileRepository`; `AttachmentFileRepository` | Buckets `idea-village-image-host` (public; key prefix = platform key) and `attachments-bucket` (private; presigned GET valid 20 min). Credentials from the default chain (`AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY`). The README lists `AWS_ACCESS_KEY`, which is the wrong name. |
| **AWS SES** | `Notification::sendWithSES*` (`:406-560`) | `sendRawEmail` with a MIME message built by PHPMailer, synchronous by default (`sendWithSESNotAsync`). Newsletter uses `sendWithSESAsync`. `sendBulkWithSES` is placeholder code. |
| **AWS SQS + email worker** | `Notification::addToEmailQueue` (`:53-131`) | Chunks recipients 20 per message. Writes a `kf_email_queue` row, then `SqsClient::sendMessage` to `send_email_queue_url` with staggered `DelaySeconds`. `workers/email/` (separate composer project: PHP 8-era Dotenv 5, Monolog 3) runs as an Elastic Beanstalk worker. The SQS daemon POSTs JSON to `public/messages.php`, `EmailSender` sends one SES raw email per recipient, and `Logger` writes to CloudWatch group `missions-worker`. The worker does **not** update `kf_email_queue.finished_sending_at`. |
| **AWS SNS** (legacy) | `POST /send_email_in_queue/`; `Communicator/Event.php` | Older SNS → HTTP path calling `Notification::sendEmailInQueue($uuid)`. `Communicator\Event` publishing is unused. |
| **Pusher** | `Event/PushToFrontEndSubscription.php`, `PushToFront.php`, `DiscussionRepository::saveComment` | Cluster `eu`, TLS. The channel is the platform key (public), or `{key}-comments-{topicId}`. Events pushed: `experiment_updated`, `new_experiment`, `new_user`, `user_updated`, `comment*`, `happening_updated`, `calendar_event_updated`, `new-comment`, `comment-changed`. Payloads hold ids only. The public key reaches the frontend via `GET /platform/` (`pusher_key`). |
| **Twilio Video** | `ExperimentsRoutes.php:288-326`, `HappeningsRoutes.php:189-312` | AccessToken + VideoGrant. Room name is `"{platform},{type},{id},{uuid}"`. Env: `twilio_account`, `twilio_api`, `twilio_secret`, `twilio_auth_token`. Marked deprecated. |
| **Payday** (Icelandic invoicing) | `src/Accounting/InvoicingService.php` | Guzzle. `invoicing_mode` selects `api.test.payday.is` or `api.payday.is`. Flow: `POST /auth/token` (client id + secret) → `/customers` → `/invoices` with `Api-Version: alpha`. Dev mode replaces the customer email with a fake one. Env: `payday_client_id[_development]`, `payday_secret[_development]`. |
| **Slack** | `Event/ExternalServiceSubscription.php`, `SlackWebhook.php` | On `new_experiment`, posts Block Kit to webhook URLs configured in `platform.external_to_events_subscriptions` (optionally filtered by stage). |
| **Link metadata scraping** | `LinkMeta` | curl + DOMDocument: title, meta, og:*, twitter:*, canonical, favicon. Cached in `kf_cache`. |
| **Google Maps / Places** (client side) | `geo_provider_key` env or config, sent to the frontend; `GeographicLocation` parses client JSON | No server-side calls. |
| **Mailtrap** | `Notification::sendWithMailTrapTester` | Enabled with env `emailtesting=mailtrap` (SMTP 2525, blank credentials). |
| External calendars | — | No calendar sync. Only outbound `.ics` attachments in announcement emails via `spatie/icalendar-generator` (`Notification::addCalendarEvent`, `:197-216`). |

---

## 7. Notifications, templates, translations, background work

- **`Karolina\Notification`**
  - The constructor loads the platform again from the DB, defaults the recipient to `email_info` and the sender to `email_noreply` / site title (`:41-51`).
  - API: `setSubject` / `setSubjectFromTemplate`, `setBodyFromTemplate` / `Html` / `Plaintext`, `setToUserIds` / `setSingleRecipientEmail` / `setMultipleRecipientsEmail` / `setToAdmin` / `addCCEmail` / `addBccEmail`, `addCalendarEvent`.
  - Two delivery modes:
    - `send()`: synchronous SES, used for password reset, contact, share, new-submission notices and platform creation.
    - `addToEmailQueue()`: SQS worker, used for comments, replies, mentions, group registrations and applications.
  - `setToUserIds()` resolves ids through `UserRepository::getDocumentsByIds()`, which queries **`kf_profiles.id`, not `user_id`** (`UserRepository.php:44-46`). It is correct only while user and profile ids coincide.
- **Twig 1** via the Silex `TwigServiceProvider` (`bootstrapme.php:132-165`).
  - Loader chain: `templates/platforms/{key}/twig/` (per-tenant overrides, none in the repo), then `templates/default/twig/`.
  - Globals: `base_url`, `site_title`, `ui`.
  - Autoescape is the Twig 1 default (html).
- **Templates** in `templates/default/twig/notifications/`, all extending `base_email.twig`:

| Template | Used by |
|---|---|
| `password_reset` | `AuthInteractor` |
| `contact_notification_subject` / `_body` | `/contact/`, `/users/{id}/contact/` |
| `comment`, `comment_reply` | Submission comments |
| `happening_comment`, `happening_announcement_reply` | Group posts |
| `happening_registration`, `happening_unregistration`, `happening_application` | `HappeningInteractor` |
| `submission_shared` | Share |
| `new_submission_published` | `NotifyUsersSubscription` |
| `newsletter` | Newsletter (also rendered by `/experiments/emailout/preview…`) |
| `generic` | Platform creation |
| `submission.twig`, `newsletter_preview.twig` | Unused |

- **Translations**
  - The UI strings live in repo-root `translations/en.json` and `is.json` (used by the frontend).
  - Per-tenant overrides live in `kf_translations`. They are served in `GET /platform/` and edited through `PUT /platform/translations/{lang}/`, with tags stripped except `<strong><p><a><h2-4>` (`SettingRepository.php:40-60`).
  - The Silex `TranslationServiceProvider` is registered with locale `en` (`bootstrapme.php:122-124`), but no catalogs are loaded. `src/Language/Language.php` (Symfony translator + YAML) is unused.
  - Content translation is the `kf_lang_fields` system.
- **Background jobs and cron**
  - There is no in-repo cron. `Event/Pulse` + `EveryMinuteEvent` + `User/UserEveryMinuteSubscription` (which emails `monitor@karolina.io` every minute) are dead code.
  - The newsletter is triggered by hitting `GET /experiments/emailout/send/{newer_than_days}/{min_relevance}/`. That is presumably an external cron or a manual call. It picks the top 10 submissions newer than N days, above a relevance threshold and not yet sent, mails every non-unsubscribed user, then marks the submissions `has_been_sent_in_newsletter`.
  - The only asynchronous component is the SQS email worker.
- **`NotifyUsersSubscription`**
  - For `new_experiment` it emails the users listed in `platform.users_to_events_subscriptions`.
  - For any *other* subscribed event type it sends a debug email ("A new event has occurred…" with `print_r` of the data) to `email_info` (`src/Event/NotifyUsersSubscription.php:31-35`).

---

## 8. Dependencies, runtime, tests, tech debt

### Runtime
- `.valetphprc` = `php@7.4` (repo root). `composer.json` `config.platform.php = 7.4`, `require php >= 7.3`.
- I saw no PHP 8-only syntax. PHP 7.4 features are used (typed properties in `SlackWebhook.php`).
- README asks for MySQL 5.7 and possibly disabling STRICT mode. JSON columns need ≥ 5.7.8.
- PHP 7.4 has been EOL since Nov 2022.

### Composer dependencies (`backend/composer.json`)
| Package | Notes |
|---|---|
| `silex/silex ^1.3` | EOL 2018 |
| `illuminate/database ^5.3`, `illuminate/events ^5.2` | EOL |
| `twig/twig ~1.0`, `twig/extensions` | Abandoned |
| `symfony/* ^3` (http-foundation, yaml, translation, twig-bridge, finder, stopwatch) | EOL |
| `lcobucci/jwt 3.3.3` | Pinned; the old API is deprecated |
| `guzzlehttp/guzzle ^7.2` | Payday |
| `aws/aws-sdk-php ^3.221` | |
| `pusher/pusher-php-server ^6.1`, `twilio/sdk ^6.25` | |
| `phpmailer ^6.6` | |
| `ezyang/htmlpurifier`, `respect/validation ^1`, `ramsey/uuid ^3`, `vlucas/phpdotenv ^2.2` | v2 API |
| `hassankhan/config ^0.9` | Noodlehaus |
| `moneyphp/money`, `phpoffice/phpspreadsheet`, `opis/json-schema`, `ueberdosis/tiptap-php`, `spatie/icalendar-generator 1.0.6`, `tburry/pquery`, `league/commonmark ^0.11` | |

- **Declared but unused** (no references in `src/`, `silex/`): `nategood/httpful`, `monolog/monolog`, `paragonie/csp-builder`, `flow/jsonpath`, `woutersioen/sir-trevor-php`, `fzaninotto/faker` (abandoned), `asika/autolink`, `justinrainbow/json-schema`, `aws/aws-php-sns-message-validator`.
- Effectively dead: `cviebrock/discourse-php` and `delight-im/cookie`, used only by unused classes.
- Dev dependencies: `phpunit ^9.5`, `mockery dev-main`, `phpmd`.

### Testing
- There are **no tests**: no `tests/` directory and no `phpunit.xml`.
- `silex/AppTests.php` is broken: it references an undefined `$envVariables` and `defaultconfig.env`, and includes a nonexistent `bootstrap.php`.
- The root README says "tests need to be reworked".
- Mock helpers exist but are unused (`UserMockFactory`, `HappeningMockRepository`).

### Dead or legacy code
- **Classes:** `Event/Pulse`, `EveryMinuteEvent`, `UserEveryMinuteSubscription`, `Http/Cookie`, `Http/DiscourseSSO`, `CustomFields`, `Language/Language`, `LanguageFieldsDocument`, `Communicator/Event`, `Accounting/Invoices` (non-namespaced, references a nonexistent class), `Notification::sendBulkWithSES` (placeholder values).
- **CodeIgniter leftovers:** `$app['ci']` is passed everywhere; `UserRepository::findOrCreateFromEmail` uses `$this->ci->ion_auth` (`:193-205`), with comments such as "Incredibly stupid hack" (`:195`, `:424`).
- **Legacy tables:** `kf_groups`, `kf_roles`, plus the Ion Auth columns in `kf_users`.
- **Unused env vars and constants:** `platform_config`, `allow_api_access`, and the constants in `bootstrap.php:63-68`.
- **Duplicate config:** `backend/platform_config/sandbox.yaml` duplicates `config/sandbox.yaml`, and the backend README describes a config selection that no longer exists.
- **Deprecated but still mounted:** the Twilio video routes.
- **Tenant-specific hard-coding:** the 7 platform keys in `UsersRoutes.php:349-357`, group 37 (`HappeningsRoutes.php:97`), the "Hlemmur" items and prices (`AccountsRoutes.php:295-376`, `SubscriptionResponse.php`), and user id `"2"` as the default registration notifier (`Happening.php:82`).

### Bugs found while reading
- `TopicsRoutes.php:133` uses an undefined `$frontEndUrl`, and `:145` calls `$comment->getMentionedUserIds()` on an undefined `$comment`. The reply route therefore fatals *after* saving and queueing email, and mention emails plus the `comment_reply` event never fire.
- `UsersRoutes.php:688` calls `getUserId()`, which doesn't exist on `User`.
- `HappeningInteractor.php:251` calls `gerRegisteredUsers()` (typo).
- `HappeningsRoutes.php:785-808`: an anonymous application passes `userId=null`, then `announceApplication` calls `getById(null)` and throws.
- `Notification.php:138` dereferences `$email->platform` before the null check, and `putenv`s `platform_key` from DB data.
- `ExperimentsRoutes.php:268-285`, `PUT /experiments/spaces/`, indexes an object as an array and saves a blank Space. `Database/Table/Space.php:32-35` writes the placeholder `['what','yes']` into `custom_language_fields`.
- `ExperimentInteractor.php:93-99`: the `happening_registered` view restriction only checks the first happening.
- `UserRepository.php:85`: `withoutGlobalScope('platform')` uses the wrong scope name (the scope is keyed by class), so the duplicate-email check for newly provisioned tenants never matches.
- `Routes.php:668-689`: `$actorId` is undefined for numeric `{time}`; an anonymous `my_last_fetched` fatals.
- `Routes.php:505` and `:561`: duplicate `GET /platform/config/`.
- `Routes.php:38`: cache TTL is 5 s against a comment saying 1 day.
- `Routes.php:528` resolves the schema path relative to the current directory.
- `PUT /platform/config/` with a key that has no schema file feeds a `null` schema to the validator.
- HTTP status codes for authorization errors are inconsistent (200, 401, 500).
- Many routes return strings or HTML under a forced JSON `Content-Type`.
- Trailing slashes are inconsistent across routes (Silex redirects only GETs).

### Performance and structural debt
- Per-request synchronous side effects: Pusher, SES and Slack are called inside requests.
- Full-aggregate re-hydration and re-serialisation on every save.
- N+1 user hydration (teams, profiles).
- Unbounded list endpoints (`/experiments/preview`, `/users/preview`).
- Delete-and-reinsert of relationships with no transactions.
- A DB-backed rate limiter and cache.
- Business logic sits in route closures despite the stated DDD layering.

---

## 9. Items the docs will need to resolve (not determinable from this repo)
1. The real `public_html/api/index.php`, the web-server rewrite rules, and the Authorization-header passthrough.
2. The `environments` DB schema and how hosts and collections are provisioned in production.
3. Whether production sets an `access` env var, which decides finding 5.
4. Installed library versions; `composer.lock` is gitignored.
5. Whatever calls the newsletter endpoint (cron?), and how the SQS queue and EB worker are deployed.
6. The contents of the gitignored `silex/sandbox/SandboxRoutes.php`.
