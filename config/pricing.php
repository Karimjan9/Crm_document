<?php

return [
    'default_currency' => env('PRICING_CURRENCY', 'UZS'),
    'discount_approval_percent' => (float) env('PRICING_DISCOUNT_APPROVAL_PERCENT', 15),
    'discount_approval_amount' => (float) env('PRICING_DISCOUNT_APPROVAL_AMOUNT', 500000),
    'default_variant' => 'standard',
];
