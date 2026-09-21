<?php

use App\Models\ClientsModel;
use App\Models\DocumentsModel;
use App\Models\ExpenseAdminModel;
use App\Models\FilialModel;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerApiKey;
use App\Models\PaymentRefund;
use App\Models\PaymentsModel;
use App\Models\PriceTariff;
use App\Models\User;

return [
    'audit' => [
        'enabled' => env('AUDIT_LOG_ENABLED', true),
        'models' => [
            User::class, ClientsModel::class, DocumentsModel::class,
            Order::class, PaymentsModel::class, PaymentRefund::class,
            ExpenseAdminModel::class, Partner::class, PartnerApiKey::class,
            PriceTariff::class, FilialModel::class,
        ],
    ],

    'alerts' => [
        'log_channel' => env('SECURITY_ALERT_LOG_CHANNEL', 'stack'),
        'forbidden_threshold' => (int) env('SECURITY_FORBIDDEN_THRESHOLD', 10),
        'rate_limit_threshold' => (int) env('SECURITY_RATE_LIMIT_THRESHOLD', 5),
        'error_threshold' => (int) env('SECURITY_ERROR_THRESHOLD', 10),
        'login_alert_threshold' => (int) env('SECURITY_LOGIN_ALERT_THRESHOLD', 10),
        'large_discount_percent' => (float) env('SECURITY_LARGE_DISCOUNT_PERCENT', 30),
        'large_refund_amount' => (float) env('SECURITY_LARGE_REFUND_AMOUNT', 1000000),
        'telegram' => [
            'endpoint' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
            'bot_token' => env('SECURITY_ALERT_TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('SECURITY_ALERT_TELEGRAM_CHAT_ID'),
            'timeout' => (int) env('SECURITY_ALERT_TIMEOUT', 10),
        ],
    ],

    'files' => [
        'scan_enabled' => env('FILE_SCAN_ENABLED', env('APP_ENV') === 'production'),
        'scan_required' => env('FILE_SCAN_REQUIRED', env('APP_ENV') === 'production'),
        'clamav_binary' => env('CLAMAV_BINARY', 'clamscan'),
        'allow_test_fakes' => true,
    ],

    'backups' => [
        'encrypt' => env('BACKUP_ENCRYPTION_ENABLED', env('APP_ENV') === 'production'),
        'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),
        'restore_test_database' => env('BACKUP_RESTORE_TEST_DATABASE'),
    ],

    'watermark' => [
        // qpdf is used with Dompdf to visibly stamp every PDF page at download time.
        'qpdf_binary' => env('QPDF_BINARY'),
    ],

    'login' => [
        // Five incorrect credentials lock that login/IP pair for 15 minutes.
        'max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
        'lockout_seconds' => (int) env('LOGIN_LOCKOUT_SECONDS', 900),
        // Limits credential stuffing against many account names from one IP.
        'ip_max_attempts' => (int) env('LOGIN_IP_MAX_ATTEMPTS', 25),
    ],
];
