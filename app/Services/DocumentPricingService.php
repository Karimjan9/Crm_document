<?php

namespace App\Services;

use App\Models\DocumentsModel;

class DocumentPricingService
{
    public function subtotal(DocumentsModel $document): float
    {
        $snapshotLines = data_get($document->pricing_snapshot, 'line_items', []);
        if (is_array($snapshotLines) && $snapshotLines !== []) {
            return round((float) collect($snapshotLines)
                ->reject(fn (array $line): bool => ($line['line_type'] ?? null) === 'discount')
                ->sum(fn (array $line): float => (float) ($line['total_price'] ?? 0)), 2);
        }

        return round((float) ($document->service_price ?? 0) + (float) ($document->addons_total_price ?? 0), 2);
    }

    public function discount(DocumentsModel $document): float
    {
        $snapshotDiscount = data_get($document->pricing_snapshot, 'discount_amount');
        if ($snapshotDiscount !== null) {
            return round(max((float) $snapshotDiscount, 0), 2);
        }

        return round(max($this->subtotal($document) - (float) ($document->final_price ?? 0), 0), 2);
    }

    public function finalPrice(float $subtotal, float $discount): float
    {
        return round(max($subtotal - max($discount, 0), 0), 2);
    }
}
