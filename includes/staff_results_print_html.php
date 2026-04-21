<?php

declare(strict_types=1);

function staff_report_h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function staff_pdf_ip(?string $ip): string
{
    if ($ip === null || $ip === '') {
        return '—';
    }

    return staff_report_h($ip);
}

/**
 * @param list<array<string, mixed>> $tallies
 * @param list<array<string, mixed>> $votedRows
 * @param list<array<string, mixed>> $notVotedRows
 */
function staff_build_results_report_html(
    array $election,
    array $tallies,
    int $voteTotal,
    int $studentTotal,
    float $turnoutPct,
    string $status,
    string $generatedAt,
    string $staffLabel,
    string $backHref,
    string $backLinkLabel,
    array $votedRows,
    array $notVotedRows
): string {
    $title = staff_report_h((string) $election['title']);
    $desc = trim((string) ($election['description'] ?? ''));
    $descHtml = $desc !== '' ? '<p class="desc">' . nl2br(staff_report_h($desc)) . '</p>' : '';

    $opens = staff_report_h(date('M j, Y g:i A', strtotime((string) $election['opens_at'])));
    $closes = staff_report_h(date('M j, Y g:i A', strtotime((string) $election['closes_at'])));
    $statusLabel = staff_report_h(ucfirst($status));

    $turnoutStr = staff_report_h((string) $turnoutPct);

    $rows = '';
    if ($voteTotal === 0) {
        $rows = '<tr><td colspan="2" class="empty-votes">No votes recorded for this election yet.</td></tr>';
    } else {
        foreach ($tallies as $t) {
            $v = (int) $t['votes'];
            $label = staff_report_h((string) $t['label']);
            $pct = $voteTotal > 0 ? round(100 * $v / $voteTotal, 1) : 0.0;
            $pctStr = staff_report_h((string) $pct);
            $rest = max(0.0, round(100 - $pct, 1));

            $rows .= <<<HTML
<tr class="tally-block">
  <td class="tally-label" colspan="2">
    <table class="tally-head" width="100%"><tr>
      <td class="opt-name">{$label}</td>
      <td class="opt-num">{$v} votes · {$pctStr}%</td>
    </tr></table>
    <table class="bar-wrap" width="100%" cellpadding="0" cellspacing="0"><tr>
      <td class="bar-on" style="width: {$pctStr}%;">&nbsp;</td>
      <td class="bar-off" style="width: {$rest}%;">&nbsp;</td>
    </tr></table>
  </td>
</tr>
HTML;
        }
    }

    $staffEsc = staff_report_h($staffLabel);
    $backHrefEsc = staff_report_h($backHref);
    $backLabelEsc = staff_report_h($backLinkLabel);

    $votedTable = '';
    if (count($votedRows) === 0) {
        $votedTable = '<p class="voter-empty">No votes recorded for this election yet.</p>';
    } else {
        $vr = '';
        foreach ($votedRows as $row) {
            $n = staff_report_h((string) $row['name']);
            $em = staff_report_h((string) $row['email']);
            $rip = staff_pdf_ip(isset($row['registration_ip']) ? (string) $row['registration_ip'] : null);
            $lip = staff_pdf_ip(isset($row['last_login_ip']) ? (string) $row['last_login_ip'] : null);
            $vt = staff_report_h(date('M j, Y g:i A', strtotime((string) $row['voted_at'])));
            $vr .= "<tr><td>{$n}</td><td>{$em}</td><td class=\"ip-cell\">{$rip}</td><td class=\"ip-cell\">{$lip}</td><td>{$vt}</td></tr>";
        }
        $votedTable = <<<HTML
<table class="pdf-voters">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Registration IP</th>
      <th>Last login IP</th>
      <th>Voted at</th>
    </tr>
  </thead>
  <tbody>{$vr}</tbody>
</table>
HTML;
    }

    $nvTable = '';
    if (count($notVotedRows) === 0) {
        $nvTable = '<p class="voter-empty">Every registered student has submitted a ballot for this election.</p>';
    } else {
        $nr = '';
        foreach ($notVotedRows as $row) {
            $n = staff_report_h((string) $row['name']);
            $em = staff_report_h((string) $row['email']);
            $rip = staff_pdf_ip(isset($row['registration_ip']) ? (string) $row['registration_ip'] : null);
            $lip = staff_pdf_ip(isset($row['last_login_ip']) ? (string) $row['last_login_ip'] : null);
            $nr .= "<tr><td>{$n}</td><td>{$em}</td><td class=\"ip-cell\">{$rip}</td><td class=\"ip-cell\">{$lip}</td></tr>";
        }
        $nvTable = <<<HTML
<table class="pdf-voters">
  <thead>
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Registration IP</th>
      <th>Last login IP</th>
    </tr>
  </thead>
  <tbody>{$nr}</tbody>
</table>
HTML;
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$title} — Results</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --ink: #1e293b;
      --muted: #64748b;
      --accent: #6366f1;
      --hero: #3730a3;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: "Inter", system-ui, -apple-system, sans-serif;
      font-size: 11pt;
      color: var(--ink);
      line-height: 1.45;
      background: #f1f5f9;
    }
    .sheet {
      max-width: 720px;
      margin: 0 auto;
      padding: 1.25rem 1rem 3rem;
    }
    .print-bar {
      position: sticky;
      top: 0;
      z-index: 10;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.75rem 1rem;
      padding: 0.85rem 1rem;
      margin: -1.25rem -1rem 1.25rem;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid #e2e8f0;
    }
    .print-bar a {
      font-weight: 600;
      color: var(--accent);
      text-decoration: none;
    }
    .print-bar a:hover { text-decoration: underline; }
    .print-bar button {
      font: inherit;
      font-weight: 600;
      cursor: pointer;
      padding: 0.55rem 1rem;
      border-radius: 10px;
      border: none;
      color: #fff;
      background: linear-gradient(135deg, var(--accent), #7c3aed);
      box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    }
    .print-bar button:hover { filter: brightness(1.05); }
    .print-hint {
      flex: 1 1 100%;
      margin: 0;
      font-size: 0.8rem;
      color: var(--muted);
    }
    .report-card {
      background: #fff;
      border-radius: 12px;
      padding: 1.5rem 1.35rem 1.75rem;
      box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
    }
    @page { margin: 48px 42px 56px 42px; }
    .hero {
      background: var(--hero);
      color: #fff;
      padding: 22px 24px 20px;
      border-radius: 8px;
      margin: 0 0 20px 0;
    }
    .hero-kicker {
      font-size: 9pt;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      opacity: 0.85;
      margin: 0 0 6px 0;
    }
    .hero h1 {
      font-size: 18pt;
      font-weight: 700;
      margin: 0;
      line-height: 1.25;
    }
    .desc {
      margin: 14px 0 0;
      font-size: 10pt;
      opacity: 0.95;
      line-height: 1.5;
    }
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 18px;
      font-size: 10pt;
    }
    .meta-table td {
      padding: 8px 10px;
      border: 1px solid #e2e8f0;
      vertical-align: top;
    }
    .meta-table td.lbl { width: 22%; background: #f8fafc; font-weight: 700; color: #475569; }
    .status-pill {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 999px;
      font-size: 9pt;
      font-weight: 700;
      text-transform: capitalize;
    }
    .st-open { background: #d1fae5; color: #047857; }
    .st-upcoming { background: #fef3c7; color: #b45309; }
    .st-closed { background: #e0e7ff; color: #4338ca; }

    .stats {
      width: 100%;
      border-collapse: separate;
      border-spacing: 10px 0;
      margin: 0 0 22px 0;
    }
    .stats td {
      width: 33%;
      text-align: center;
      padding: 14px 10px;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      background: #f8fafc;
      vertical-align: middle;
    }
    .stat-val {
      font-size: 20pt;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.1;
    }
    .stat-lbl {
      font-size: 8pt;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #64748b;
      font-weight: 700;
      margin-top: 6px;
    }

    h2 {
      font-size: 11pt;
      color: #334155;
      margin: 0 0 10px 0;
      padding-bottom: 6px;
      border-bottom: 2px solid #c7d2fe;
    }
    .tally-table { width: 100%; border-collapse: collapse; }
    .tally-block td { padding: 0 0 14px 0; vertical-align: top; }
    .tally-head .opt-name { font-weight: 700; color: #0f172a; }
    .tally-head .opt-num {
      text-align: right;
      font-size: 10pt;
      font-weight: 600;
      color: #64748b;
      white-space: nowrap;
    }
    .bar-wrap { margin-top: 6px; border-radius: 6px; overflow: hidden; height: 12px; }
    .bar-on {
      background: linear-gradient(90deg, #6366f1, #9333ea);
      background-color: #6366f1;
      height: 12px;
      font-size: 1px;
      line-height: 1px;
    }
    .bar-off {
      background: #e2e8f0;
      height: 12px;
      font-size: 1px;
      line-height: 1px;
    }
    .empty-votes {
      padding: 12px;
      border: 1px dashed #cbd5e1;
      border-radius: 4px;
      color: #64748b;
      font-style: italic;
      text-align: center;
    }
    h3 {
      font-size: 10pt;
      color: #475569;
      margin: 18px 0 8px 0;
      font-weight: 700;
    }
    .voter-note {
      font-size: 9pt;
      color: #64748b;
      margin: 0 0 12px 0;
      line-height: 1.4;
    }
    .voter-empty {
      font-size: 9.5pt;
      color: #64748b;
      font-style: italic;
      margin: 0 0 14px 0;
    }
    .pdf-voters {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.5pt;
      margin: 0 0 16px 0;
    }
    .pdf-voters th,
    .pdf-voters td {
      padding: 6px 8px;
      border: 1px solid #e2e8f0;
      text-align: left;
      vertical-align: top;
    }
    .pdf-voters th {
      background: #f8fafc;
      font-weight: 700;
      color: #475569;
      font-size: 7.5pt;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .pdf-voters .ip-cell {
      font-variant-numeric: tabular-nums;
      white-space: nowrap;
      font-size: 8pt;
      color: #334155;
    }
    .footer {
      margin-top: 28px;
      padding-top: 12px;
      border-top: 1px solid #e2e8f0;
      font-size: 8.5pt;
      color: #94a3b8;
    }
    .footer strong { color: #64748b; }

    @media print {
      body { background: #fff; }
      .sheet { padding: 0; max-width: none; }
      .print-bar { display: none !important; }
      .report-card {
        box-shadow: none;
        border-radius: 0;
        padding: 0;
      }
    }
  </style>
</head>
<body>
  <div class="sheet">
    <div class="print-bar">
      <a href="{$backHrefEsc}">{$backLabelEsc}</a>
      <button type="button" onclick="window.print()">Print or save as PDF</button>
      <p class="print-hint">In the print dialog, choose <strong>Save as PDF</strong> (or “Microsoft Print to PDF”) to download a file — no extra software required.</p>
    </div>

    <div class="report-card">
      <div class="hero">
        <p class="hero-kicker">Election results</p>
        <h1>{$title}</h1>
        {$descHtml}
      </div>

      <table class="meta-table">
        <tr>
          <td class="lbl">Status</td>
          <td><span class="status-pill st-{$status}">{$statusLabel}</span></td>
        </tr>
        <tr>
          <td class="lbl">Voting window</td>
          <td>{$opens} — {$closes}</td>
        </tr>
      </table>

      <table class="stats">
        <tr>
          <td>
            <div class="stat-val">{$voteTotal}</div>
            <div class="stat-lbl">Votes cast</div>
          </td>
          <td>
            <div class="stat-val">{$studentTotal}</div>
            <div class="stat-lbl">Registered students</div>
          </td>
          <td>
            <div class="stat-val">{$turnoutStr}%</div>
            <div class="stat-lbl">Turnout</div>
          </td>
        </tr>
      </table>

      <h2>Ballot tallies</h2>
      <table class="tally-table">
        {$rows}
      </table>

      <h2>Voter participation</h2>
      <p class="voter-note">Registration and last-login IP addresses are shown as stored in the system (best-effort from the web server).</p>

      <h3>Voted</h3>
      {$votedTable}

      <h3>Not voted</h3>
      {$nvTable}

      <div class="footer">
        <strong>Generated</strong> {$generatedAt} · <strong>Prepared by</strong> {$staffEsc}<br>
        Official tally for staff use — keep consistent with the live system when reporting.
      </div>
    </div>
  </div>
</body>
</html>
HTML;
}
