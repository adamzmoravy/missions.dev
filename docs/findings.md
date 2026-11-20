# Findings: tech debt, bugs and next steps

Snapshot of upstream `ae208d2` (November 2026). Security-relevant findings are deliberately left out
of this public document; report those privately as described in `SECURITY.md`.

## 1. State of the codebase

| Area | Status |
|---|---|
| Runtime | PHP 7.4 (EOL since Nov 2022). The Debian 11 base of the official `php:7.4` images is archived too. |
| Framework | Silex 1.3 (EOL 2018), Pimple 1, Symfony 3 components, Twig 1, Eloquent 5.x standalone, all EOL |
| Dependency install | Fails out of the box. There is no `composer.lock` (`*.lock` is gitignored). The `composer.json` `name` was invalid (fixed in this branch). Current Composer refuses most pinned versions because they carry published security advisories. Installing anyway gives 41 advisories across 11 packages. |
| Tests | None. `backend/silex/AppTests.php` is broken, and the frontend has no tests, lint or type checks. |
| Install docs | Only `backend/README.md` (outdated: it describes a `platform_config` file selection the code no longer does, and names the wrong `AWS_ACCESS_KEY` variable) |
| Seed data | None. A fresh DB needs a `kf_platforms` row and the separate `environments` schema before the API will boot (both are handled by this branch's Docker setup). |
| Frontend | Vue 3 and Vite 7 are current, but there are many Vue 2 leftovers (`this.$http`, `$root.eventBus`, `inline-template`, `beforeDestroy`) that break at runtime |

## 2. Bugs found while reading (non-security)

### Backend
- **Comment replies crash after saving.** `TopicsRoutes.php:133` uses an undefined `$frontEndUrl`, and `:145` calls a method on an undefined `$comment`. The reply is stored and the e-mail queued, but mention e-mails and the `comment_reply` event never fire, and the client gets a 500.
- `UsersRoutes.php:688` calls the non-existent `User::getUserId()`, so `GET /users/me/experiments/preview/...` crashes.
- `HappeningInteractor.php:251` calls `gerRegisteredUsers()` (typo).
- `HappeningsRoutes.php:785-808`: an anonymous group application throws in `announceApplication(getById(null))`.
- `PUT /experiments/spaces/` is broken.
- `Notification::setToUserIds()` resolves recipients by `kf_profiles.id` instead of `user_id` (`UserRepository.php:44-46`). It is only correct while the two ids coincide.
- The worker never sets `kf_email_queue.finished_sending_at`.
- **Realtime made writes fail.** `PushToFront` and `PushToFrontEndSubscription` called Pusher unconditionally. With no or invalid Pusher credentials, every save that fires a realtime event (submission updates, comments, users) returned a 500 *after* the data was written. This branch now skips the push when no key is configured.
- `GET /platform/config/` is defined twice (`Routes.php:505` and `:561`).
- The error handler only catches `\Exception`, so PHP `Error`s (TypeErrors from `false` passed as a `User`) become non-JSON 500s. Many routes rely on this to "reject" anonymous callers.
- There are no DB transactions anywhere. Multi-step saves (team, language fields, network, document) are not atomic.
- N+1 hydration: every user loads its profile, tags, groups, skills and network. `GET /experiments/preview` loads all matching documents with no default limit.

### Frontend
- The **Share button never appears**: config has `submissionFeatures: ["shares"]`, but the code checks `includes('share')` (`submission.vue:46`, `submission-create.vue:163,179`).
- **Adding an event to a submission** posts to `apiUrl + null` (`submission.vue:97` passes a prop that `group-event-new` doesn't declare).
- Realtime refresh of the notification bell never fires: `activity.vue:38` and `events.vue:101` read `event.event_type`, but the store uses `eventName`.
- `admin-groups.vue:65` calls `groups.removeGroup`, which doesn't exist, so deleting a group from admin is broken.
- `profile-wizard.vue:361` uses `backend` without importing it.
- `admin-page-surveys.vue:53` checks `auth.user_type` instead of `auth.userData.user_type`.
- The `group` route guard calls `next()` twice when no groups are loaded (`routes.js:325-327`).
- `submission.js:19,22` reassigns a ref instead of `.value`, so the fetch status is not reactive.
- About 10 features still call the removed Vue 2 `this.$http` (comment upvote, reply in some paths, admin "create user", change registration type…).
- Plausible analytics never initialises (`main.js`, `domains = []`).
- Missing font files: `MyFontsWebfontsKit.css` references `webfonts/329BBA_*`, which is not in the repo.
- `idea-village.css:7` has invalid CSS (`var('--very-dark')`).

## 3. Dead code and tenant-specific hard-coding

- **Backend classes nothing uses:** `Event/Pulse`, `EveryMinuteEvent`, `UserEveryMinuteSubscription`, `Http/Cookie`, `Http/DiscourseSSO`, `CustomFields`, `Language/Language`, `LanguageFieldsDocument`, `Communicator/Event`, `Accounting/Invoices`, `HappeningMockRepository`, plus CodeIgniter / Ion Auth leftovers (`$app['ci']`, `kf_groups`, `kf_roles`).
- **Unused Composer packages:** `nategood/httpful`, `monolog/monolog`, `paragonie/csp-builder`, `flow/jsonpath`, `woutersioen/sir-trevor-php`, `fzaninotto/faker`, `asika/autolink`, `justinrainbow/json-schema`, `aws/aws-php-sns-message-validator`, and effectively `cviebrock/discourse-php` and `delight-im/cookie`.
- **Unused env vars and constants:** `platform_config`, `allow_api_access`, and the constants defined in `bootstrap.php:63-68`.
- **Frontend dead files:** `* copy.vue` (3 files), `group-org.vue` (a near-copy of `group.vue`), `ui-input-kennitala-old.vue`, `read-more.vue`, `text-fitter.vue`, `entitites-modal.vue`, `stores/counter.js`, and about 11 pages with no route (`monitor-members-page*.vue`, `form-page.vue`, `submissions-page.vue`, which imports a missing file, and others).
- **Unused npm packages:** `jwt-decode`, `vue-moment`, and the premium FullCalendar `resource-timeline`. Vue 2-era `v-tooltip@1` and `vue-closable` are still installed.
- **Hard-coded tenants:** seven platform keys in `UsersRoutes.php:349-357`, group 37 (hafnar.haus monitors), the "Hlemmur" coworking items and prices (`AccountsRoutes.php`, `SubscriptionResponse.php`), user id `"2"` as the default registration notifier (`Happening.php:82`), the S3 bucket names, and the image CDN host in `ui-img-dynamic.vue`.

## 4. Recommendations (in order)

1. **Report the security review privately** to East of Moon (see `SECURITY.md`) before any public work on the affected areas.
2. **Make installs reproducible:** commit `composer.lock` and keep `package-lock.json`. Fix the `composer.json` name (done here). Add a seed SQL (done here, under `docker/`).
3. **Add a thin safety net before refactoring.** Following `principles.md` ("test at the right places"), write HTTP-level API tests against the Docker stack for the main flows: register/login, create/publish submission, comment, group join, booking.
4. **Upgrade the runtime:** port `backend/silex/*` (the routes are thin closures) to a maintained micro-framework such as Slim 4, on PHP 8.3. Move Eloquent, Symfony components and Twig to current majors. `src/` is mostly framework-independent, so this is mechanical but broad.
5. **Clean up the frontend:** remove the Vue 2 leftovers and dead files, fix the bugs listed above, and add ESLint (`eslint-plugin-vue`) to catch the rest.
6. **Make local dev independent of AWS:** add a configurable S3 endpoint (MinIO) and SMTP sending (Mailpit) in place of the hard-wired S3/SES/SQS calls.
