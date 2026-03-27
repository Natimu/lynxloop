# AGENTS GUIDE FOR LYNXLOOP 1.1
This document is the shared memory for every autonomous agent in this repository.
It captures how to bootstrap the stack, run checks, and keep the codebase stylistically aligned.
No Cursor (.cursor/rules or .cursorrules) or Copilot (.github/copilot-instructions.md) rule files exist, so treat this guide as canonical.

## Orientation Map
- `public/index.php` is the single entry point; it wires the Router, loads controllers, and dispatches requests.
- `app/core/` contains the micro-framework primitives: `Controller`, `Database`, `Model`, plus a global `Router`.
- `app/controllers/` currently offers `HomeController` and `AuthController`, both extending `App\Core\Controller`.
- `app/models/` hosts `App\Models\User`, the lone PDO-backed model today.
- `app/views/` contains layouts, feature views, and empty `partials/`; note the casing mismatch (`Auth/` folder vs. controller lookup `auth/`).
- `db/lynxloop_db.sql` is the authoritative schema and seed dump (~600 lines) for MySQL 8.
- `docker/`, `Dockerfile`, and `docker-compose.yml` define Apache + PHP 8.2, MySQL, and phpMyAdmin services.
- There is no Composer autoloader, package.json, or test suite; add tooling explicitly when required.

## Runtime & Toolchain Expectations
- Target PHP 8.2 with `declare(strict_types=1);` at the top of every PHP file (only `public/index.php` does this now).
- Apache’s mod_rewrite is available in Docker; route all traffic through `public/.htaccess` and `index.php`.
- Use Docker Desktop (or compatible) and assume `docker compose` v2 syntax for commands.
- MySQL 8 runs inside the `db` service; phpMyAdmin listens on port 8081.
- Outside Docker you may run `php -S localhost:8000 -t public` but must provide matching DB credentials manually.
- Composer is absent, so any dependency requires adding `composer.json` plus instructions in this file.

## Build & Run Commands
- `docker compose up --build` — rebuild the PHP image and start Apache, MySQL, phpMyAdmin; run this on first boot or after Dockerfile edits.
- `docker compose up` — start the existing containers for day-to-day work.
- `docker compose down -v` — stop all services and drop the MySQL volume for a clean database slate (re-import schema afterward).
- `docker compose logs -f web` — tail Apache/PHP logs; use this instead of inline `var_dump` debugging when possible.
- `docker compose exec web bash` — enter the PHP container to run `php -l`, artisan-style scripts, or DB clients.
- `docker compose exec db mysql -u lynxuser -ppa55word lynxloop_db` — open a MySQL shell without phpMyAdmin.
- `php -S localhost:8000 -t public` — lightweight host web server; keep DB pointing at the running MySQL container or a local instance.

## Linting & Static Analysis
- `find app -name '*.php' -print0 | xargs -0 -n1 php -l` — run PHP’s syntax check across the codebase (inside or outside Docker).
- `php -l public/index.php` — mandatory after editing the front controller; syntax errors here stop every request.
- When Composer lands, prefer PSR-12 with `vendor/bin/phpcs --standard=PSR12 app public`.
- For static analysis, adopt PHPStan level 5 via `vendor/bin/phpstan analyse app public --level=5` once dependencies exist.
- Keep lint commands runnable in both the host and `web` container to mirror production extensions.

## Testing Playbook
- No automated tests exist yet; document manual QA steps (routes hit, payloads, expected output) in PRs.
- Future full-suite command (after adding Composer + PHPUnit): `./vendor/bin/phpunit`.
- Single test focus (requested format): `./vendor/bin/phpunit --filter AuthControllerTest::testRegisterRequiresEmail`.
- Use `.env.testing` or injected env vars for PHPUnit so tests do not mutate the shared dev database.
- Prefer controller and model unit tests before adding browser/UI layers; snapshot or Dusk-style tests are overkill for now.

## Database & Data Flow
- Import schema via `docker compose exec db mysql -u root -proot < db/lynxloop_db.sql` or phpMyAdmin’s import UI.
- All tables default to InnoDB + utf8mb4; keep those defaults for migrations or manual DDL.
- `db/lynxloop_db.sql` seeds users, listings, trades, notifications, and more—even if unused at the PHP layer yet.
- Use prepared statements exclusively; `App\Models\User` demonstrates named placeholders and binding.
- Timestamps should lean on `NOW()` for inserts and `ON UPDATE CURRENT_TIMESTAMP` where defined.
- Foreign keys are absent; if you add them, verify cascading rules and update the dump (or create migrations) accordingly.

