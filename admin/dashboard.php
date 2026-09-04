<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
admin_require_login();

$pdo = get_db();

// Quick inline status update from the table.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    if (in_array($status, ['Under Review', 'Approved', 'Rejected'], true)) {
        $stmt = $pdo->prepare('UPDATE registrations SET status = :s WHERE id = :id');
        $stmt->execute(['s' => $status, 'id' => $id]);
    }
    header('Location: dashboard.php');
    exit;
}

$rows = $pdo->query('SELECT id, child_name, parent1_name, parent1_mobile, parent1_email, package, status, submitted_at FROM registrations ORDER BY submitted_at DESC')->fetchAll();

$counts = ['Under Review' => 0, 'Approved' => 0, 'Rejected' => 0];
foreach ($pdo->query('SELECT status, COUNT(*) AS c FROM registrations GROUP BY status') as $row) {
    $counts[$row['status']] = (int)$row['c'];
}
$total = array_sum($counts);

function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <title>Registrations | The Grove Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../assets/css/bootstrap.css" media="all" />
  <link rel="stylesheet" href="../assets/css/datatables.min.css" media="all" />
  <link rel="icon" href="../assets/images/06/Favicon.png" sizes="32x32" />
  <style>
    body { font-family: "Open Sans", Arial, sans-serif; background: #f7f6fa; color: #333; }
    main { padding: 24px; max-width: 1300px; margin: 0 auto; }
    .admin-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .admin-header .brand { display: flex; align-items: center; gap: 12px; }
    .admin-header .brand img { height: 100px; width: auto; }
    .admin-header .brand span { color: #6c3fa0; font-weight: 700; font-size: 18px; }
    .admin-header a.logout-btn { color: #fff; text-decoration: none; font-size: 14px; background: #6c3fa0; padding: 7px 18px; border-radius: 20px; }
    .admin-header a.logout-btn:hover { background: #5a3385; }
    .stat-card { border-radius: 10px; padding: 20px; color: #fff; box-shadow: 0 4px 14px rgba(0,0,0,0.08); }
    .stat-card .stat-figure { font-size: 34px; font-weight: 800; line-height: 1; margin-bottom: 6px; }
    .stat-card .stat-label { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; }
    .stat-total { background: linear-gradient(120deg, #6c3fa0, #9c5fd6); }
    .stat-approved { background: linear-gradient(120deg, #2f9e44, #51cf66); }
    .stat-rejected { background: linear-gradient(120deg, #c92a2a, #ff6b6b); }
    .stat-review { background: linear-gradient(120deg, #e08e0b, #ffc94a); }
    .table-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); overflow-x: auto; }
    table.dataTable { width: 100% !important; }
    .status-badge { padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; display: inline-block; }
    .status-Under-Review { background: #fff3cd; color: #8a6d1a; }
    .status-Approved { background: #d9f2d9; color: #256029; }
    .status-Rejected { background: #f8d7da; color: #7a1f1f; }
    .status-select { font-size: 13px; padding: 3px 6px; }
    a.view-link { color: #6c3fa0; font-weight: 700; text-decoration: none; }

    @media (max-width: 767px) {
      main { padding: 14px; }
      .admin-header .brand img { height: 38px; }
      .admin-header .brand span { font-size: 15px; }
      .stat-card { padding: 14px; }
      .stat-card .stat-figure { font-size: 26px; }
    }
  </style>
</head>
<body>
  <main>
    <div class="admin-header">
      <div class="brand">
        <img src="../assets/images/06/Logo-Final.png" alt="The Grove">
      </div>
      <span style="color: #6c3fa0; font-weight: 700; font-size: 28px;">Registration Applications</span>
      <a class="logout-btn" href="logout.php">Log out</a>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="stat-card stat-total">
          <div class="stat-figure"><?= $total ?></div>
          <div class="stat-label">Total Applications</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card stat-approved">
          <div class="stat-figure"><?= $counts['Approved'] ?></div>
          <div class="stat-label">Approved</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card stat-rejected">
          <div class="stat-figure"><?= $counts['Rejected'] ?></div>
          <div class="stat-label">Rejected</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card stat-review">
          <div class="stat-figure"><?= $counts['Under Review'] ?></div>
          <div class="stat-label">Under Review</div>
        </div>
      </div>
    </div>

    <div class="table-card">
      <table id="regTable" class="table table-striped table-hover align-middle" style="width:100%">
        <thead>
          <tr>
            <th>Child</th>
            <th>Parent/Guardian 1</th>
            <th>Mobile</th>
            <th>Email</th>
            <th>Package</th>
            <th>Status</th>
            <th>Submitted</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= h($r['child_name']) ?></td>
              <td><?= h($r['parent1_name']) ?></td>
              <td><?= h($r['parent1_mobile']) ?></td>
              <td><?= h($r['parent1_email']) ?></td>
              <td><?= h($r['package']) ?></td>
              <td>
                <form method="post" action="dashboard.php" class="d-flex align-items-center gap-1">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <select name="status" class="form-select status-select" onchange="this.form.submit()">
                    <?php foreach (['Under Review', 'Approved', 'Rejected'] as $s): ?>
                      <option value="<?= $s ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td data-order="<?= h($r['submitted_at']) ?>"><?= h($r['submitted_at']) ?></td>
              <td><a class="view-link" href="view.php?id=<?= (int)$r['id'] ?>">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script src="../assets/js/jquery.js"></script>
  <script src="../assets/js/datatables.min.js"></script>
  <script>
    jQuery(function ($) {
      $('#regTable').DataTable({
        order: [[6, 'desc']],
        responsive: false,
        pageLength: 10,
        language: { search: 'Search applications:' }
      });
    });
  </script>
</body>
</html>
