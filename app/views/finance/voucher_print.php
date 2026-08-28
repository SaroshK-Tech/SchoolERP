<?php /** @var array $voucher @var array $items */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Voucher #<?= e($voucher['voucher_no']) ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, Helvetica, sans-serif; color: #111; background: #f1f5f9; }
  .page { width: 720px; margin: 24px auto; background: #fff; padding: 32px 36px; box-shadow: 0 2px 12px rgba(0,0,0,.12); }
  .toolbar { width:720px; margin: 0 auto 12px; text-align:right; }
  .toolbar .btn { margin-left:8px; font:600 13px Arial; padding:8px 16px; border:0; border-radius:6px; cursor:pointer;
                  background:#0f172a; color:#fff; text-decoration:none; }
  .toolbar .btn.back { background:#64748b; }
  .head { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #0f172a; padding-bottom:14px; }
  .brand { font-size:22px; font-weight:800; letter-spacing:.5px; }
  .brand small { display:block; font-size:11px; font-weight:500; color:#64748b; letter-spacing:2px; }
  .doc-label { text-align:right; }
  .doc-label .title { font-size:20px; font-weight:800; }
  .doc-label .no { font-size:13px; color:#0f172a; font-weight:700; margin-top:2px; }
  .meta { display:flex; justify-content:space-between; gap:24px; margin-top:18px; }
  .meta table { width:100%; border-collapse:collapse; font-size:13px; }
  .meta td { padding:4px 8px; }
  .meta .k { color:#64748b; width:110px; }
  .meta .v { font-weight:600; }
  .bill, .bill-wrap { width:100%; }
  .bill h3 { font-size:13px; margin:20px 0 6px; text-transform:uppercase; letter-spacing:1px; color:#475569; }
  .items { width:100%; border-collapse:collapse; font-size:13px; }
  .items th { background:#0f172a; color:#fff; text-align:left; padding:8px 10px; }
  .items td { padding:8px 10px; border-bottom:1px solid #e2e8f0; }
  .items .num, .items th.num { text-align:right; }
  .total-row td { font-size:15px; font-weight:800; background:#f8fafc; border-bottom:2px solid #0f172a; }
  .due { margin-top:18px; padding:10px 14px; border:1px dashed #94a3b8; font-size:13px; color:#334155; }
  .footer { margin-top:26px; border-top:1px solid #e2e8f0; padding-top:12px; display:flex; justify-content:space-between; font-size:12px; color:#64748b; }
  .sign { margin-top:34px; display:flex; justify-content:space-between; font-size:12px; color:#334155; }
  .sign div { width:200px; }
  .sign .line { border-top:1px dotted #94a3b8; margin-top:34px; padding-top:6px; text-align:center; color:#64748b; }
  @media print { body{background:#fff;} .page{box-shadow:none; margin:0 auto;} .toolbar{display:none;} .sign .line{margin-top:40px;} }
</style>
</head>
<body>
  <div class="toolbar">
    <a class="btn back" href="<?= e(App::url('finance/vouchers')) ?>">← Back</a>
    <button class="btn" onclick="window.print()">Print Voucher</button>
  </div>

  <div class="page">
    <div class="head">
      <div class="brand"><?= e(App::config('app.name', 'SchoolERP')) ?>
        <small>FEE VOUCHER / DEMAND NOTE</small>
      </div>
      <div class="doc-label">
        <div class="title">Fee Voucher</div>
        <div class="no"># <?= e($voucher['voucher_no']) ?></div>
      </div>
    </div>

    <div class="meta">
      <table>
        <tr><td class="k">Student</td><td class="v"><?= e($voucher['student_name']) ?></td></tr>
        <tr><td class="k">Admission No.</td><td class="v"><?= e($voucher['admission_no']) ?></td></tr>
        <tr><td class="k">Class / Section</td><td class="v"><?= e($voucher['class_name']) ?><?= $voucher['section_name'] ? ' - ' . e($voucher['section_name']) : '' ?></td></tr>
        <?php if ($voucher['guardian_name']): ?>
        <tr><td class="k">Guardian</td><td class="v"><?= e($voucher['guardian_name']) ?> <?= $voucher['guardian_phone'] ? '(' . e($voucher['guardian_phone']) . ')' : '' ?></td></tr>
        <?php endif; ?>
      </table>
      <table>
        <tr><td class="k">Session</td><td class="v"><?= e($voucher['session_name'] ?: '—') ?></td></tr>
        <tr><td class="k">Label</td><td class="v"><?= e($voucher['label'] ?: '—') ?></td></tr>
        <tr><td class="k">Issue Date</td><td class="v"><?= e(fmt_date($voucher['issue_date'])) ?></td></tr>
        <tr><td class="k">Due Date</td><td class="v"><?= e(fmt_date($voucher['due_date'])) ?: '—' ?></td></tr>
      </table>
    </div>

    <div class="bill">
      <h3>Fee Breakdown</h3>
      <table class="items">
        <thead><tr><th>Description</th><th>Frequency</th><th class="num">Amount</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= e($it['fee_type']) ?></td>
            <td><?= e(ucfirst($it['frequency'])) ?></td>
            <td class="num"><?= e(money($it['amount'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr class="total-row"><td colspan="2">Total Amount Payable</td><td class="num"><?= e(money($voucher['total_amount'])) ?></td></tr>
        </tbody>
      </table>
    </div>

    <div class="due"><strong>Due Date:</strong> <?= e(fmt_date($voucher['due_date'])) ?: 'As per school policy' ?> &nbsp;•&nbsp; Kindly pay the above amount before the due date. Late payments incur a fine as per school policy.</div>

    <div class="sign">
      <div class="line">Student / Guardian Signature</div>
      <div class="line">Accountant / Authorised Signatory</div>
    </div>

    <div class="footer">
      <span><?= e(App::config('app.name', 'SchoolERP')) ?></span>
      <span>Generated <?= e(date('M j, Y g:i A')) ?></span>
    </div>
  </div>
</body>
</html>
