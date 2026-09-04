<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
admin_require_login();

$pdo = get_db();
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    if (in_array($status, ['Under Review', 'Approved', 'Rejected'], true)) {
        $stmt = $pdo->prepare('UPDATE registrations SET status = :s WHERE id = :id');
        $stmt->execute(['s' => $status, 'id' => $id]);
    }
    header('Location: view.php?id=' . $id);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM registrations WHERE id = :id');
$stmt->execute(['id' => $id]);
$r = $stmt->fetch();

if (!$r) {
    http_response_code(404);
    echo 'Registration not found.';
    exit;
}

function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function yn(?string $v, ?string $detail): string {
    if ($v === 'Yes' && $detail) {
        return 'Yes — ' . h($detail);
    }
    return h($v);
}
?>
<!doctype html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <title><?= h($r['child_name']) ?> | The Grove Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body { font-family: "Open Sans", Arial, sans-serif; margin: 0; background: #f7f6fa; color: #333; }
    main { max-width: 900px; margin: 30px auto; padding: 0 20px; }
    .admin-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .admin-header .brand { display: flex; align-items: center; gap: 12px; }
    .admin-header .brand img { height: 100px; width: auto; }
    .admin-header a.logout-btn { color: #fff; text-decoration: none; font-size: 14px; background: #6c3fa0; padding: 7px 18px; border-radius: 20px; }
    .admin-header a.logout-btn:hover { background: #5a3385; }
    .card { background: #fff; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 20px; }
    .card h2 { margin-top: 0; color: #6c3fa0; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 8px; }
    .row { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 10px; }
    .field { flex: 1 1 260px; }
    .field .label { font-size: 12px; text-transform: uppercase; color: #888; font-weight: 700; }
    .field .value { font-size: 15px; word-break: break-word; }
    .status-form { display: flex; flex-wrap: wrap; gap: 10px; }
    .status-form select { padding: 6px 10px; border-radius: 4px; border: 1px solid #bbb; }
    .status-form button { padding: 6px 14px; background: #6c3fa0; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    .back-link { display: inline-block; margin-bottom: 16px; color: #6c3fa0; text-decoration: none; }

    @media (max-width: 600px) {
      main { padding: 0 12px; }
      .card { padding: 16px; }
      .admin-header .brand img { height: 38px; }
    }
  </style>
</head>
<body>
  <main>
    <div class="admin-header">
      <div class="brand">
        <img src="../assets/images/06/Logo-Final.png" alt="The Grove">
      </div>
      <span style="color: #6c3fa0; font-weight: 700; font-size: 28px;">Registration Detail</span>
      <a class="logout-btn" href="logout.php">Log out</a>
    </div>

    
    <div class="card">
      <h2>Status</h2>
      <form class="status-form" method="post" action="view.php?id=<?= (int)$r['id'] ?>">
        <select name="status">
          <?php foreach (['Under Review', 'Approved', 'Rejected'] as $s): ?>
            <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Update Status</button>
      </form>
    </div>

    <div class="card">
      <h2>Child</h2>
      <div class="row">
        <div class="field"><div class="label">Name</div><div class="value"><?= h($r['child_name']) ?></div></div>
        <div class="field"><div class="label">D.O.B.</div><div class="value"><?= h($r['child_dob']) ?></div></div>
        <div class="field"><div class="label">Gender</div><div class="value"><?= h($r['gender']) ?></div></div>
        <div class="field"><div class="label">B-Form / Birth Certificate</div><div class="value"><?= h($r['birth_certificate']) ?></div></div>
      </div>
    </div>

    <div class="card">
      <h2>Parents / Guardians</h2>
      <div class="row">
        <div class="field"><div class="label">Parent/Guardian 1</div><div class="value"><?= h($r['parent1_name']) ?></div></div>
        <div class="field"><div class="label">Mobile</div><div class="value"><?= h($r['parent1_mobile']) ?></div></div>
        <div class="field"><div class="label">Email</div><div class="value"><?= h($r['parent1_email']) ?></div></div>
      </div>
      <div class="row">
        <div class="field"><div class="label">Parent/Guardian 2</div><div class="value"><?= h($r['parent2_name']) ?: '—' ?></div></div>
        <div class="field"><div class="label">Mobile</div><div class="value"><?= h($r['parent2_mobile']) ?: '—' ?></div></div>
        <div class="field"><div class="label">Email</div><div class="value"><?= h($r['parent2_email']) ?: '—' ?></div></div>
      </div>
      <div class="row">
        <div class="field"><div class="label">Home Address</div><div class="value"><?= h($r['home_address']) ?></div></div>
        <div class="field"><div class="label">Child lives with</div><div class="value"><?= h($r['child_lives_with']) ?></div></div>
      </div>
      <div class="row">
        <div class="field"><div class="label">First language</div><div class="value"><?= h($r['first_language']) ?: '—' ?></div></div>
        <div class="field"><div class="label">Religion</div><div class="value"><?= h($r['religion']) ?: '—' ?></div></div>
      </div>
    </div>

    <div class="card">
      <h2>Health &amp; Care</h2>
      <div class="row">
        <div class="field"><div class="label">Allergies</div><div class="value"><?= yn($r['has_allergies'], $r['allergies_detail']) ?></div></div>
        <div class="field"><div class="label">Medical condition / medication</div><div class="value"><?= yn($r['has_medical_condition'], $r['medical_detail']) ?></div></div>
      </div>
      <div class="row">
        <div class="field"><div class="label">Additional care / developmental needs</div><div class="value"><?= yn($r['has_additional_needs'], $r['additional_needs_detail']) ?></div></div>
        <div class="field"><div class="label">Dietary requirements</div><div class="value"><?= yn($r['has_dietary_requirements'], $r['dietary_detail']) ?></div></div>
      </div>
    </div>

    <div class="card">
      <h2>Attendance</h2>
      <div class="row">
        <div class="field"><div class="label">Package</div><div class="value"><?= h($r['package']) ?></div></div>
        <div class="field"><div class="label">Days Required</div><div class="value"><?= h($r['days_required']) ?></div></div>
      </div>
    </div>

    <div class="card">
      <h2>Authorized Pick-up Persons</h2>
      <?php for ($i = 1; $i <= 3; $i++): $name = $r["collector{$i}_name"]; if (!$name) continue; ?>
        <div class="row">
          <div class="field"><div class="label">Name</div><div class="value"><?= h($name) ?></div></div>
          <div class="field"><div class="label">Relationship</div><div class="value"><?= h($r["collector{$i}_relationship"]) ?></div></div>
          <div class="field"><div class="label">Mobile</div><div class="value"><?= h($r["collector{$i}_mobile"]) ?></div></div>
          <div class="field"><div class="label">Regular collector</div><div class="value"><?= $r["collector{$i}_regular"] ? 'Yes' : 'No' ?></div></div>
        </div>
      <?php endfor; ?>
      <?php if (!$r['collector1_name'] && !$r['collector2_name'] && !$r['collector3_name']): ?>
        <p>None provided.</p>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2>Emergency Contact</h2>
      <div class="row">
        <div class="field"><div class="label">Name</div><div class="value"><?= h($r['emergency_name']) ?></div></div>
        <div class="field"><div class="label">Relationship</div><div class="value"><?= h($r['emergency_relationship']) ?></div></div>
        <div class="field"><div class="label">Mobile</div><div class="value"><?= h($r['emergency_mobile']) ?></div></div>
      </div>
      <div class="row">
        <div class="field"><div class="label">Important family / social information</div><div class="value"><?= yn($r['has_important_info'], $r['important_info_detail']) ?></div></div>
      </div>
    </div>

    <div class="card">
      <h2>Consent</h2>
      <div class="row">
        <div class="field"><div class="label">Basic first aid</div><div class="value"><?= $r['consent_first_aid'] ? 'Consented' : 'Not consented' ?></div></div>
        <div class="field"><div class="label">Indoor/outdoor activities</div><div class="value"><?= $r['consent_activities'] ? 'Consented' : 'Not consented' ?></div></div>
        <div class="field"><div class="label">Routine communication</div><div class="value"><?= $r['consent_communication'] ? 'Consented' : 'Not consented' ?></div></div>
        <div class="field"><div class="label">Photos/videos</div><div class="value"><?= $r['consent_photos'] ? 'Consented' : 'Not consented' ?></div></div>
      </div>
      <div class="row">
        <div class="field"><div class="label">Signed by</div><div class="value"><?= h($r['signed_name']) ?></div></div>
        <div class="field"><div class="label">Date</div><div class="value"><?= h($r['signed_date']) ?></div></div>
        <div class="field"><div class="label">Submitted at</div><div class="value"><?= h($r['submitted_at']) ?></div></div>
      </div>
    </div>
    <a class="back-link" href="dashboard.php">&larr; Back to all applications</a>
  </main>
</body>
</html>
