<?php
$items = [
    ['finance', 'Overview', 'Overview'],
    ['finance/fees', 'Fee Management', 'Fees'],
    ['finance/fee-payments', 'Fee Payments', 'Payments'],
    ['finance/payroll', 'Payroll', 'Payroll'],
    ['finance/petty', 'Petty Income & Expense', 'Petty'],
];
$activePath = '/' . ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
?>
<div class="card" style="margin-bottom:20px;">
  <div class="card-body" style="padding:8px 12px;">
    <div class="row" style="gap:6px;">
      <?php foreach ($items as [$route, $label]): ?>
        <?php $prefix = '/' . $route; $is = $activePath === $prefix || str_starts_with($activePath, $prefix . '/'); ?>
        <a href="<?= e(App::url($route)) ?>" class="btn <?= $is ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
