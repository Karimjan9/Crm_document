#!/usr/bin/env bash

set -Eeuo pipefail
IFS=$'\n\t'

PROJECT_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

fail() {
    printf '[rollback] ERROR: %s\n' "$*" >&2
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

[[ "${CONFIRM_ROLLBACK:-}" == 'YES' ]] \
    || fail 'Rollback is destructive. Re-run with CONFIRM_ROLLBACK=YES after verifying the backup and commit.'
[[ -n "${ROLLBACK_COMMIT:-}" ]] || fail 'ROLLBACK_COMMIT is required.'
[[ -n "${DB_BACKUP_FILE:-}" ]] || fail 'DB_BACKUP_FILE is required.'
[[ -f .env ]] || fail '.env is required.'

[[ "$(env_value DB_CONNECTION mysql)" == 'mysql' ]] \
    || fail 'rollback.sh currently supports DB_CONNECTION=mysql only.'

if ! grep -Eq '^[[:space:]]*APP_ENV=production([[:space:]]|$)' .env \
    || ! grep -Eq '^[[:space:]]*APP_DEBUG=false([[:space:]]|$)' .env; then
    fail 'Rollback is allowed only when APP_ENV=production and APP_DEBUG=false.'
fi

for binary in git php composer node npm curl tar realpath mktemp; do
    command -v "$binary" >/dev/null 2>&1 || fail "Required command not found: $binary"
done

[[ -z "$(git status --porcelain --untracked-files=all)" ]] \
    || fail 'The rollback worktree must be clean.'

rollback_commit="$(git rev-parse --verify "${ROLLBACK_COMMIT}^{commit}")" \
    || fail "Could not resolve rollback commit: ${ROLLBACK_COMMIT}"

backup_directory="$(env_value DEPLOY_BACKUP_DIR storage/app/backups)"
backup_directory_absolute="$(resolve_path "$backup_directory")"
backup_directory_real="$(realpath -e "$backup_directory_absolute")" \
    || fail "Backup directory does not exist: ${backup_directory_absolute}"

backup_file_absolute="$(resolve_path "$DB_BACKUP_FILE")"
backup_file_real="$(realpath -e "$backup_file_absolute")" \
    || fail "Database backup does not exist: ${backup_file_absolute}"
[[ -s "$backup_file_real" ]] || fail 'Database backup is empty.'

case "$backup_file_real" in
    "$backup_directory_real"/*) ;;
    *) fail 'DB_BACKUP_FILE must be inside DEPLOY_BACKUP_DIR.' ;;
esac

storage_backup_real=''
if [[ -n "${STORAGE_BACKUP_FILE:-}" ]]; then
    storage_backup_absolute="$(resolve_path "$STORAGE_BACKUP_FILE")"
    storage_backup_real="$(realpath -e "$storage_backup_absolute")" \
        || fail "Storage backup does not exist: ${storage_backup_absolute}"
    case "$storage_backup_real" in
        "$backup_directory_real"/*) ;;
        *) fail 'STORAGE_BACKUP_FILE must be inside DEPLOY_BACKUP_DIR.' ;;
    esac
fi

db_host="$(env_value DB_HOST 127.0.0.1)"
db_port="$(env_value DB_PORT 3306)"
db_name="$(env_value DB_DATABASE '')"
db_user="$(env_value DB_USERNAME '')"
db_password="$(env_value DB_PASSWORD '')"
db_client="$(env_value DB_CLIENT_BINARY mysql)"
recreate_database="$(env_value ROLLBACK_RECREATE_DATABASE true)"
app_url="$(env_value APP_URL '')"
health_url="$(env_value DEPLOY_HEALTH_URL '')"

[[ -n "$db_name" && -n "$db_user" ]] || fail 'DB_DATABASE and DB_USERNAME are required.'
[[ "$db_name" =~ ^[A-Za-z0-9_]+$ ]] || fail 'DB_DATABASE must contain only letters, numbers and underscores for rollback.'
if [[ "$db_client" = /* || "$db_client" == ./* || "$db_client" == ../* ]]; then
    [[ -f "$db_client" && -x "$db_client" ]] || fail "Database client is not executable: ${db_client}"
else
    command -v "$db_client" >/dev/null 2>&1 || fail "Database client not found: ${db_client}"
fi
[[ -n "$health_url" ]] || health_url="${app_url%/}/health"
[[ "$health_url" == https://* ]] || fail 'APP_URL/DEPLOY_HEALTH_URL must use HTTPS.'

maintenance_enabled=0
rollback_failed() {
    local status=$?

    trap - EXIT

    if (( status != 0 && maintenance_enabled == 1 )); then
        php artisan up >/dev/null 2>&1 || true
    fi

    if (( status != 0 )); then
        printf '[rollback] Rollback failed. Verify application and database state before reopening traffic.\n' >&2
    fi

    exit "$status"
}
trap rollback_failed EXIT

php artisan down --retry=60 || fail 'Could not enable maintenance mode.'
maintenance_enabled=1

printf '[rollback] Checking out %s.\n' "$rollback_commit"
git -c advice.detachedHead=false checkout --detach "$rollback_commit"

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci --include=dev
npm run build
php artisan optimize:clear

printf '[rollback] Restoring database from %s.\n' "$backup_file_real"
if [[ "${recreate_database,,}" == 'true' ]]; then
    printf '[rollback] Recreating database %s before importing the backup.\n' "$db_name"
    MYSQL_PWD="$db_password" "$db_client" \
        --host="$db_host" \
        --port="$db_port" \
        --user="$db_user" \
        --execute="DROP DATABASE IF EXISTS \`$db_name\`; CREATE DATABASE \`$db_name\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
fi

MYSQL_PWD="$db_password" "$db_client" \
    --host="$db_host" \
    --port="$db_port" \
    --user="$db_user" \
    "$db_name" < "$backup_file_real"

if [[ -n "$storage_backup_real" ]]; then
    printf '[rollback] Restoring storage archive from %s.\n' "$storage_backup_real"
    tar -xzf "$storage_backup_real" -C "$PROJECT_ROOT"
fi

php artisan migrate:status >/dev/null
php artisan storage:link
[[ -L public/storage ]] || fail 'public/storage is not a symbolic link after rollback.'
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
maintenance_enabled=0

health_response_file="$(mktemp)"
health_status="$(curl --silent --show-error --max-time 30 -o "$health_response_file" -w '%{http_code}' "$health_url" || true)"
if [[ "$health_status" == '200' ]] && grep -Eq '"status"[[:space:]]*:[[:space:]]*"ok"' "$health_response_file"; then
    rm -f -- "$health_response_file"
elif [[ "$health_status" == '404' ]]; then
    rm -f -- "$health_response_file"
    php artisan about >/dev/null || fail 'Legacy rollback commit has no health route and could not boot with artisan about.'
else
    rm -f -- "$health_response_file"
    fail 'Health endpoint did not report status=ok after rollback.'
fi

trap - EXIT
printf '[rollback] Completed at %s.\n' "$rollback_commit"
