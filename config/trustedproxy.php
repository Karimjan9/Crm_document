<?php

return [
    // Comma-separated proxy IPs/CIDRs. Leave empty when the app is directly exposed.
    // Do not use '*' unless the application is unreachable except through a trusted proxy.
    'proxies' => env('TRUSTED_PROXIES'),
];
