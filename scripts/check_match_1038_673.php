<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PurchaseInvoice;
use App\Models\Proforma;

$invoiceId = 1038;
$proformaId = 673;

$purchase = PurchaseInvoice::find($invoiceId);
$proforma = Proforma::with('fornitore')->find($proformaId);

if (! $purchase) {
    echo "PurchaseInvoice {$invoiceId} not found\n";
    exit(1);
}
if (! $proforma) {
    echo "Proforma {$proformaId} not found\n";
    exit(1);
}

function toFloat($val) {
    if ($val === null) return 0.0;
    return (float) $val;
}

$purchaseAmount = toFloat($purchase->amount);
$purchaseVat = $purchase->vat_number;
$purchaseDate = $purchase->registration_date ? $purchase->registration_date->toDateString() : null;

// Proforma totale uses accessor
$proformaTotal = toFloat($proforma->totale);
$proformaVat = $proforma->vat_number;
$proformaSent = $proforma->sended_at ? $proforma->sended_at->toDateString() : null;

echo "PurchaseInvoice #{$invoiceId}\n";
echo "- amount: {$purchaseAmount}\n";
echo "- vat_number: {$purchaseVat}\n";
echo "- registration_date: {$purchaseDate}\n\n";

echo "Proforma #{$proformaId}\n";
echo "- totale: {$proformaTotal}\n";
echo "- vat_number: {$proformaVat}\n";
echo "- sended_at: {$proformaSent}\n";
echo "- invoiceable_id: " . ($proforma->invoiceable_id ?? 'NULL') . "\n";
echo "- invoiceable_type: " . ($proforma->invoiceable_type ?? 'NULL') . "\n\n";

$vatMatch = false;
if ($purchaseVat && $proformaVat && trim(str_replace(' ', '', $purchaseVat)) === trim(str_replace(' ', '', $proformaVat))) {
    $vatMatch = true;
}
// If purchase model doesn't have a working relationship, fall back to comparing vat_number only


$dateMatch = false;
if ($purchaseDate && $proformaSent) {
    $dateMatch = ($proforma->sended_at <= $purchase->registration_date);
}

$amountsMatch = abs($purchaseAmount - $proformaTotal) <= 0.01;

echo "Checks:\n";
echo "- VAT match: " . ($vatMatch ? 'YES' : 'NO') . "\n";
echo "- Date (proforma.sended_at <= invoice.registration_date): " . ($dateMatch ? 'YES' : 'NO') . "\n";
echo "- Amounts match (|purchase - proforma| <= 0.01): " . ($amountsMatch ? 'YES' : 'NO') . "\n\n";

if ($vatMatch && $dateMatch && $amountsMatch && is_null($proforma->invoiceable_id)) {
    echo "RESULT: WOULD MATCH - conditions satisfied.\n";
} else {
    echo "RESULT: WOULD NOT MATCH - conditions not all satisfied.\n";
}

// Print brief proforma and purchase arrays for debugging

echo "\nDebug dump:\n";
print_r([
    'purchase' => [
        'id' => $purchase->id,
        'amount' => $purchaseAmount,
        'vat_number' => $purchaseVat,
        'registration_date' => $purchaseDate,
    ],
    'proforma' => [
        'id' => $proforma->id,
        'totale' => $proformaTotal,
        'vat_number' => $proformaVat,
        'sended_at' => $proformaSent,
        'invoiceable_id' => $proforma->invoiceable_id,
    ],
]);
