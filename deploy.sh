#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'

PROJECT_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

log() {
    printf '[deploy] %s\n' "$*"
}

fail() {
    printf '[deploy] ERROR: %s\n' "$*" >&2
    exit 1
}

env_value() {
    local key="$1"
    local fallback="${2:-}"
    local value=""

    if [[ -n "${!key+x}" ]]; then
        value="${!key}"
    elif [[ -f .env ]]; then
        value="$(grep -E "^[[:space:]]*${key}=" .env | tail -n 1 | cut -d '=' -f 2- || true)"
        value="${value%$'\r'}"
        if [[ "${value:0:1}" == '"' && "${value: -1}" == '"' ]]; then
            value="${value:1:${#value}-2}"
        elif [[ "${value:0:1}" == "'" && "${value: -1}" == "'" ]]; then
            value="${value:1:${#value}-2}"
        fi
    fi

    printf '%s' "${value:-$fallback}"
}

resolve_path() {
    local path="$1"

    if [[ "$path" = /* ]]; then
        printf '%s' "$path"
    else
        printf '%s/%s' "$PROJECT_ROOT" "${path#./}"
    fi
}

if [[ ! -f .env ]]; then
    fail '.env is required for a production deployment.'
fi

if ! grep -Eq '^[[:space:]]*APP_ENV=production([[:space:]]|$)' .env \
    || ! grep -Eq '^[[:space:]]*APP_DEBUG=false([[:space:]]|$)' .env \
    || ! grep -Eq '^[[:space:]]*APP_URL=https://' .env \
    || ! grep -Eq '^[[:space:]]*SESSION_SECURE_COOKIE=true([[:space:]]|$)' .env; then
    fail 'Require APP_ENV=production, APP_DEBUG=false, HTTPS APP_URL and SESSION_SECURE_COOKIE=true.'
fi

if [[ -n "$(git status --porcelain --untracked-files=all)" ]]; then
    fail 'The deployment worktree must be clean. Commit or remove local changes before deploying.'
fi

remote="$(env_value DEPLOY_REMOTE origin)"
branch="$(env_value DEPLOY_BRANCH master)"
requested_ref="$(env_value DEPLOY_REF '')"
db_connection="$(env_value DB_CONNECTION mysql)"
db_username="$(env_value DB_USERNAME '')"
db_password="$(env_value DB_PASSWORD '')"
mail_mailer="$(env_value MAIL_MAILER log)"
mail_host="$(env_value MAIL_HOST '')"
queue_connection="$(env_value QUEUE_CONNECTION database)"
queue_worker_check="$(env_value DEPLOY_QUEUE_WORKER_CHECK true)"
supervisor_program="$(env_value DEPLOY_SUPERVISOR_PROGRAM crm-document-worker)"
backup_directory="$(env_value DEPLOY_BACKUP_DIR storage/app/backups)"
backup_directory_absolute="$(resolve_path "$backup_directory")"
backup_retention="$(env_value DEPLOY_BACKUP_RETENTION 7)"
storage_backup_enabled="$(env_value DEPLOY_STORAGE_BACKUP true)"
app_url="$(env_value APP_URL '')"
app_key="$(env_value APP_KEY '')"
health_url="$(env_value DEPLOY_HEALTH_URL '')"
dump_binary="$(env_value DB_DUMP_BINARY mysqldump)"

if [[ "${db_connection,,}" != 'sqlite' ]]; then
    [[ -n "$db_username" && "${db_username,,}" != 'change-me' && "${db_username,,}" != 'root' ]] \
        || fail 'Production DB_USERNAME must be a dedicated non-placeholder user.'
    [[ -n "$db_password" && "${db_password,,}" != 'change-me' && "${db_password,,}" != 'password' && "${db_password,,}" != 'null' ]] \
        || fail 'Production DB_PASSWORD must be set to a non-placeholder secret.'
fi

[[ "${mail_mailer,,}" != 'log' ]] || fail 'MAIL_MAILER=log is not allowed in production.'
[[ -n "$mail_host" ]] || fail 'MAIL_HOST must be configured in production.'
[[ "${mail_host,,}" != 'mailhog' && "${mail_host,,}" != 'mailhog.local' && "${mail_host,,}" != 'smtp.example.com' ]] \
    || fail 'MAIL_HOST cannot point to MailHog or an example SMTP host in production.'
mail_username="$(env_value MAIL_USERNAME '')"
mail_password="$(env_value MAIL_PASSWORD '')"
[[ "${mail_username,,}" != 'change-me' && "${mail_password,,}" != 'change-me' ]] \
    || fail 'SMTP credentials still contain the change-me placeholder.'
[[ "${queue_connection,,}" != 'sync' ]] || fail 'QUEUE_CONNECTION=sync is not allowed in production.'
[[ "$queue_worker_check" =~ ^(true|false)$ ]] || fail 'DEPLOY_QUEUE_WORKER_CHECK must be true or false.'
[[ -n "$app_key" && "${app_key,,}" != 'change-me' ]] || fail 'APP_KEY must be configured in production.'

for binary in git php composer node npm curl tar; do
    command -v "$binary" >/dev/null 2>&1 || fail "Required command not found: $binary"
done

if [[ "$queue_worker_check" == 'true' ]]; then
    command -v supervisorctl >/dev/null 2>&1 \
        || fail 'supervisorctl is required when DEPLOY_QUEUE_WORKER_CHECK=true.'
fi

[[ "$backup_retention" =~ ^[1-9][0-9]*$ ]] || fail 'DEPLOY_BACKUP_RETENTION must be a positive integer.'
[[ "$backup_directory_absolute" != '/' && "$backup_directory_absolute" != "$PROJECT_ROOT" ]] \
    || fail 'DEPLOY_BACKUP_DIR points to an unsafe directory.'

mkdir -p -- "$backup_directory_absolute"
chmod 700 -- "$backup_directory_absolute"

previous_commit="$(git rev-parse --verify HEAD^{commit})"

log "Fetching ${remote}/${branch}..."
git fetch --prune "$remote" "$branch"

target_ref="${requested_ref:-${remote}/${branch}}"
target_commit="$(git rev-parse --verify "${target_ref}^{commit}")" \
    || fail "Could not resolve deployment ref: ${target_ref}"

if [[ -z "$health_url" ]]; then
    [[ -n "$app_url" ]] || fail 'APP_URL or DEPLOY_HEALTH_URL is required for the post-deploy health check.'
    health_url="${app_url%/}/health"
fi

[[ "$health_url" == https://* ]] || fail 'DEPLOY_HEALTH_URL must use HTTPS in production.'

if [[ "$dump_binary" = /* || "$dump_binary" == ./* || "$dump_binary" == ../* ]]; then
    [[ -f "$dump_binary" && -x "$dump_binary" ]] || fail "Database dump binary is not executable: ${dump_binary}"
else
    command -v "$dump_binary" >/dev/null 2>&1 || fail "Database dump binary not found: ${dump_binary}"
fi

maintenance_enabled=0
db_backup_file=''
storage_backup_file=''
timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
manifest_file="${backup_directory_absolute}/deploy-${timestamp}.manifest"

deployment_failed() {
    local status=$?

    trap - EXIT

    if (( status == 0 )); then
        exit 0
    fi

    if (( maintenance_enabled == 1 )); then
    php artisan up >/dev/null 2>&1 || true
        maintenance_enabled=0
    fi

    printf '\n[deploy] Deployment failed with status %s.\n' "$status" >&2
    printf '[deploy] Previous commit: %s\n' "$previous_commit" >&2
    if [[ -n "$db_backup_file" ]]; then
        printf '[deploy] Database backup: %s\n' "$db_backup_file" >&2
        if [[ -n "$storage_backup_file" ]]; then
            printf '[deploy] Storage backup: %s\n' "$storage_backup_file" >&2
            printf '[deploy] Manual rollback: CONFIRM_ROLLBACK=YES ROLLBACK_COMMIT=%s DB_BACKUP_FILE=%s STORAGE_BACKUP_FILE=%s ./rollback.sh\n' \
                "$previous_commit" "$db_backup_file" "$storage_backup_file" >&2
        else
            printf '[deploy] Manual rollback: CONFIRM_ROLLBACK=YES ROLLBACK_COMMIT=%s DB_BACKUP_FILE=%s ./rollback.sh\n' \
                "$previous_commit" "$db_backup_file" >&2
        fi
    else
        printf '[deploy] No database backup was created; inspect the failure before retrying.\n' >&2
    fi

    exit "$status"
}

trap deployment_failed EXIT

log "Target commit: ${target_commit}"
log 'Enabling maintenance mode.'
maintenance_enabled=1
php artisan down --retry=60 || fail 'Could not enable maintenance mode.'

if [[ "${storage_backup_enabled,,}" == 'true' ]]; then
    storage_paths=()
    [[ -d storage/app/private ]] && storage_paths+=(storage/app/private)
    [[ -d storage/app/public ]] && storage_paths+=(storage/app/public)

    if (( ${#storage_paths[@]} > 0 )); then
        storage_backup_file="${backup_directory_absolute}/storage-${timestamp}.tar.gz"
        log 'Creating a storage archive before code and migration changes.'
        tar -czf "$storage_backup_file" -C "$PROJECT_ROOT" "${storage_paths[@]}"
        chmod 600 -- "$storage_backup_file"
    fi
fi

if [[ "$previous_commit" != "$target_commit" ]]; then
    if [[ -z "$requested_ref" && "$target_ref" == "${remote}/${branch}" ]] \
        && git show-ref --verify --quiet "refs/heads/${branch}"; then
        git checkout "$branch"
        git reset --ff-only "$target_commit"
    else
        git -c advice.detachedHead=false checkout --detach "$target_commit"
    fi
fi

log 'Installing PHP dependencies.'
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

log 'Building frontend assets.'
node --version
npm --version
npm ci --include=dev
npm run build

php artisan optimize:clear

log 'Creating database backup before migrations.'
set +e
backup_output="$(php artisan backup:database --path="$backup_directory_absolute" --keep="$backup_retention" --no-ansi 2>&1)"
backup_status=$?
set -e
printf '%s\n' "$backup_output"
(( backup_status == 0 )) || fail 'The database backup command failed.'
db_backup_file="$(printf '%s\n' "$backup_output" | sed -n 's/^BACKUP_PATH=//p' | tail -n 1)"
[[ -n "$db_backup_file" && -s "$db_backup_file" ]] \
    || fail 'The database backup command did not return a readable backup file.'

{
    printf 'previous_commit=%s\n' "$previous_commit"
    printf 'target_commit=%s\n' "$target_commit"
    printf 'database_backup=%s\n' "$db_backup_file"
    printf 'storage_backup=%s\n' "$storage_backup_file"
    printf 'created_at=%s\n' "$timestamp"
} > "$manifest_file"
chmod 600 -- "$manifest_file"

log 'Running database migrations.'
php artisan migrate --force

log 'Verifying the public storage link.'
php artisan storage:link
[[ -L public/storage ]] || fail 'public/storage is not a symbolic link after storage:link.'

log 'Caching production configuration and views.'
php artisan config:cache
php artisan route:cache
php artisan view:cache

log 'Restarting queue workers so they load the new release.'
php artisan queue:restart

if [[ "$queue_worker_check" == 'true' ]]; then
    log "Checking Supervisor queue workers: ${supervisor_program}"
    supervisorctl status | awk -v program="${supervisor_program}:" \
        '$1 ~ "^" program && $2 == "RUNNING" { running = 1 } END { exit running ? 0 : 1 }' \
        || fail "No RUNNING Supervisor worker found for ${supervisor_program}."
fi

log 'Disabling maintenance mode.'
php artisan up
maintenance_enabled=0

log "Running health check: ${health_url}"
health_response="$(curl --fail --silent --show-error --max-time 30 "$health_url")"
printf '%s' "$health_response" | grep -Eq '"status"[[:space:]]*:[[:space:]]*"ok"' \
    || fail 'Health endpoint did not report status=ok.'

find "$backup_directory_absolute" -maxdepth 1 -type f \
    \( -name '*.tar.gz' -o -name '*.manifest' \) \
    -mtime "+${backup_retention}" -delete

trap - EXIT
log "Deployment finished at ${target_commit}."
