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

