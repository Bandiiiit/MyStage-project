<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= $invoice->id ?></title>
</head>
<body>
    <h1>Invoice for <?= $invoice->client_name ?></h1>
    <p>Transaction Type: <?= $invoice->transaction_type == 'L' ? 'Local' : 'International' ?></p>
    <p>Amount: <?= number_format($invoice->amount, 2) ?> DH</p>
    <p>Commission HT: <?= number_format($commission_ht, 2) ?> DH</p>
    <p>TVA (10%): <?= number_format($tva, 2) ?> DH</p>
    <p>Total TTC: <?= number_format($total_ttc, 2) ?> DH</p>
    <p><strong>Solde Net: <?= number_format($solde_net, 2) ?> DH</strong></p>
</body>
</html>