## Controllers & Routing Patterns
- Register new controllers by adding `require_once` entries in `public/index.php` before calling `$router->get()` or `$router->post()`.
- Keep controller actions thin: sanitize request data, delegate to models/services, decide on a response helper, and return early on validation failures.
- Guard each action by HTTP method even if the Router enforces it; bail with `http_response_code(405)` plus a friendly view for unsupported verbs.
- Use `$this->redirect('/login')` style absolute paths to avoid rewrite edge cases when Apache rules evolve.
- Pass associative arrays to `view()` so templates never touch `$_POST` or `$_SESSION` directly; precompute booleans in the controller layer.
- For JSON APIs, call `$this->json($payload, $statusCode)` which sets headers and exits; never echo arrays manually.

## Models & Data Access Guidelines
- Extend `App\Core\Model` so `$this->db` is a shared PDO instance; set `protected string $table = 'foo';` instead of sprinkling literals.
- Write explicit column lists (`SELECT id, email FROM ...`) to avoid schema-dependent bugs and to document expected fields in context.
- Bind all parameters with named placeholders and, where appropriate, explicit PDO parameter types (`PDO::PARAM_INT`).
- Wrap multi-statement writes in transactions and roll back on failure; surface friendly errors to controllers and log exception messages privately.
- Extract private helper methods for repeated query fragments (e.g., `buildUserSelect()`); keep controllers unaware of SQL details.
- Return hydrated arrays or dedicated DTOs; controllers should never receive raw `PDOStatement` objects.

## Error Handling, Logging, and Responses
- Use `http_response_code()` with descriptive copy in views for routing or authorization failures; keep status codes accurate.
- Prefer thrown `RuntimeException`/`DomainException` inside models and catch them at the controller layer to set flash errors.
- Log unexpected exceptions via `error_log()` or a future PSR-3 logger; never leak stack traces to end users or JSON clients.
- Replace `var_dump`/`die` debugging with `docker compose logs -f web` or temporary log statements so responses stay valid.
- Keep validation errors in associative arrays keyed by field (plus `general` when needed) and feed that structure straight into views.
- For JSON responses, include `success`, `message`, and optional `data` keys to maintain a predictable schema.

## Application Architecture
- **Router & Front Controller** — `public/index.php` boots the Router, starts sessions (add `session_start()` before touches), and registers `$router->get()`/`$router->post()` handlers.
- **Controllers** — Extend `App\Core\Controller` to inherit `view()`, `redirect()`, and `json()` helpers; guard on HTTP method even if the Router enforces it.
- **Models** — Extend `App\Core\Model`, define `protected string $table`, and use `$this->db` (PDO) for queries; keep SQL readable via multiline strings or heredoc.
- **Views** — Rendered through `Controller::view()` with `app/views/layouts/main.php` as the default shell; pass data arrays and escape output with `htmlspecialchars`.
- **Sessions** — `session_regenerate_id(true)` already runs after successful login; ensure `session_start()` exists before reading from `$_SESSION`.

## Code Style & Quality Rules
- **Formatting** — Follow PSR-12 spacing and brace placement; use 4-space indents, one class/interface per file, and blank lines between logical blocks.
- **Imports & Namespaces** — Add `declare(strict_types=1);` then the namespace, then grouped `use` statements ordered alphabetically (built-ins before project classes).
- **Typing** — Add scalar and return type hints everywhere; prefer typed properties (`protected PDO $db;`) and constructor property promotion when appropriate.
- **Arrays & Defaults** — Use short array syntax `[]`, set sensible default values in signatures, and avoid nulls unless they carry meaning.
- **Naming** — Classes are StudlyCase, methods camelCase verbs (`handleLogin`, `storeUser`), constants UPPER_SNAKE, views lowercase to match router lookups.
- **Control Flow** — Prefer early returns for validation errors; collect validation feedback in associative `$errors` keyed by field plus an optional `general` key.
- **Error Handling** — Wrap DB writes in try/catch when rollback is possible, log via `error_log()`, and return friendly messages (never stack traces) to clients.
- **Responses** — Use `json()` for JSON payloads, `redirect()` for HTTP redirects (absolute paths), and `view()` for templated HTML; never echo raw HTML inside controllers.
- **Comments** — Only add comments when intent is non-obvious; otherwise rely on clear naming and extracted methods.

## Security & Session Expectations
- Sanitize all `$_POST`/`$_GET` inputs with `trim()` + `filter_var()` before use; replicate the `AuthController::register()` pattern.
- Hash passwords with `password_hash()` and verify with `password_verify()`; never store plaintext or reversible hashes.
- Enforce allowed role values (`student`, `alumni`, `faculty`, `admin`) and validate server-side, not just in the UI.
- Add CSRF tokens before introducing state-changing POST routes beyond auth; none exist yet.
- When returning JSON, avoid leaking internal IDs or stack traces; log detailed errors separately.
- Call `session_start()` at the very beginning of `public/index.php` before reading or writing session data.
- Store flash messages or one-time banners in `$_SESSION` and unset them immediately after rendering to prevent stale state.

