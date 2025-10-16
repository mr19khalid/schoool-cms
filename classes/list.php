<?php
// classes/list.php
require __DIR__ . '/../includes/config.php';

// only logged in admins can access
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Initialize variables
$classes = [];
$error = '';
$totalClasses = 0;
$activeSections = 0;

try {
    // fetch all classes
    $stmt = $pdo->query("SELECT * FROM classes ORDER BY name ASC");
    $classes = $stmt->fetchAll();
    $totalClasses = count($classes);
    
    // Count unique sections
    $sections = [];
    foreach ($classes as $class) {
        if (!in_array($class['section'], $sections)) {
            $sections[] = $class['section'];
        }
    }
    $activeSections = count($sections);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Failed to load classes. Please try again.";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Class List - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --gold-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
      --dark-blue: #2c3e50;
    }
    
    * {
      font-family: 'Inter', sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px 0;
    }
    
    .container {
      max-width: 1400px;
    }
    
    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    
    .header-section {
      background: var(--primary-gradient);
      padding: 40px 0;
      border-radius: 20px;
      margin-bottom: 30px;
      position: relative;
      overflow: hidden;
    }
    
    .page-title {
      font-size: 2.8rem;
      font-weight: 700;
      color: white;
      text-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      position: relative;
    }
    
    .page-subtitle {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.2rem;
      font-weight: 300;
      position: relative;
    }
    
    .btn-premium {
      background: var(--gold-gradient);
      border: none;
      color: white;
      font-weight: 600;
      padding: 12px 25px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(245, 87, 108, 0.3);
      transition: all 0.3s ease;
    }
    
    .btn-premium:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 35px rgba(245, 87, 108, 0.4);
      color: white;
    }
    
    .btn-secondary-premium {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      font-weight: 500;
      padding: 12px 25px;
      border-radius: 12px;
      transition: all 0.3s ease;
    }
    
    .btn-secondary-premium:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-2px);
      color: white;
    }
    
    .table-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    
    .table-header {
      background: var(--primary-gradient);
      padding: 25px 30px;
      border-bottom: none;
    }
    
    .table-title {
      color: white;
      font-size: 1.5rem;
      font-weight: 600;
      margin: 0;
    }
    
    .table thead th {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      font-weight: 600;
      padding: 20px 15px;
      border: none;
      font-size: 0.95rem;
    }
    
    .table tbody td {
      padding: 20px 15px;
      vertical-align: middle;
      border-color: rgba(0, 0, 0, 0.05);
      font-weight: 500;
    }
    
    .table tbody tr {
      transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
      background: rgba(102, 126, 234, 0.05);
      transform: translateX(5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .badge-premium {
      background: var(--secondary-gradient);
      color: white;
      font-weight: 600;
      padding: 8px 15px;
      border-radius: 10px;
      font-size: 0.8rem;
    }
    
    .action-btn {
      padding: 8px 15px;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.85rem;
      transition: all 0.3s ease;
      border: none;
      margin: 2px;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-view {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
    }
    
    .btn-edit {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
      color: white;
    }
    
    .btn-delete {
      background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
      color: white;
    }
    
    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      color: white;
    }
    
    .empty-state {
      padding: 60px 20px;
      text-align: center;
    }
    
    .empty-icon {
      font-size: 5rem;
      background: var(--gold-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 20px;
    }
    
    .stats-card {
      background: white;
      border-radius: 15px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    .stats-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    .stats-number {
      font-size: 2.5rem;
      font-weight: 700;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 5px;
    }
    
    .alert-premium {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: none;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>

<div class="container py-4">
  <!-- Error Message -->
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-premium alert-dismissible fade show mb-4" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Header Section -->
  <div class="header-section text-center mb-5">
    <h1 class="page-title mb-2">🎓 Class Management</h1>
    <p class="page-subtitle">Premium Education Administration System</p>
  </div>

  <!-- Stats Cards -->
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="stats-card">
        <div class="stats-number"><?= $totalClasses ?></div>
        <div class="text-muted">Total Classes</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stats-card">
        <div class="stats-number"><?= $activeSections ?></div>
        <div class="text-muted">Active Sections</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stats-card">
        <div class="stats-number">A+</div>
        <div class="text-muted">System Status</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stats-card">
        <div class="stats-number">24/7</div>
        <div class="text-muted">Support Ready</div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <a href="add.php" class="btn btn-premium me-3">
        <i class="fas fa-plus-circle me-2"></i>Add New Class
      </a>
      <a href="../dashboard.php" class="btn btn-secondary-premium">
        <i class="fas fa-home me-2"></i>Dashboard
      </a>
    </div>
    <div class="text-end">
      <span class="text-white fw-semibold">Premium Edition</span>
    </div>
  </div>

  <!-- Main Table -->
  <div class="table-container">
    <div class="table-header">
      <h3 class="table-title"><i class="fas fa-list-alt me-2"></i>Class Directory</h3>
    </div>
    
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th width="10%" class="text-center">ID</th>
            <th>Class Name</th>
            <th>Section</th>
            <th width="35%" class="text-center">Management Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($totalClasses > 0): ?>
            <?php foreach ($classes as $c): ?>
              <tr>
                <td class="text-center fw-bold text-primary">#<?= htmlspecialchars($c['id']) ?></td>
                <td class="fw-semibold">
                  <i class="fas fa-graduation-cap me-2 text-primary"></i>
                  <?= htmlspecialchars($c['name']) ?>
                </td>
                <td>
                  <span class="badge-premium">
                    <i class="fas fa-layer-group me-1"></i>
                    <?= htmlspecialchars($c['section']) ?>
                  </span>
                </td>
                <td class="text-center">
                  <a href="view.php?id=<?= $c['id'] ?>" class="action-btn btn-view">
                    <i class="fas fa-eye me-1"></i>View
                  </a>
                  <a href="edit.php?id=<?= $c['id'] ?>" class="action-btn btn-edit">
                    <i class="fas fa-edit me-1"></i>Edit
                  </a>
                  <a href="delete.php?id=<?= $c['id'] ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this class?')">
                    <i class="fas fa-trash me-1"></i>Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center py-5">
                <div class="empty-state">
                  <div class="empty-icon">
                    <i class="fas fa-university"></i>
                  </div>
                  <h4 class="text-muted mb-3">No Classes Found</h4>
                  <p class="text-muted mb-4">Begin by creating your first class to build your academic structure</p>
                  <a href="add.php" class="btn btn-premium">
                    <i class="fas fa-plus-circle me-2"></i>Create First Class
                  </a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <div class="text-center mt-4">
    <p class="text-white-50">© 2024 School CMS Premium Edition. All rights reserved.</p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>