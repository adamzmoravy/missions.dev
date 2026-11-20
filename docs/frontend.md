# Frontend (Vue 3): architecture and reference

Paths are relative to `frontend/` unless they start with `../`. Line numbers refer to the commit `ae208d2` (upstream `main` when this was written).

- The README inside `frontend/` is the stock Vite template. There are **no frontend tests**, no ESLint/Prettier config and no TypeScript.

---

## 1. Tooling, build and serving

### package.json
- Name `"vue3"`. Scripts: `dev` (`vite`), `build` (`vite build`), `preview`.
- Versions resolved in `package-lock.json`:
  - **Core:** vue 3.4.31, vue-router 4.4.0, pinia 2.1.7, axios 1.12.2, @unhead/vue 1.9.16, @vueuse/core and @vueuse/components 10.11.
  - **Build:** vite 7.3.1, which needs Node ^20.19 or >=22.12. @vitejs/plugin-vue 6.0.3. sass 1.77.6 (dev).
  - **UI:** bootstrap 4.6.2 (CSS only; jQuery/Popper are not installed, so Bootstrap JS does not work), @dbetka/vue-material-symbols with material-symbols 0.14.7 (pulled in transitively, but imported directly in `src/main.js:80`), portal-vue 3, v-tooltip **1.1.6** (old tether-based Vue 1/2 library), vue-closable 0.0.3 (Vue 2-era), vue-horizontal 1.0.2, vue-virtual-scroller 2.0.0-beta.8, vue-slider-component 4.1.0-beta.7, tippy.js, mini-toastr, vue-timeago3, qrcode.vue.
  - **Features:** @tiptap/* 2.5.5 (starter-kit, link, mention, youtube, suggestion), @fullcalendar/* 6.1.15 (including the **premium** `resource-timeline`), mapbox-gl 3.9.4, @studiometa/vue-mapbox-gl, @mapbox/mapbox-gl-geocoder, honeycomb-grid 3.1.8, panzoom, add-to-calendar-button (a web component), @json-editor/json-editor, kennitalajs (validates the Icelandic national ID), pusher-js 8.4.0-rc2, @plausible-analytics/tracker.
- **Installed but unused or half-used:**
  - jwt-decode: never imported.
  - vue-moment: never `app.use`d, yet `this.$moment` appears in unrouted pages.
  - `moment` is imported directly in `src/calendar.js:2` and several components, but is only a transitive dependency.

### vite.config.js
- `server.fs.allow: ['../node_modules/material-symbols', '.']`.
- `build.outDir: '../public_html'`.
- The Vue plugin treats `add-to-calendar-button` as a custom element.
- There is no `emptyOutDir`. Because outDir is outside the Vite root, Vite will not clean it, so old hashed bundles pile up in `public_html/assets`.
- The local Docker setup (`docs/local-development.md`) adds `server.proxy['/api'] → process.env.API_PROXY_TARGET`, active only when that variable is set. It is needed because the backend sends no CORS headers: there are no `Access-Control-*` headers anywhere in `../backend/silex` or `../backend/src`.

### How the frontend is served through public_html
- `vite build` writes `index.html` and `assets/` into `../public_html/`, and copies `public/` verbatim. That copy brings:
  - `public/.htaccess`: an SPA fallback that rewrites every non-file request to `index.html`.
  - `public/api/index.php`: one line, `require_once __DIR__ . '/../../backend/bootstrap.php';`. So `public_html/api/*` runs the PHP Silex app.
  - `public/api/.htaccess`: rewrites to `index.php`. Also `public/api/robots.txt` and `public/api/favicon.ico`.
  - `public/img/**`: badges, hex tiles, stage icons, reactions icons, a `haus/` folder and so on. Config JSON references these paths (for example `/img/reactions/heart.png`).
- The whole `public_html/*` folder is gitignored (`../.gitignore`). The entries `public_html/static/vite` and `public_html/generated/js/dev` are leftovers from an older pipeline; nothing writes there now.
- `../.valetphprc` holds `php@7.4`, a hint that Laravel Valet was used locally.
- The production API base is the relative `/api` (`src/main.js:58`).

### Environment variables
- `.env.example` contains only `VITE_API_URL=http://localhost:8888`.
- `src/main.js:51-54`: in `MODE === 'development'` the API URL is `VITE_API_URL`, defaulting to `http://localhost:8888`. Any other mode uses `/api`.
- So a cross-origin dev setup fails, because the backend sends no CORS headers. The Docker compose file sets `VITE_API_URL=/api` and `API_PROXY_TARGET=http://app:80`, so dev runs same-origin through the proxy.
- The backend `.env` lives at `../config.env` (see `../config.env.example`). The frontend reads its `pusher_api_key` indirectly, through `GET /platform/`.

### Build-time coupling
The frontend imports files from outside its own folder, so the sibling directories must exist when you build:
- `../translations/*.json` in `src/stores/translations.js:3-4`.
- `../config/config_item_schemas/*.json` through dynamic imports in `src/components/admin/admin-config.vue:86,93`.

---

## 2. App bootstrap

### src/main.js
1. Creates the router with `createWebHistory()`. Scroll behaviour: restore the saved position, otherwise go to the top (lines 9-24).
2. Installs Pinia (26-29) and Unhead, plus `VueHeadMixin` so components can define `head()` (32-35).
3. Sets `app.config.globalProperties.backend = backend` (38-39). Some components call `this.backend.get(...)`.
4. Installs the global mixin `src/mixins/global.js` (43-44).
5. Calls `usePlatformStore().initialize(apiUrl)` (47-75).
6. Production only: dynamically imports Plausible, but `domains = []` (67), so **Plausible never initialises**.
7. Installs plugins: material symbols, PortalVue, VTooltip, VueClosable, timeago, VueHorizontal (component), VueVirtualScroller (78-100).
8. Registers about 45 `ui-*` components globally (102-197). Also global: `entities`, `entity`, `profileImageSimple`, `profileIndexRow`, `profileIndexList`, `submissionPreview`, `stageIcon`, `adminPanelTable`, `commentTopic` (202-221), and the theme components `onboarding-theme` and `onboarding-theme-haus` (224-228).

### src/App.vue
- Imports every global CSS file (lines 22-45).
- Renders `platform-v2` only after `platformStore.platformLoaded`, meaning both config and ui are non-null (55). Otherwise it shows "Error loading {apiUrl}".
- Adds `platform-{platform_key}` to `<body>` for per-platform theming (5-16).

### src/components/platform-v2.vue (the app shell)
- `head()` sets the title, favicon and description from config (24-47).
- **Access gate** `accessible` (81-101): `config.access.site === 'public'` means open. Routes with `meta.byPassAuthentication` are always allowed. `members_only` requires a logged-in user. Otherwise the shell renders `platform-not-accessible`, which offers a login button and a register link.
- `created` (193-209):
  - `auth.login()` restores the token from localStorage.
  - Sets the language from `$route.params.lang`.
  - `translations.fetchPlatformTranslations()` makes a **second** `GET /platform/` call.
  - Creates the Pusher client with `config.pusher_key` and subscribes to the channel named after `config.platform_key`.
- `mounted` (210-231):
  - Sets the page title.
  - Adds an infinite-scroll listener that increments `ui.loadMoreOnScroll` (155-176).
  - **`?login=<token>` in the URL is accepted as a bearer token** (216-222), then stripped from the URL.
  - Starts a ping: it checks every 5 s and sends `GET /platform/ping/` at most once a minute while authenticated (132-154, 224-228).
- The `$latestEvent` watcher (112-129) fans every realtime or local event out to the submissions, groups, profiles and auth stores. On `user-login`, `user-logout` or `user-not-login` it calls `initializePlatformData()` (179-190). That loads submissions, profile previews, groups, calendar events, spaces and tags, but only if the user is authenticated or the site is public.
- A `$route` watcher resets the modal and action, and switches language when `:lang` changes (104-111).

### src/components/spa.vue
- Renders `nav-header`, a `<router-view>` wrapped in **`<keep-alive>`** (21-25) and a footer.
- The footer is hidden for bypass or hideHeader routes. Its terms/privacy links are hard-coded to `/en/policies/...` (39).
- It registers a global `router.beforeEach` that emits a `new-page-navigation` event. **After 10 minutes on a page, the next navigation does a full `window.location` reload**, presumably to pick up new deploys (111-126).
- Because of keep-alive, many pages re-run their `setup()` from `$route` watchers.

### src/components/modals-control.vue
- A global modal outlet: `<component :is="uiStore.modal">`, used for `login-modal` and `logout-modal`.
- Opens `submission-create` when `ui.newSubmission != null`, `group-post-create` when `ui.newPost != null`, and `group-event-new` when `ui.newEvent != null`.
- Always renders `group-preview-modal`.
- Any component opens these by setting the store, for example `$stores.ui.setNewSubmission({stage: 9})`.

### How platform and UI config reach the client
- `GET /platform/` (`../backend/silex/Routes.php:492-502`) returns three things:
  - `platform`: `PlatformResponse::get()` (`../backend/src/API/v1/PlatformResponse.php:16-47`). It includes site_title and site_label, mission_statement, platform_key, pusher_key, site_languages, access.site, site_registration_enabled, header_elements, groups.default, geo_provider_key, join.ongoing_groups, platform_meta_definitions, and more.
  - `ui`: `conf('platform.ui')`.
  - `translations`: database overrides, as `{lang: {key: value}}`.
- The backend loads `../config/` with `Noodlehaus\Config::load(dir)` (`../backend/src/Platform.php:167-170`). That merges every JSON and YAML file there: `ui_config.json`, `ui_config_reactions.json`, `ui_config_icon_files.json`, `public.json`, `sandbox.yaml` and the others. Admin changes made with PATCH `/platform/` and PUT `/platform/config/` are layered on top.
- On the client, the stores keep the result in `platformStore.config` and `platformStore.ui`. The global mixin exposes them as `$config.platform` and `$ui`.
- `ui` keys the frontend uses (from `../config/ui_config.json`):

| Key | What it controls |
|---|---|
| `theme` | Theme suffix used by the `$theme()` helper |
| `frontPage` | Where the root route redirects |
| `headerFeatures` | Which header blocks appear |
| `mainMenu`, `mainMenuShow` | Menu entries and which are shown |
| `adminSections` | Admin navigation |
| `stages`, `stageHandles`, `stagesIndexPages`, `ideationStages` | Submission types and where they are listed |
| `hats` | The comment "hat" colours |
| `commentReactions`, `submissionReactions` | Reaction sets |
| `iconFiles` | Menu icons |
| `submissionFeatures` | Optional submission features |
| `offerACLForSubmissionStages`, `offerHatsForSubmissionStages` | Per-stage options |
| `profileTagTypes`, `profileElements`, `profileBackground` | Profile layout |
| `onboarding` | Onboarding steps |
| `pinnedComponents.feed` | Components rendered by name on the feed (`src/pages/feed-page.vue`, `<component :is="component.name">`) |
| `terms` | Terms links |
| `showPoweredBy`, `showPlatformIntro`, `showGuideForSection`, `missionImage`, `shortcut_icon` | Misc display settings |

### i18n (custom, no vue-i18n)
- `src/stores/translations.js` imports `../translations/en.json` (482 keys) and `../translations/is.json` (464 keys) at build time (3-10). 21 keys exist in en but not in is.
- The `translations` getter merges `platformTranslations` from the `GET /platform/` `translations` field over the file defaults (44-55). Note that it **mutates `defaultTranslations` in place**.
- `trans(key)` looks in the selected language, then English, then returns the key itself. It records misses in `missingTranslations` (25-41), and admin-translations displays that list as JSON.
- Templates call `$trans('key')` from the global mixin (`src/mixins/global.js:33-35`). I counted 352 distinct literal keys. Many keys are English sentences.
- The language comes from the first URL segment (`/:lang/...`). Switching language in the header rewrites the path (`src/components/header/nav-header.vue:286-299`).
- `<html lang="en">` is hard-coded in `index.html` and is never updated.
- Admins edit translations with `PUT /platform/translations/{lang}/` (`src/components/admin/admin-translations.vue:88`). The backend stores them with `strip_tags($value, '<strong><p><a><h2><h3><h4>')` (`../backend/src/Setting/SettingRepository.php:52`), and several keys are rendered with `v-html`.

### API client: src/backend.js
- A thin axios wrapper with methods `get(resource, token=null, params={}, silentFail=true)`, `post`, `put`, `patch`, `delete` and `postFile` (multipart) (53-80). The URL is `platform.apiUrl + resource`.
- **Auth:** `getAuthHeader()` reads `localStorage.token`, which is JSON `{string, timestamp, set}`, and sends `Authorization: Bearer <string>` (84-111). There are no cookies and no `withCredentials`.
- **Error handling:**
  - Mutating calls end with `.catch(catchErrors)`. That shows a mini-toastr error with `error.data.message`. On `status === 'invalid_token'` it clears localStorage and does a hard redirect to `/` (21-41).
  - Because the catch swallows the error, **callers' `.then(response => response.data...)` receive `undefined` on failure** and throw a TypeError. You can see this in components that pair `.then` with `.catch` around backend calls, for example `src/components/resources/resource-details.vue:427-445`.
  - `get` with the default `silentFail=true` does not catch: errors reject and show no toast.
- Line 13 declares an `authHeader` that is never used.

### Auth and session
- Login: `login-modal.vue` and `sign-in.vue` first call `GET /users/identifier/?email=` to see whether the account exists. Then:
  - an existing user enters a password, which sends `POST /users/tokens/`;
  - a new user registers with `POST /users/` (or with an access code on `/access/:token/`).
- `auth.setToken()` stores the token object in localStorage and calls `GET /users/me/profile`. It then emits a local `user-login` event, which reloads platform data (`src/stores/auth.js:45-64`).
- Logout calls `removeToken()` and emits `user-logout` (24-28).
- The token is **not** a JWT the client inspects (jwt-decode is unused).
- Roles: the only role checked is `userData.user_type == 'admin'`. All admin gating in the UI is cosmetic (menus and tabs); the backend enforces it with `$adminOnly`.

### Realtime: src/stores/events.js
- Pusher, cluster `eu`. `subscribeToChannel(platform_key)` binds a global handler that pushes `{eventName, data, time}` into a ring buffer capped at 100 (12-42).
- The backend triggers events on that public channel (`../backend/src/PushToFront.php`).
- Event names the frontend handles:

| Event | Handled in |
|---|---|
| `experiment_updated` | `stores/submissions.js:103`, `submission.vue:313` |
| `comment_happening`, `calendar_event_updated` | `stores/groups.js:30-35` |
| `user_updated` | `stores/auth.js:18`, `stores/profiles.js:31` |
| `happening_updated` | `group.vue:441` |
| `comment-changed` | `comment-topic.vue:398`, `group-comment-feed.vue:46` |

- Local synthetic events go through the same bus: `user-login`, `user-logout`, `user-not-login`, `new-page-navigation`.
- Components listen through `$latestEvent`, which is the last element of the buffer.

---

## 3. Routing (src/routes.js)

There are no global auth guards. Access is decided in `platform-v2.vue`'s `accessible`, using `meta.byPassAuthentication`. `meta.hideHeader`, and also bypass, hides the header (`src/components/header/nav-header.vue:243-245`) and the footer (`src/components/spa.vue:31`).

| Path | Alias | Name | Component | Meta / guard |
|---|---|---|---|---|
| `/:lang/policies/:policy/` | – | policies | `pages/policies-page.vue` (placeholder text "Insert policies here.") | byPassAuthentication |
| `/:lang/platform-new/` | – | platform-new | `pages/platform-new-page.vue` | byPass, hideHeader |
| `/:lang/submissions-slideshow/` | – | submissions-slideshow | `pages/submissions-slideshow-page.vue` | hideHeader |
| `/:lang/landing/` | – | landing | `pages/welcome-landing-page.vue` (template is empty) | – |
| `/:lang/password-reset/` | – | password-reset | `pages/password-reset-page.vue` | byPass, `changePassword:false` |
| `/:lang/password-change/:token/` | – | password-change | `pages/password-reset-page.vue` | byPass, `changePassword:true` |
| `/:lang/talk/` | – | talk | `pages/group-page.vue` (shows `config.groups.default`) | – |
| `/:lang/events/` | – | events | `pages/events-page.vue` | – |
| `/:lang/monitor/` | – | monitor | `pages/monitor-page.vue` (kiosk display) | byPass, hideHeader |
| `/:lang/access/:token/` | – | access | `pages/access-page.vue` (secret invite link) | byPass |
| `/:lang/mesh/:board/` | `/:lang/canvas/:board/` | mesh | `pages/mesh-page.vue` | – |
| `/:lang/join/` | – | join | `pages/join-page.vue` | – |
| `/:lang/admin/:section/` | – | admin | `pages/admin-page.vue` | none client-side |
| `/:lang/profile/:profileId/` | – | profile | `pages/profile-page.vue` | – |
| `/:lang/resource/:resourceId/` | – | resource | `pages/resource-page.vue` | – |
| `/:lang/onboarding/` | `/:lang/register/` | onboarding | `pages/onboarding-page.vue` | byPass |
| `/:pathMatch(.*)*` | – | NotFound | `pages/not-found-page.vue` | `beforeEnter` (lines 192-205): if the first segment is not 2 characters long, redirects to `ui.lang + to.path`. That value is `'en'` plus the path with **no leading slash**, so it is fragile. |
| `/:lang([a-zA-Z]{2})/book/` | – | book | `pages/bookings-page.vue` | – |
| `/:lang([a-zA-Z]{2})/` | `/:lang/front` | fronter | `pages/front-page.vue`: redirects to `/{lang}/{ui.frontPage}/` if logged in, otherwise `/{lang}/welcome/` | – |
| `/:lang/admin/surveys/:surveyName/` | – | admin-surveys | `pages/admin-page-surveys.vue` | – |
| `/:lang/feed/` | `/:lang/home/` | feed | `pages/feed-page.vue` | – |
| `/:lang/issues/` | – | issues | `pages/issues-page.vue` (stage 5) | – |
| `/:lang/qa/` | – | qa | `pages/qa-page.vue` (stage 10 and stage 5) | – |
| `/:lang/portfolio/` | – | portfolio | `pages/portfolio-page.vue` (stage 9) | – |
| `/:lang/guide` | – | guide | `pages/guide-page.vue` → `components/guide.vue` (stage 6) | – |
| `/:lang/organisations` | – | organisations | `pages/organisations-page.vue` → `components/organisations.vue` (stage 8 and a map) | – |
| `/:lang/submission/:id/` | `/:lang/experiment/:id/` | submission | `pages/submission-page.vue` | – |
| `/:lang/members/` | `/:lang/people/` | members | `pages/members-page.vue` | – |
| `/:lang/welcome/` | – | **front** | `pages/welcome-page.vue` | – |
| `/:lang/ideation/` | – | ideation | `pages/ideation-page.vue` | – |
| `/:lang/submissions/` | – | submissions | `pages/ideation-page.vue` | – |
| `/:lang/group/:groupId/` | `/:lang/happening/:groupId/` | group | `pages/group-page.vue` | `beforeEnter` (319-333) calls `groups.previewIfRequired` for private groups |

Visiting `/` hits the NotFound guard, which redirects to `en/`, then fronter, then welcome or the configured front page. `welcome-page.vue` itself redirects authenticated users to `/feed/`.

Routing bugs to note:
- The `group` `beforeEnter` calls `next()` twice when `groups.length === 0` (325-327).
- `previewIfRequired` dereferences `getGroupPreview(id).config` with no undefined check (`src/stores/groups.js:52-64`).
- In the unused `pages` map, `'front': FrontPage.AdminPageSurveys` evaluates to `undefined` (routes.js:48).
- `admin-page.vue:150` assigns to a computed property (`currentView`).

**Page files with no route:** `events-poster-page.vue` (hard-coded `/happenings/37/...`), `form-page.vue` (Icelandic application form, uses an unregistered `<survey>`), `home-page.vue`, `talk-page.vue`, `sign-in-page.vue`, `skills-cloud-page.vue`, `privacy-policy-page.vue`, `onboarding-mentor-page.vue`, `submissions-page.vue` (imports the non-existent `../components/submission-wizard.vue` at line 78), `monitor-members-page.vue` and `monitor-members-page-v2024.vue` (both specific to the "haus" platform, use `$moment` and the non-existent `<profile-image>`/`<img-dynamic>`).

---

## 4. State (Pinia, options-style except `entities`)

Several stores expose a "preview list plus getters" pattern. Their getters take `(sorters[], filters[])` and return generic **entities** of shape `{title, link, icon, type, data, id}`. Search, mentions and side lists consume these.

| Store | State | Actions → API | Notable getters |
|---|---|---|---|
| `platform` (`stores/platform.js`) | `config, ui, apiUrl, translations, platformTranslations, pusherKey, platformKey, loadingError` | `initialize(apiUrl)` and `fetchConfig()` → GET `/platform/`. On an `invalid_token` error it calls `auth.logout()`. | `platformLoaded` |
| `auth` | `token {string,timestamp,set}`, `userData` | `login()` (from localStorage), `register(credentials)` → POST `/users/`, `setToken()`, `setUserData()` → GET `/users/me/profile` (token passed explicitly), `refreshUserData`, `removeToken`, `logout`, `handleEvent(user_updated)` | `isAuthenticated`. `persist: true` (line 83) does nothing because no persistence plugin is installed. |
| `ui` | `modal, lang('en'), pageMode, action, newSubmission, newPost, newEvent, platformName, loadMoreOnScroll` | `setModal/closeModal/login`, `setNewSubmission/Post/Event`, `setAction`, `setPageTitle` (vueuse `useTitle`), `notifySuccess` (toast) | `showPrivateScreens` |
| `translations` | `defaultTranslations, platformTranslations, selectedLanguage, missingTranslations` | `setLanguage`, `fetchPlatformTranslations` → GET `/platform/` | `trans`, `languages`, `translations` |
| `events` | `events[] (≤100), pusher` | `initiatePusher(key)`, `subscribeToChannel(name)`, `addEvent` | `getEventsByName`, `getLatestEventByName`, `getLatestEvent` |
| `submissions` | `submissions[]` (previews) | `fetchSubmissions(lang)` → GET `/experiments/preview/{lang}`, `handleEvent(experiment_updated)` | `getEntities`, `getSortedSubmissions`, `getFilteredSubmissions`. Filters: owner, stages, ids, search, address. Sorters: title, relevance, created, updated, owner, stage. Also `getLocations`, `getSubmissionPreview(ById/UUID)`. |
| `profiles` | `profiles[]` (full), `profilePreviews[]`, `currentUser` | `fetchProfilePreviews` → GET `/users/preview/`. `fetchProfile(id)` → GET `/users/{id}/profile`. `handleEvent(user_updated)`. | `getEntities`. Sorters: profile_pic, random, date_joined. Filters: search, name, ids. `getProfilesCountries`, `getProfilePreviewsByIds`, `getProfilePreviewById` and `getProfileById`, which **fire fetches from inside getters**. |
| `groups` ("happenings" in the API) | `groups[], groupPreview, groupComments[], calendarEvents[]` | `createGroup` → POST `/happenings/`. `updateGroup` → POST `/happenings/{id}/config/`. `fetchGroupPreviews` → GET `/happenings/`. `fetchGroupComments` and `fetchSingleComment` → GET `/happenings/topics/` (`?comment_id=`). `fetchCalendarEvents` → GET `/calendar/events/`. Also `isCurrentUserRegistered` and `previewIfRequired`. | `getEntities`, `getEntitiesByMember`, `getGroupPreview(ByUUID)`. Filters: member, search. Sorters: name, registrations_count, created. **There is no `removeGroup`, although `admin-groups.vue:65` calls it.** |
| `spaces` | `spaces[]` (mesh boards) | `createNewSpace` → POST `/experiments/spaces/`. `removeSpace` → DELETE `/experiments/spaces/{id}/`. `fetchSpacesPreviews` → GET `/experiments/spaces/`. | `getEntities` (link `/mesh/{key}/`) |
| `tags` | `tags[]` | `fetchTags(lang)` → GET `/tags/{lang}`. `createTag` → POST. `updateTag` → PUT `/tags/{id}/{lang}`. `removeTag` → DELETE `/tags/{id}/`. | `getTagById(s)`, `getTagsByTagsWithType`, `getTypesAndTags` (logs to the console), `getTagsByType(s)` |
| `entities` (setup store) | computed concatenation of profiles, groups, submissions and spaces entities | – | `getFilteredEntities` (search/type/id), `getSortedEntities` (title/random). Used by header search and TipTap @mentions. |
| `counter` | Pinia sample | – | unused |

Composables (not stores):
- `src/components/submissions/submission.js` (`useSubmission`): fetch, participate (like/updates/help), team, related submissions, image collection. Bug: `status = 'fetching'` at lines 19 and 22 reassigns the ref variable instead of `.value`, so its computeds do not react properly.
- `src/components/groups/group.js` (`useGroup`): fetch and join.
- `src/calendar.js` (`useCalendar`): event formatting, started/ended flags, a 60 s clock.

---

## 5. Product features and component map

### Domain vocabulary
- **Submissions** are "experiments" in the API. Each one has a `stage` that is really a **type**. From `ui.stageHandles`:

| Stage | Type | Where it is listed |
|---|---|---|
| 0 | challenge | Ideation page |
| 1 | idea | Ideation page |
| 2 | project | Ideation page |
| 3 | insight | Ideation page |
| 4 | topic | – |
| 5 | issue | Issues page, Q&A page |
| 6 | guide | Guide page |
| 7 | policy | – |
| 8 | organisation | Organisations page |
| 9 | portfolio_item | Portfolio page, slideshow |
| 10 | question | Q&A page |

- **Groups** are "happenings".
- **Spaces** are boards on the hex canvas.
- **Resources** are bookable rooms.
- **Accounts** cover membership and invoicing.

### What a user can do
1. **Browse and join:**
   - The welcome page shows the mission statement, a carousel of top submissions by relevance, spaces and groups (`pages/welcome-page.vue`).
   - Registration and onboarding go through `/onboarding/` (`components/profiles/onboarding.vue`, which picks `onboarding-theme[-<ui.theme>]` through `$theme()`). Steps: sign-in or register, then a platform intro, then for admins an offer of the **platform wizard**, then the **profile wizard**, then "what next".
   - A secret invite link `/access/:code/` registers new users with `access_code`.
   - Password reset and password change go through `password-reset.vue` → `/users/password_resets/` and `/users/password_changes/`.
2. **Feed** (`pages/feed-page.vue`):
   - Left column: own profile, my groups, random members.
   - Centre: buttons for "new post", "Ideation" and "Event", then an interleaved timeline. It merges group comments (`group-comment-feed`) with recently updated submissions (`submission-feed`, which embeds a comment thread). The timeline extends on scroll.
   - Right column: spaces, plus recently updated ideation, guides and issues.
   - There is also a hidden "Secret wish" easter-egg modal.
3. **Submissions** (`components/submissions/*`):
   - **Create** (`submission-create.vue`, opened from `ui.newSubmission`):
     1. Pick a type from `stage-icon` entries.
     2. Enter a title and short description. Visibility (public/private) is offered for the stages in `offerACLForSubmissionStages`.
     3. POST `/experiments/`, then PATCH `/experiments/{id}/language/{lang}/`.
     4. A steps modal follows: related submissions, tags, team, image.
     5. Publish with POST `.../publish/`. Optionally share it by email.
     - `submission-wizard.vue` is an alternative simpler flow, imported by ideation-page.
   - **Detail page** (`submission.vue`):
     - Title, top image, short description, tags.
     - **Participation reactions** (`submission-participate.vue`), configured by `submissionReactions`: like ("I care about this"), updates ("I'd like updates"), help.
     - For editors: edit (`submission-edit.vue` with location, translations, image, delete), Share by email to selected profiles (`submission-share.vue`), Resolve/Reopen status, document attachments (`attachments.vue`, upload and signed download via `file_request`), photo stream (`image-stream.vue`), submission events.
     - A "Further info" document (`submission-document-row.vue`, rendered HTML; the Quill editor path is dead).
     - The comment thread, with "hats" offered for stages in `offerHatsForSubmissionStages`.
     - A sidebar of related submissions and people/team (`submission-details-people.vue`).
   - **Listings:** ideation (stages 3, 0, 1, 2 with a stage filter from `ui-stage-process-explainer`), issues, qa, portfolio, guide, organisations (with `submissions-map.vue` on Mapbox clusters).
   - **Join a submission's team:** `submission-join.vue` POSTs `/experiments/{id}/team/me/`. The profile wizard uses it for organisations (stage 8).
   - **Previews:** `submission-preview`, `submission-preview-feed` (includes a QR code), `submission-preview-modal`.
   - **Slideshow:** `/submissions-slideshow/` cycles portfolio items every 6 s (kiosk use).
4. **Comments and discussions** (`components/comments/*`, `components/editor/*`):
   - `comment-topic.vue` is a generic thread bound to `fetchCommentsUrl`, `postCommentUrl` and `postReplyUrl`. It is used for `/experiments/{id}/topic/` and `/happenings/{id}/topic/`.
   - Posts are written in **TipTap** and stored as JSON (`format: 'tiptap-json'`). The editor (`comment-editor.vue`) has StarterKit, Link, YouTube and an @mention of any entity (`tiptap-suggestions.js` uses the entities store; `tiptap-mention-entity-extension.js` and `tiptap-entity-mention.vue` render it).
   - Posts can carry images, attached submissions and an email announcement. The email can include a subject and a calendar invitation (`calendar-event-picker.vue`).
   - **Six Thinking Hats:** a comment can be tagged black (critical), white (information), red (feelings), green (new ideas), blue (planning) or yellow (positive). See `comment-topic.vue:215` and `hat.vue`.
   - Replies (`comment-reply.vue`), comment **reactions** (`reactions.vue`: love, funny, letsDoIt, solidarity, through POST/DELETE `/comments/{id}/reaction/`), delete, and **convert a comment into a submission** (`comment-convert-modal.vue` → POST `/experiments/from_comment/`).
   - Threads auto-refresh on the `comment-changed` Pusher event.
5. **Groups** (`components/groups/*`), served by `group-page` at `/group/:id/` and `/talk/`:
   - `group.vue` has tabs: intro (custom per-language text, `group-intro.vue` → PATCH `custom_language`), announcements (photo stream, events, comment thread), schedule, guide, participants (admins can register or unregister users), admin (`group-admin.vue`: registrations list, plus account and custom invoice creation through `admin-accounts-new-items` and `admin-accounts-custom-invoice`), and config (`group-config.vue`).
   - Access is `public` or `private`. Joining a private group opens `group-preview-modal.vue`, which shows survey questions (`ui-questions`) and joins with `registration_info`.
   - Also: `group-post-create.vue` (new post to a chosen group), `group-event-new.vue` (new calendar event), `group-invitation.vue` (join page cards for `config.join.ongoing_groups`), and `group-preview.vue` (cards).
6. **Events and calendar:**
   - `/events/` lists all calendars (platform, groups, submissions) with a day picker (`calendar-pick-day.vue` on FullCalendar dayGrid) and an event detail modal (`calendar-event-details.vue`, which has an add-to-calendar-button and delete). `group-events-show.vue` wraps `useCalendar`.
   - `/join/` lists **sessions**: activity events with capacity (`session-signup.vue`). Users attend, cancel or "+1" a guest. Creators can duplicate or remove sessions; `session-event-new.vue` adds them.
   - `/monitor/` is a public, headerless kiosk view of upcoming events. It refreshes every 60 s and reloads the page every hour.
7. **Profiles** (`components/profiles/*`):
   - `profile-of-user.vue` shows the photo, name, description, skills, challenge/idea/project counts, typed tags, links, contact info (visible according to backend rules) and the user's submissions. The owner edits inline through `profile-*-edit` components.
   - The profile wizard (`profile-wizard.vue`) covers picture, description, skills, tags per type (degrees, roles, populations, environments, interests, offers, languages), ORCID and other meta (`profile-meta-editor`), location (Mapbox geocoder), contact info and links (with link-preview metadata from `/page/metadata/`), then joining organisations.
8. **Members** (`pages/members-page.vue`):
   - Group carousel plus tabs: a user grid with search and sort, a **map** (`profile-map.vue`, Mapbox clusters, token taken from `config.geo_provider_key`), and a **finder** (`profile-finder.vue`, filtering by tags, country and organisation membership).
9. **Search:** the header search (`nav-header-search.vue`) runs entirely on the client over the entities store, with keyboard navigation. Recent searches are kept in `localStorage.recentSearches`.
10. **Notifications:** the header bell (`nav-header-notifications.vue`) shows an unseen count (`activity.vue` → `/events/since/my_last_fetched/`) and a panel (`events/events.vue` → GET `/events/`, then POST `/events/last_activity_fetch/`). It renders `events-comment`, `events-comment-happening`, `events-new-experiment` and `events-new-user`.
11. **Bookings:** `/book/` lists rooms (`resource-index.vue` → `/resources/type/{type}/`). `/resource/:id/` (`resource-details.vue`) shows the description, a FullCalendar timeGrid (`resource-calendar.vue`) and booking. Booking takes a date, time, length and reason. Priced rooms (shown in kr/hour) require a **kennitala** validated with kennitalajs; it is sent as `accounting_id`. Users can also remove their bookings.
12. **Hex canvas / mesh:** `/mesh/:board/` (`canvases/mesh-container.vue`, `experiment-mesh.vue`) is a pan- and zoomable honeycomb grid (honeycomb-grid, panzoom, vue-slider zoom). Submissions are tiles. In edit mode you double-click to place or move a tile, which saves through PATCH `/experiments/{id}/settings/` with `{settings:{show_in_placement:{[board]: {x,y}}}}` (lines 461-470). `space-preview.vue` shows mini-boards.
13. **Platform creation:** `/platform-new/` (`platform-create.vue`) POSTs `/platform/` with platform_key and admin credentials. It is meant for creating test platforms.

### Admin (`pages/admin-page.vue` with `components/admin/*`)
- Sections come from `ui.adminSections` (configurable; schema in `../config/config_item_schemas/ui.adminSections.json`). The header shows an admin menu when `user_type == 'admin'`.

| Section | Component | What it does |
|---|---|---|
| wizard | `admin-platform-wizard.vue` | Steps: name, label and mission; logo upload; access public/private plus a secret access link toggle. GET `/platform/admin/`, PATCH `/platform/`. |
| platform / admin | `admin-platform.vue` | Raw platform settings and secret link copy. Mixes a legacy `$http.patch` (line 206) with `backend.patch`. |
| users | `admin-users.vue` | Table of users, delete user. Create user uses a broken `$http`. |
| submissions | `admin-submissions.vue` | GET `/experiments/admin/en`, delete. |
| tags | `admin-tags.vue` | CRUD through `admin-panel-table`. |
| groups | `admin-groups.vue` | Create and update groups. Remove is broken. |
| spaces | `admin-spaces.vue` | Create and remove boards. |
| config | `admin-config.vue`, `admin-config-item.vue` | Schema-validated editing of select config keys with `ui-json-editor` (@json-editor), through GET/PUT `/platform/config/`. The raw `<v-jsoneditor>` it also uses is unregistered. |
| translations | `admin-translations.vue` | Edit DB translation overrides and view missing keys. |
| stats | `admin-stats.vue` | Daily active users from pings. |
| accounts | `admin-accounts.vue` and children | Membership accounts and invoicing; see below. |
| resources | `admin-resources.vue` | Room CRUD. |

- The accounts section has tabs:
  - Administer: an `admin-panel-table` of accounts, CSV import (POST `/accounts/import_accounts/`), new coworker/account forms that use kennitala inputs, custom invoices.
  - Monthly invoicing (`admin-accounts-invoicing.vue`): previews and creates invoices per month. The backend side uses "payday", per `config.env`.
  - Accounts overview and invoices overview.
  - Subscriptions per account, through `admin-panel-table-field-subscriptions.vue` mounted into the table via portal-vue.
- `admin-panel-table.vue` is the generic editable table that all of these use.
- Survey results can be viewed at `/admin/surveys/:surveyName/` (GET `/survey_results/{name}/`). There is no admin check on the client.

### Header and layout (`components/header/*`)
- `nav-header.vue`: platform identifier, search, main menu (`header-menu-item` built from `ui.mainMenu` and `mainMenuShow`, with icons from `ui.iconFiles`), language switcher, notifications, admin menu, login/register or profile menu, and a "more" dropdown (`nav-header-more-menu`, which uses vue-closable).
- `nav-side-bar.vue` is commented out in `spa.vue`.

### ui-* kit (globally registered)
- **Buttons and links:** `ui-button`, `ui-button-theme(-haus)`, `ui-a`.
- **Containers:** `ui-modal` (teleported to `body`, Esc closes), `ui-steps` and `ui-steps-modal` (wizards emitting `finished`/`stage-changed`), `ui-tabs`, `ui-panel`, `ui-page-layout/header/body`, `ui-cards-container`.
- **Inputs:** `ui-input-text/-email/-email-list/-url/-phone-number` (539 lines), `ui-input-kennitala`, `ui-input-list`, `ui-textarea`.
- **Images:** `ui-image-upload` (POST `/files/images/`), `ui-img-dynamic` (resizes through the Cloudinary-style fetch CDN `d34m5sods6fnic.cloudfront.net`, `ui-img-dynamic.vue:65`), `ui-image-lightbox`, `ui-image-adjust`.
- **Rich content and data:** `ui-html` (TipTap HTML editor), `ui-json-editor`, `ui-custom-language` (per-language custom text editor), `ui-language-field`, `ui-questions` (survey), `ui-tags` (tag picker with custom tag creation), `ui-location-selector` (Mapbox geocoder), `ui-timespan-selector`.
- **Display:** `ui-stage-process-explainer` (stage icons and filter), `ui-read-more/ui-show-more`, `ui-text-fitter`, `ui-logos`, `ui-document-frame`, `ui-country-flag`, `ui-context-menu`, `ui-expanded-menu(-item)`, `ui-icon-control`.
- **Entities:** `entities`/`entity` render generic entity lists (vue-virtual-scroller `RecycleScroller`), with search and single or multiple select. Used for "pick submissions", "pick team" and side lists.

---

## 6. Mixins, utilities and styling

### src/mixins/global.js
The only mixin, applied app-wide. It provides:
- methods `$truncate`, `$trans`, and `$theme(name)`, which returns `name-theme-<ui.theme>` if such a component is registered, otherwise `name-theme`;
- computed `$stores` (all stores), `$config`, `$ui`, `$currentUser`, `$lang`, `$isUserAuthenticated` and `isUserAuthenticated`, `$latestEvent`, `$loadMoreOnScroll`, `$breakpoints` (vueuse; tablet 640, laptop 1024, desktop 1280);
- a `mounted: false` data flag.

Code style is mostly the Options API. There are a few `<script setup>` blocks and a mix of both in one file (for example `profile-wizard.vue`).

### Styling
- Bootstrap 4 CSS (`App.vue:22`), plus 21 global CSS files in `src/assets/css` (about 3,800 lines; the largest are `idea-village.css` with 803, `video-conferencing.css` with 666, `text.css` and `layout.css`).
- `platform-themes.scss`: per-platform overrides keyed by the body class `.platform-<key>` (haus, fellowtravellers, apollo, astroaccel, iriss, circularlibrary).
- Many components also have their own scoped styles.
- **Fonts:**
  - `fonts-ggx7onp.css` pulls Adobe Typekit (Soleil, Permanent Marker) from `use.typekit.net`, a licensed kit ID.
  - `MyFontsWebfontsKit.css` references `webfonts/329BBA_*` (Avenir) **files that do not exist in the repo**, so those requests will 404.
  - Material Symbols is loaded through the plugin.
- `idea-village.css:7` has invalid CSS: `var('--very-dark')` (the quotes break it).
- `video-conferencing.css` is leftover. The only video reference in the code is in the unused `group-org.vue:717`, even though the backend still has `video_chat` routes.

---

## 7. Quality notes

### Dead and duplicate files
Files nothing imports:
- `components/ui-page-body copy.vue`, `ui-page-header copy.vue`, `ui-page-layout copy.vue`, `components/img/loading-dots copy.svg`.
- `ui-input-kennitala-old.vue`, `read-more.vue` (duplicate of ui-read-more), `text-fitter.vue` (duplicate of ui-text-fitter), `entitites-modal.vue` (typo in the name), `ui-intro.vue`, `ui-hexagon.vue`, `ui-button-theme-og.vue`.
- `groups/group-org.vue`: a 734-line near-copy of `group.vue` (about 490 diff lines) with legacy `$http`.
- `profiles/profile-index.vue`, `authentication/sign-out.vue` (imports the non-existent `../mixins/helpers.js` and `auth.js`), `header/nav-side-bar.vue`.
- The unrouted pages listed in section 3. The pair `monitor-members-page.vue` and `-v2024.vue` is a "-v2024" variant.
- Other variant pairs: `platform-v2.vue` (no v1 left), `platform-intro.vue` vs `platform-intro-v2.vue` (the haus theme uses v2), `onboarding-theme` vs `onboarding-theme-haus`.

Also present:
- Unused stores: `stores/counter.js`.
- Stray text before `<template>` and a `flase` typo in the close handler of `submission-document-row.vue`.
- A dead import of `filterHash` from `@fullcalendar/core/internal` in `admin-invoices-overview.vue:87`.
- An unused premium `resourceTimelinePlugin` import in `resource-calendar.vue:5`.

### Leftover Vue 2 code (broken at runtime under Vue 3)
- **`this.$http` (vue-resource), together with the undefined `this.apiUrl` and `getAuthorizationHeaderMixin`:**
  - `comment-topic.vue:586` (upvote), `:638` (postReply), `:701` (plaintext post);
  - `admin-users.vue:174` (create user), `admin-platform.vue:206`;
  - `group.vue:560` (change registration type);
  - `group-register.vue:206,228`, `group-org.vue:532-631`, `profile-contact.vue:70` (that component is commented out).
- **`$root.eventBus`** still used in templates and handlers: `comment-single.vue:158`, `group.vue:95`, `experiment-mesh.vue:634-641`, `sign-up.vue:95`, `sign-out.vue:39`, `nav-header.vue:296,325` (in the catch path), `members-page.vue:408`.
- **`inline-template`**, removed in Vue 3: bookings, issues, portfolio, members and others (it is ignored, so the custom tab markup is dead).
- `$validator` (vee-validate) in `sign-up.vue`.
- Unregistered components: `<v-jsoneditor>` (`group-config.vue:116`, `admin-config.vue:23`), `<textarea-autosize>`, `<quill-editor>`, `<survey>`.
- `beforeDestroy` hooks, which never run in Vue 3.
- v-tooltip 1.x and vue-closable are Vue 2-era directives. Tooltips may not work, but I did not run the app to confirm.

### Logic bugs worth listing
- `ui.submissionFeatures` is `["shares"]` in config, but the code checks `includes('share')` (`submission.vue:46`, `submission-create.vue:163,179`). **The Share button never appears** with the default config.
- `submission.vue:97` passes `:post-event-url` to `group-event-new`, but that component has no such prop. `postEventUrl` is a computed that needs a group ID (`group-event-new.vue:74-79`), so **adding an event to a submission posts to `apiUrl + null`**.
- `events-page.vue:85` and `admin-page.vue:150` assign to computed properties.
- `activity.vue:38` and `events.vue:101` test `event.event_type`, but store events carry `eventName`. Their realtime refresh never fires.
- `admin-accounts-new-items.vue:164`: `backend.get('/accounts/', this.account)` passes an object as the **token** argument.
- `admin-groups.vue:65` calls the non-existent `groups.removeGroup`.
- `profile-wizard.vue:361` uses `backend` without importing it.
- `admin-page-surveys.vue:53` checks `$stores.auth.user_type`; it should be `userData.user_type`.
- The `encodeURIComponent` email is then encoded again by axios params (`login-modal.vue:171`, `sign-in.vue:83`).
- `nav-header-search.vue:147` adds a window keydown listener that is never removed. `options.components` is declared twice in that file.
- `group.vue:96` links to the non-existent route `/happening_apply/...`.

### Security
The security notes from this review are kept out of the public repository; see `SECURITY.md` for how to report issues.

### Accessibility
- About 56 `<a @click>` elements without `href` and 33 clickable `<div>`s.
- `ui-a` renders `<a :href="null">` (`ui-a.vue:49`): no keyboard focus, no middle-click, no link semantics.
- About 6 of 104 `<img>` tags have `alt`.
- Only 3 `aria-*` attributes and 8 `role=` attributes in the whole app.
- `ui-modal` has no `role="dialog"`/`aria-modal`, and its close control is a `<div>` holding an `<img>` with no alt.
- `<html lang>` never changes.
- The admin navbar toggler relies on Bootstrap JS, which is not loaded.

### Other code-quality notes
- About 80 `console.log` calls, 22 `alert()` and 13 `confirm()`.
- `GET /platform/` is called twice at startup (platform store and translations store).
- Getters trigger fetches (profiles store).
- Long-running `setInterval`s are never cleared (monitor, skills cloud, slideshow, `calendar.js`).
- `keep-alive` with `$route` watchers is used as the refresh mechanism.
- TODOs: only `password-reset.vue:100` and `admin-users.vue:171`.
- **Tests:** none in the frontend. The backend has `../backend/silex/AppTests.php`, and the root README says tests "need to be reworked".

---

## 8. Every API endpoint the frontend calls

All calls go through `src/backend.js`, relative to `/api` in production. Each section below lists **method path** followed by the caller(s). These marks are used throughout:
- † the caller uses the broken `$http` path
- ‡ the caller lives only in an unreferenced or unrouted file
- (via prop) the URL is passed in as a prop, and the component named after "→" makes the actual request

Every path below matches a route in `../backend/silex/*.php`.

### Platform
- GET `/platform/`: `stores/platform.js:28,39`, `stores/translations.js:65`
- PATCH `/platform/`: `admin/admin-platform.vue:221` (also :206†), `admin/admin-platform-wizard.vue:188`, `profiles/profile-wizard.vue:361` (backend not imported)
- POST `/platform/`: `platform-create.vue:101`
- GET `/platform/admin/`: `admin-platform.vue:199`, `admin-platform-wizard.vue:181`, `admin-accounts.vue:188`
- GET `/platform/ping/`: `platform-v2.vue:139`
- GET `/platform/config/`: `admin-config.vue:78`
- PUT `/platform/config/`: `admin-config-item.vue:49`
- PUT `/platform/translations/{lang}/`: `admin-translations.vue:88`
- GET `/platform/stats/{date}/`: `admin-stats.vue:101`
- GET `/stats/{scope}/`: `platform-stats.vue:35` (welcome-landing only)

### Users, auth and profile
- GET `/users/identifier/?email=`: `authentication/login-modal.vue:173`, `sign-in.vue:84`
- POST `/users/tokens/`: `login-modal.vue:184`, `sign-in.vue:92`
- POST `/users/`: `stores/auth.js:41`, `sign-in-code.vue:70`, `admin-users.vue:174`†
- GET `/users/{id|me}/profile`: `stores/auth.js:58`, `stores/profiles.js:46`, `profiles/profile-of-user.vue:351`
- GET `/users/preview/`: `stores/profiles.js:37`, `admin-users.vue:160`
- GET `/all_platforms/users/preview/`: `pages/welcome-landing-page.vue:76`, `profiles/profile-index.vue:291`‡
- DELETE `/users/{id}/`: `admin-users.vue:166`
- POST `/users/password_resets/`: `password-reset.vue:120`
- POST `/users/password_changes/`: `password-reset.vue:141`
- PATCH `/users/me/profile`: `profile-description-edit.vue:36`, `profile-meta-editor.vue:37`, `profile-editor.vue:59`
- PATCH `/users/me/profile/geographic_location`: `profile-location-edit.vue:75,86`
- PATCH `/users/me/profile/image_collection`: `profile-image-upload.vue:59,80`
- POST `/users/me/profile/contact_information`: `profile-contact-edit.vue:77`
- GET `/users/{id}/profile/contact_information`: `profile-contact-edit.vue:85`, `profile-contact-information.vue:228,236`
- POST `/users/me/links/`: `profile-links-edit.vue:194`
- GET `/page/metadata/?url=`: `profile-links-edit.vue:250`
- POST `/users/me/skills/`: `profile-skills-edit.vue:91`
- GET `/users/skills/`: `pages/skills-cloud-page.vue:90`‡
- POST `/custom_tags/{lang}`: `profile-tags-edit.vue:49`
- POST `/users/me/tags/{tagId}/`: `profile-tags-edit.vue:63`
- DELETE `/users/me/tags/{tagId}/{type}/`: `profile-tags-edit.vue:75`
- POST `/users/{id}/contact/`: `profile-contact.vue:70`† (component commented out)

### Files
- POST `/files/images/` (multipart): `ui-image-upload.vue:67`, `profile-image-upload.vue:72`

### Experiments (submissions)
- **Core:**
  - GET `/experiments/preview/{lang}`: `stores/submissions.js:109`
  - GET `/experiments/{id}/{lang}`: `submissions/submission.js:20`
  - POST `/experiments/`: `submission-create.vue:433`, `submission-wizard.vue:127`
  - DELETE `/experiments/{id}/`: `submission-edit.vue:76`, `admin-submissions.vue:279`
  - GET `/experiments/admin/en`: `admin-submissions.vue:268`
  - POST `/experiments/from_comment/`: `comment-convert-modal.vue:59`
- **Content and publishing:**
  - PATCH `/experiments/{id}/language/{lang}/`: `submission-create.vue:451`, `submission-wizard.vue:144`
  - PATCH `/experiments/{id}/language/`: `submission-intro-translations.vue:133`
  - POST `/experiments/{id}/publish/`: `submission-create.vue:379`, `submission-wizard.vue:146`
  - POST `/experiments/{id}/tag/`: `submission-create.vue:387`
  - DELETE `/experiments/{id}/tag/{tagId}/`: `submission-create.vue:395`
  - PATCH `/experiments/{id}/settings/`: `submission.vue:382` (resolve/reopen), `submission-location-edit.vue:70,81`, `canvases/experiment-mesh.vue:470` (hex placement)
  - PATCH `/experiments/{id}/image_collection/`: `submission.js:175`, `submission-edit.vue:84`
  - POST `/experiments/{id}/share/`: `submission-share.vue:132`
- **Participation, team and relations:**
  - POST and DELETE `/experiments/{id}/{like|updates|help}/`: `submission.js:43,47` (called from `submission-participate.vue:179-183`)
  - POST `/experiments/{id}/team/{userId}/`: `submission.js:99`
  - DELETE `/experiments/{id}/team/{userId}/`: `submission.js:117`, `submission-join.vue:39`
  - POST `/experiments/{id}/team/me/`: `submission-join.vue:31`
  - POST `/experiments/{id}/related_experiments/`: `submission.js:149`
  - DELETE `/experiments/{id}/related_experiments/{relId}/`: `submission.js:167`
- **Media, attachments and events:**
  - GET `/experiments/{id}/photo_stream/50/`: `submission.vue:394`
  - POST `/experiments/{id}/photo_stream/` (via prop `submission.vue:126`): → `image-stream.vue:129`
  - POST `/experiments/{id}/attachments/file/` (via prop `submission.vue:111`): → `attachments.vue:217`
  - POST `/experiments/{id}/attachments/file_request/` (via prop `submission.vue:110`, signed download): → `attachments.vue:193`
  - DELETE `/experiments/{id}/attachments/{attId}/` (via prop `submission.vue:109`): → `attachments.vue:182`
  - POST `/experiments/{id}/calendar/events/`: intended at `submission.vue:97`, but broken (see section 7)
- **Comments on a submission:**
  - GET and POST `/experiments/{id}/topic/` (via props `submission.vue:167-169`, `submission-feed.vue:23-25`): → `comment-topic.vue:526,546,670`
  - POST `/experiments/{id}/comment/{commentId}/reply/` (same props): → `comment-single.vue:405,429`
- **Spaces (mesh boards):**
  - GET `/experiments/spaces/`: `stores/spaces.js:25`
  - POST `/experiments/spaces/`: `stores/spaces.js:14`
  - DELETE `/experiments/spaces/{id}/`: `stores/spaces.js:19`

### Happenings (groups)
- **Core:**
  - GET `/happenings/`: `stores/groups.js:77`
  - POST `/happenings/`: `stores/groups.js:16`
  - GET `/happenings/{id}/`: `groups/group.js:25`, `group.vue:672`
  - POST `/happenings/{id}/config/`: `stores/groups.js:21`, `group.vue:618`, `group-org.vue:631`†‡
  - PATCH `/happenings/{id}/custom_language/{lang}/`: `group-intro.vue:37`
- **Comments:**
  - GET `/happenings/topics/` (`?comment_id=`): `stores/groups.js:86,94`
  - GET and POST `/happenings/{id}/topic/` (via props `group.vue:187-189`, `group-post-create.vue:39-41`): → `comment-topic.vue`
  - POST `/happenings/{id}/comment/{commentId}/reply/` (via props `group.vue:188`, `group-comment-feed.vue:9`): → `comment-single.vue`
- **Membership:**
  - POST `/happenings/{id}/registrations/`: `group.js:33`, `group.vue:595`, `group-org.vue:608`‡
  - POST `/happenings/{id}/registrations/{userId}/`: `group.vue:550`
  - DELETE `/happenings/{id}/registrations/{userId}/`: `group.vue:555` (and leave)
  - PATCH `/happenings/{id}/registrations/{userId}/`: `group.vue:560`†
  - GET `/happenings/{id}/registrations/`: `group-admin.vue:109`
  - POST `/happenings/{id}/applications/`: `group-register.vue:228`† (no template uses it)
- **Media and events:**
  - GET `/happenings/{id}/photo_stream/50/`: `group.vue:644` (and `/photo_stream/` with no limit at `group-org.vue:668`‡)
  - POST `/happenings/{id}/photo_stream/` (via prop `group.vue:134`): → `image-stream.vue:129`
  - GET `/happenings/{id}/calendar/events/`: `group.vue:656`
  - POST `/happenings/{id}/calendar/events/`: `group-event-new.vue:104`
  - GET `/happenings/37/calendar/past_events/2023-06-23/`: `pages/events-poster-page.vue:41`‡

### Calendar
- GET `/calendar/events/`: `stores/groups.js:67`, `pages/events-page.vue:84`, `pages/monitor-page.vue:154`
- DELETE `/calendar/events/{id}/`: `calendar-event-details.vue:108`, `session-signup.vue:197`
- GET `/calendar/events/participate/`: `session-signup.vue:190`
- POST `/calendar/events/attendance/{id}/me/`: `session-signup.vue:214`
- DELETE `/calendar/events/attendance/{id}/me/`: `session-signup.vue:206`
- POST `/calendar/events/attendance/{id}/me/1/`: `session-signup.vue:221`
- POST `/calendar/activites/events/` (spelled "activites" on both sides): `session-event-new.vue:50`
- GET `/monitor/haus/calendar/events/`: `monitor-members-page.vue:194`‡, `monitor-members-page-v2024.vue:197`‡

### Comments
- DELETE `/comments/{id}/`: `comment-single.vue:325`, `comment-reply.vue:78`
- POST `/comments/{id}/reaction/`: `reactions.vue:175`
- DELETE `/comments/{id}/reaction/`: `reactions.vue:161`
- POST `/comments/{id}/upvote/`: `comment-topic.vue:586`†
- DELETE `/comments/{id}/upvote/`: `comment-topic.vue:607`

### Tags
- GET `/tags/{lang}`: `stores/tags.js:10`, `admin-tags.vue:33`
- POST `/tags/{lang}`: `stores/tags.js:14`, `admin-tags.vue:39`
- PUT `/tags/{id}/{lang}`: `stores/tags.js:18`, `admin-tags.vue:64`
- DELETE `/tags/{id}/`: `stores/tags.js:22`, `admin-tags.vue:51`

### Activity
- GET `/events/`: `events/events.vue:87`
- POST `/events/last_activity_fetch/`: `events/events.vue:82`
- GET `/events/since/my_last_fetched/`: `header/activity.vue:23`

### Resources (bookings)
- GET `/resources/type/{type}/`: `resource-index.vue:111`
- GET `/resources/type/all/`: `admin-resources.vue:56`
- GET `/resources/{id}/`: `resource-details.vue:449`
- POST `/resources/{id}/bookings/`: `resource-details.vue:429`
- DELETE `/resources/{id}/bookings/{bookingId}/`: `resource-details.vue:413`
- POST `/resources/`: `admin-resources.vue:44`
- PATCH `/resources/{id}/`: `admin-resources.vue:51`
- DELETE `/resources/{id}/`: `admin-resources.vue:39`

### Accounts and invoicing (admin)
- **Accounts:**
  - GET `/accounts/`: `admin-accounts.vue:262`, `admin-accounts-new-items.vue:142,164` (164 is the bug in section 7)
  - POST `/accounts/`: `admin-accounts.vue:243`, `admin-accounts-new-coworker.vue:63`, `admin-accounts-new-items.vue:134`
  - PATCH `/accounts/{id}/`: `admin-accounts.vue:250`
  - DELETE `/accounts/{id}/`: `admin-accounts.vue:238`
  - POST `/accounts/import_accounts/`: `admin-accounts.vue:175`
- **Subscriptions:**
  - POST `/accounts/{id}/subscriptions/`: `admin-panel-table-field-subscriptions.vue:59`, `admin-accounts-new-items.vue:150`
  - PATCH `/accounts/{id}/subscriptions/{sid}/`: `admin-panel-table-field-subscriptions.vue:73`, `admin-accounts-overview.vue:263`
  - DELETE `/accounts/{id}/subscriptions/{sid}/`: `admin-panel-table-field-subscriptions.vue:54`
- **Invoices:**
  - POST `/accounts/{id}/custom_invoices/`: `admin-accounts.vue:228`, `admin-accounts-custom-invoice.vue:91`, `admin-accounts-invoicing.vue:251`
  - GET `/accounts/invoices/{start}/{end}/`: `admin-accounts-invoicing.vue:271`
  - POST `/accounts/{id}/invoices/`: `admin-accounts-invoicing.vue:280`
  - GET `/accounts/{id}/invoices/{month}/`: `admin-accounts-invoicing.vue:289`
  - GET `/accounts/invoiced/{month}/`: `admin-accounts-invoicing.vue:298`
  - GET `/accounts/updated/{month}/`: `admin-accounts-invoicing.vue:307`

### Surveys
- GET `/survey_results/{surveyName}/`: `pages/admin-page-surveys.vue:61`

### Backend routes the frontend never calls
`/video_chat/*`, `/experiments/{id}/video_chat/`, `/happenings/{id}/video_chat/`, `/contact/`, `/experiments/{id}/funding/`, `/stagemoves/`, `/links/`, `/custom_language/` for experiments, `/happenings/{id}/schedule/`, DELETE `/happenings/{id}/`, `/sdg/tags/`, `/dashboard/`, `/platforms/`, `/users/me/password`, POST `/survey_results/`, and the maintenance GETs (`resaveallexperiments`, `rewritedocuments`, `emailout`). `GET /platform/config/` is defined twice in `../backend/silex/Routes.php`.