## Views, Layouts, and Assets
- Keep `app/views/layouts/main.php` as the layout shell; content views should only output the inner markup they own.
- Stick to inline CSS or `<style>` blocks until an asset pipeline arrives; document any required fonts/images in PRs.
- Escape every dynamic value via `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` and prefer helper functions or variables prepared in controllers.
- Organize reusable markup under `app/views/partials/` (prefix filenames with `_`) and `require` them from views when needed.
- Avoid DB queries or heavy logic inside views; controllers must supply all necessary data.
- Use POST forms for any state-changing action and mirror validation rules on the server even if JS validation is introduced later.
- Keep `<title>` and `<meta>` tags descriptive inside layout conditionals so QA reviewers can quickly identify the rendered route.

## Manual QA & Observability
- Default smoke test path: submit `/register`, `/login`, then land on `/` to verify routing, layout, and auth-sensitive header text.
- Capture manual QA evidence in PR descriptions: include routes visited, payloads, and observed responses.
- Tail PHP errors with `docker compose logs -f web` while exercising flows; fix notices immediately.
- Inspect DB effects with `docker compose exec db mysql -u lynxuser -ppa55word lynxloop_db -e "SELECT COUNT(*) FROM users"` after registration workflows.
- Cycle through login/logout to ensure sessions clear correctly before re-running registration or password reset tests.
- Use phpMyAdmin on port 8081 for visual verification of inserts/updates when CLI access is inconvenient.
- Note before/after row counts or sample IDs in PR descriptions so reviewers can replay the same steps quickly.

## Observability & Troubleshooting
- `docker compose ps` confirms service health; restart a single container with `docker compose restart web` when PHP changes misbehave.
- For fatal errors, run `docker compose logs -f web` in a separate terminal and reproduce the request to capture stack traces.
- Use `docker compose exec web php -l path/to/file.php` to pinpoint syntax errors reported in logs.
- Database quirks can be checked via `docker compose logs -f db` or `SHOW ENGINE INNODB STATUS` inside the MySQL shell.
- Keep `display_errors` disabled in production configs; rely on logs plus temporary instrumentation for local debugging.

## Git & Workflow Norms
- Maintain small, purposeful commits; avoid mixing schema, backend, and view edits unless tightly coupled.
- Never revert or overwrite user-provided changes unless explicitly asked; work around existing diffs instead.
- Keep secrets out of Git—`.env` stays local. If you add one, commit `.env.example` plus documentation here.
- Always use feature branches; do not commit to `main` directly.
- Update this AGENTS guide whenever you add tooling, workflows, or conventions future agents must follow.

## Deployment & Environment Tips
- Default Docker `.env` values live in `docker-compose.yml`; override them via an `.env` file beside the compose file when needed.
- If you run PHP’s built-in server, ensure Apache-specific headers/routes still work by retesting inside Docker before merging.
- Update `docker/Dockerfile` and `docker/apache.conf` whenever enabling new PHP extensions so teammates can rebuild consistently.
- Use `docker compose down -v` sparingly; it wipes the DB volume. Re-import `db/lynxloop_db.sql` immediately afterward to avoid drift.
- Document any manual tweaks (hostfile entries, SSL certs) in PRs so reviewers can mirror the environment quickly.

## Future Tooling & Automation
- When adding Composer, pin versions and describe install steps here plus in the README or AGENTS diff.
- Introduce lint/test scripts via `composer scripts` or Makefiles so agents can run `composer run lint` consistently.
- If CI is added, mirror its commands locally (e.g., GitHub Actions job steps) to avoid “works on my machine” surprises.
- Expand this guide whenever automation changes expectations (new services, env vars, secrets rotation, etc.).

## Reference Checklist Before Opening a PR
- [ ] Stack boots via `docker compose up` without fatal errors.
- [ ] New PHP files declare strict types, have namespaces, and include typed signatures.
- [ ] Routes register inside `public/index.php` with matching `require_once` entries.
- [ ] Schema changes live in `db/lynxloop_db.sql` (or a documented migration) and stay in sync with MySQL.
- [ ] `php -l` (and PHPCS/PHPStan if configured) passes on all modified files.
- [ ] Tests or manual QA steps are provided; include the `--filter` PHPUnit example when relevant.
- [ ] Security-sensitive code paths are reviewed for sanitization, escaping, and session usage.
- [ ] This AGENTS file reflects any new conventions introduced by your work.
