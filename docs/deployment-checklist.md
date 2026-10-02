# Production deployment checklist

## Before deploy

- [ ] `APP_ENV=production`, `APP_DEBUG=false` and an HTTPS `APP_URL` are set.
- [ ] `APP_KEY`, database, SMS, mail and `OPENWEATHER_API_KEY` values are stored only in the server `.env` or secret manager.
- [ ] Any credential that was previously committed or rendered into HTML has been revoked and replaced at the provider.
- [ ] `CORS_ALLOWED_ORIGINS` contains only required HTTPS origins; it is not `*`.
- [ ] `composer audit` and `npm audit --audit-level=high` are clean.
- [ ] The target commit is reviewed and the worktree is clean.
- [ ] The queue worker and scheduler services are running or ready to restart.

## Deploy

Run from the release directory:

```bash
DEPLOY_REF=<commit-or-tag> bash deploy.sh
```

The script checks the target commit, enters maintenance mode, creates database/storage backups, installs dependencies, builds the frontend, runs migrations, recreates the storage link, warms caches and checks `/health` over HTTPS.

## After deploy

- [ ] `/health` returns HTTP 200 and reports database/storage as `ok`.
- [ ] `php artisan queue:failed` is reviewed.
- [ ] Supervisor worker is processing the `database` queue.
- [ ] Cron is invoking `php artisan schedule:run` every minute.
- [ ] Login, filial isolation, document creation, private file download and payment are smoke-tested.
- [ ] The latest dump and storage archive are present under `storage/app/backups`.

## Telegram “Yangi murojaat” update

Deploy the companion `crm_bot` update together with the CRM API/controller, integration service, Lead relation and `resources/views/leads/` changes. The two-step intake uses `request.mode=compact`: the customer sends text/files and then their own contact. Missing document type and urgency are allowed and clarified by the employee. Legacy detailed requests are still accepted.

- [ ] `CRM_BOT_API_KEY` matches the bot configuration. Set `CRM_BOT_DEFAULT_FILIAL_ID` and optionally `CRM_BOT_DEFAULT_ASSIGNEE_ID` to route response tasks; otherwise the integration uses an existing filial and an employee/admin of that filial.
- [ ] Existing bot integration migrations are installed. This compact intake change adds no migration.
- [ ] Run `php artisan optimize:clear`, then restore the release's normal cache warmup. Restart the bot and its separate outbox worker.
- [ ] `php artisan test --compact --filter="TelegramBotApiTest|OperationsGrowthTest"` passes in the isolated testing environment, and `python -m pytest -q` passes in the bot project.
- [ ] With an authorized test account, submit text-only and file-only requests through “Yangi murojaat”. In CRM `/leads`, open “Telegram murojaati” on the customer row and check full text, contact, captions and private downloads. Verify the response task and “Xizmatni aniqlashtirish” action.
- [ ] Retrying an intake/upload returns the existing record; any pending files show their received/expected count until the outbox worker completes the uploads.

## Rollback

Do not overwrite production data without explicit approval. Select the exact code commit and matching backups first:

```bash
CONFIRM_ROLLBACK=YES \
ROLLBACK_COMMIT=<known-good-commit> \
DB_BACKUP_FILE=storage/app/backups/<dump>.sql \
STORAGE_BACKUP_FILE=storage/app/backups/<storage>.tar.gz \
bash rollback.sh
```

If the database user cannot recreate the database, add `ROLLBACK_RECREATE_DATABASE=false` and verify the import carefully.

