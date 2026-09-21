<?php

return [
    'api_key' => env('CRM_BOT_API_KEY'),
    'webhook_url' => env('CRM_BOT_WEBHOOK_URL'),
    'webhook_secret' => env('CRM_BOT_WEBHOOK_SECRET'),
    'timeout' => (int) env('CRM_BOT_TIMEOUT', 15),
    'default_filial_id' => env('CRM_BOT_DEFAULT_FILIAL_ID'),
    'default_assignee_id' => env('CRM_BOT_DEFAULT_ASSIGNEE_ID'),
    'response_minutes' => (int) env('CRM_BOT_RESPONSE_MINUTES', 15),
    'file_retention_days' => (int) env('CRM_BOT_FILE_RETENTION_DAYS', 365),
];
