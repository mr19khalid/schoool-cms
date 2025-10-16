<?php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch classes for filter dropdown
$classQuery = $pdo->query("SELECT id, name, section FROM classes ORDER BY name ASC");
$classes = $classQuery->fetchAll();

// Filters
$class_id = $_GET['class_id'] ?? '';
$search = $_GET['search'] ?? '';

$query = "
    SELECT s.*, c.name AS class_name, c.section AS class_section 
    FROM students s 
    LEFT JOIN classes c ON s.class_id = c.id 
    WHERE 1=1
";

$params = [];
if ($class_id) {
    $query .= " AND s.class_id = :class_id";
    $params['class_id'] = $class_id;
}
if ($search) {
    $query .= " AND (s.name LIKE :search OR s.roll_no LIKE :search)";
    $params['search'] = "%$search%";
}

$query .= " ORDER BY s.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Student Reports - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #f5f7fa, #e4e8f0);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
    }
    .page-header {
      background: white;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 30px;
      border-left: 5px solid #4361ee;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .student-avatar {
      width: 60px;
      height: 60px;
      border-radius: 10px;
      object-fit: cover;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
      border: 2px solid #e9ecef;
    }
    .no-photo {
      width: 60px;
      height: 60px;
      border-radius: 10px;
      background: linear-gradient(135deg, #dee2e6, #ced4da);
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: 600;
      color: #6c757d;
      font-size: 0.8rem;
    }
    .table thead {
      background: linear-gradient(135deg, #4361ee, #3f37c9);
      color: white;
    }
    .table th {
      border: none;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 0.85rem;
      padding: 1rem 0.75rem;
    }
    .table td {
      vertical-align: middle;
      padding: 1rem 0.75rem;
      border-color: #f1f3f4;
    }
    .filter-box {
      background: white;
      padding: 25px;
      border-radius: 12px;
      margin-bottom: 25px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      border: 1px solid #f1f3f4;
    }
    .btn-filter {
      background: linear-gradient(135deg, #4361ee, #3f37c9);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
    }
    .btn-filter:hover {
      background: linear-gradient(135deg, #3a56d4, #364edb);
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
    }
    .btn-secondary {
      background: linear-gradient(135deg, #6c757d, #495057);
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
    }
    .btn-secondary:hover {
      background: linear-gradient(135deg, #5a6268, #3d4348);
      transform: translateY(-1px);
    }
    .back-btn {
      background: linear-gradient(135deg, #6c757d, #495057);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      margin-right: 10px;
    }
    .back-btn:hover {
      background: linear-gradient(135deg, #5a6268, #3d4348);
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
      color: white;
    }
    .form-select, .form-control {
      border-radius: 8px;
      border: 2px solid #e9ecef;
      font-weight: 500;
    }
    .form-select:focus, .form-control:focus {
      border-color: #4361ee;
      box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      overflow: hidden;
    }
    .badge {
      padding: 0.5rem 0.75rem;
      border-radius: 8px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .header-actions {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }
    .header-content {
      flex: 1;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <!-- HEADER SECTION -->
    <div class="page-header">
      <div class="header-actions">
        <div class="header-content">
          <h2 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-chart-line me-2"></i> Student Reports</h2>
          <p class="text-muted mb-0">View and filter detailed student performance, class, and fee information.</p>
        </div>
        <div>
          <a href="../reports/index.php" class="btn back-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to Reports
          </a>
        </div>
      </div>
    </div>

    <!-- FILTER FORM -->
    <div class="filter-box">
      <form method="GET">
        <div class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark">Select Class</label>
            <select name="class_id" class="form-select">
              <option value="">All Classes</option>
              <?php foreach($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($class_id == $c['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?> <?= $c['section'] ? '('.$c['section'].')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark">Search by Name or Roll No</label>
            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="e.g. Ali or 101">
          </div>
          <div class="col-md-4 text-md-start">
            <button type="submit" class="btn btn-filter"><i class="fa fa-search me-2"></i>Apply Filters</button>
            <a href="student_report.php" class="btn btn-secondary ms-2"><i class="fas fa-redo me-2"></i>Reset</a>
          </div>
        </div>
      </form>
    </div>

    <!-- REPORT TABLE -->
    <div class="card">
      <div class="card-body p-0">
        <?php if(!$students): ?>
          <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-user-slash fa-3x mb-3 opacity-50"></i>
            <h5 class="fw-semibold">No student records found</h5>
            <p class="mb-0">Try adjusting your search filters</p>
          </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="px-4">#</th>
                <th class="px-4">Photo</th>
                <th class="px-4">Roll No</th>
                <th class="px-4">Name</th>
                <th class="px-4">Class</th>
                <th class="px-4">Parent</th>
                <th class="px-4">Phone</th>
                <th class="px-4">Fee Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($students as $i => $s): ?>
                <tr>
                  <td class="px-4 fw-semibold"><?= $i+1 ?></td>
                  <td class="px-4">
                    <?php if($s['photo'] && file_exists("../".$s['photo'])): ?>
                      <img src="../<?= htmlspecialchars($s['photo']) ?>" class="student-avatar" alt="Photo">
                    <?php else: ?>
                      <div class="no-photo">N/A</div>
                    <?php endif; ?>
                  </td>
                  <td class="px-4"><strong class="text-primary"><?= htmlspecialchars($s['roll_no']) ?></strong></td>
                  <td class="px-4 fw-semibold"><?= htmlspecialchars($s['name']) ?></td>
                  <td class="px-4">
                    <span class="badge bg-light text-dark"><?= htmlspecialchars($s['class_name'] . ($s['class_section'] ? ' - '.$s['class_section'] : '')) ?></span>
                  </td>
                  <td class="px-4"><?= htmlspecialchars($s['parent_name']) ?></td>
                  <td class="px-4"><?= htmlspecialchars($s['phone']) ?></td>
                  <td class="px-4">
                    <?php if(isset($s['fee_status']) && strtolower($s['fee_status']) === 'paid'): ?>
                      <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Paid</span>
                    <?php else: ?>
                      <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Unpaid</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>