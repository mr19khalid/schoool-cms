<?php
// classes/view.php
require __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$class_id = $_GET['id'] ?? null;
if (!$class_id) {
    header('Location: list.php');
    exit;
}

// Initialize variables
$class = [];
$students = [];
$error = '';
$search = $_GET['search'] ?? '';

try {
    // get class info
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->execute([$class_id]);
    $class = $stmt->fetch();

    if (!$class) {
        $error = "Class not found!";
    } else {
        // Build query based on search
        $sql = "SELECT * FROM students WHERE class_id = ?";
        $params = [$class_id];
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR roll_no LIKE ? OR parent_name LIKE ? OR phone LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $sql .= " ORDER BY roll_no";
        
        $studentsStmt = $pdo->prepare($sql);
        $studentsStmt->execute($params);
        $students = $studentsStmt->fetchAll();
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Failed to load data. Please try again.";
}

// Calculate stats safely
$totalStudents = count($students);
$maleStudents = 0;
$femaleStudents = 0;

foreach ($students as $student) {
    $gender = strtolower($student['gender'] ?? '');
    if ($gender === 'male') $maleStudents++;
    if ($gender === 'female') $femaleStudents++;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Students - <?= htmlspecialchars($class['name'] ?? 'Class') ?> | School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 20px 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .container {
      max-width: 1400px;
    }
    
    .header-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 30px;
      border-radius: 20px;
      margin-bottom: 30px;
      color: white;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .page-title {
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .class-info {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 15px;
      padding: 20px;
      margin-top: 20px;
      backdrop-filter: blur(10px);
    }
    
    .btn-premium {
      background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
      border: none;
      color: white;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    
    .btn-premium:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      color: white;
    }
    
    .btn-secondary-premium {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      font-weight: 500;
      padding: 10px 20px;
      border-radius: 10px;
      transition: all 0.3s ease;
    }
    
    .btn-secondary-premium:hover {
      background: rgba(255, 255, 255, 0.3);
      color: white;
    }
    
    .search-box {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }
    
    .search-input {
      border-radius: 10px;
      border: 2px solid #e9ecef;
      padding: 12px 15px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .btn-search {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: white;
      border-radius: 10px;
      padding: 12px 25px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-search:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
      color: white;
    }
    
    .btn-clear {
      background: #6c757d;
      border: none;
      color: white;
      border-radius: 10px;
      padding: 12px 20px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-clear:hover {
      background: #5a6268;
      color: white;
    }
    
    .table-container {
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    
    .table-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 20px;
      color: white;
    }
    
    .stats-card {
      background: white;
      border-radius: 12px;
      padding: 15px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 15px;
      border-left: 4px solid #667eea;
    }
    
    .stats-number {
      font-size: 1.8rem;
      font-weight: 700;
      color: #667eea;
      margin-bottom: 5px;
    }
    
    .student-photo {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid #e9ecef;
    }
    
    .table thead th {
      background: #2c3e50;
      color: white;
      font-weight: 600;
      padding: 15px 12px;
      border: none;
    }
    
    .table tbody td {
      padding: 15px 12px;
      vertical-align: middle;
      border-color: #f8f9fa;
    }
    
    .table tbody tr:hover {
      background-color: #f8f9fa;
    }
    
    .gender-badge {
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 600;
    }
    
    .badge-male {
      background: #4facfe;
      color: white;
    }
    
    .badge-female {
      background: #f093fb;
      color: white;
    }
    
    .empty-state {
      padding: 50px 20px;
      text-align: center;
    }
    
    .empty-icon {
      font-size: 4rem;
      color: #6c757d;
      margin-bottom: 15px;
    }
    
    .alert-premium {
      border-radius: 15px;
      border: none;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .search-results-info {
      background: #e7f3ff;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
      border-left: 4px solid #4facfe;
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

  <?php if (empty($error) && !empty($class)): ?>
  <!-- Header Section -->
  <div class="header-section">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h1 class="page-title">
          <i class="fas fa-users me-3"></i>
          Students in <?= htmlspecialchars($class['name']) ?>
          <?php if (!empty($class['section'])): ?>
            <span class="text-warning"> - <?= htmlspecialchars($class['section']) ?></span>
          <?php endif; ?>
        </h1>
        <p class="mb-0 opacity-75">Complete student directory with detailed information</p>
      </div>
      <div class="col-md-4 text-end">
        <a href="list.php" class="btn btn-secondary-premium">
          <i class="fas fa-arrow-left me-2"></i>Back to Classes
        </a>
      </div>
    </div>
    
    <!-- Class Info Card -->
    <div class="class-info mt-3">
      <div class="row">
        <div class="col-md-3">
          <strong><i class="fas fa-graduation-cap me-2"></i>Class:</strong>
          <?= htmlspecialchars($class['name']) ?>
        </div>
        <div class="col-md-3">
          <strong><i class="fas fa-layer-group me-2"></i>Section:</strong>
          <?= !empty($class['section']) ? htmlspecialchars($class['section']) : 'General' ?>
        </div>
        <div class="col-md-3">
          <strong><i class="fas fa-user-friends me-2"></i>Total Students:</strong>
          <?= $totalStudents ?>
        </div>
        <div class="col-md-3">
          <strong><i class="fas fa-calendar me-2"></i>View Date:</strong>
          <?= date('M d, Y') ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Search Box -->
  <div class="search-box">
    <form method="GET" action="">
      <input type="hidden" name="id" value="<?= $class_id ?>">
      <div class="row g-3 align-items-center">
        <div class="col-md-8">
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
              <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" 
                   name="search" 
                   class="form-control search-input border-start-0" 
                   placeholder="Search students by name, roll number, parent name, or phone..." 
                   value="<?= htmlspecialchars($search) ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-search flex-fill">
              <i class="fas fa-search me-2"></i>Search
            </button>
            <?php if (!empty($search)): ?>
              <a href="?id=<?= $class_id ?>" class="btn btn-clear">
                <i class="fas fa-times me-2"></i>Clear
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php if (!empty($search)): ?>
        <div class="mt-3 search-results-info">
          <i class="fas fa-info-circle me-2 text-primary"></i>
          Showing <?= $totalStudents ?> result(s) for "<strong><?= htmlspecialchars($search) ?></strong>"
        </div>
      <?php endif; ?>
    </form>
  </div>

  <!-- Stats Cards -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="stats-card">
        <div class="stats-number"><?= $totalStudents ?></div>
        <div class="text-muted">Total Students</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="stats-number"><?= $maleStudents ?></div>
        <div class="text-muted">Male Students</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="stats-number"><?= $femaleStudents ?></div>
        <div class="text-muted">Female Students</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="stats-number">A+</div>
        <div class="text-muted">Class Status</div>
      </div>
    </div>
  </div>

  <!-- Students Table -->
  <div class="table-container">
    <div class="table-header">
      <h4 class="mb-0">
        <i class="fas fa-list-ol me-2"></i>
        Student Directory
        <small class="opacity-75">(Sorted by Roll Number)</small>
      </h4>
    </div>
    
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th width="8%" class="text-center">Photo</th>
            <th width="10%">Roll No</th>
            <th>Student Name</th>
            <th width="12%">Gender</th>
            <th width="12%">Date of Birth</th>
            <th>Parent/Guardian</th>
            <th width="15%">Contact</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($totalStudents > 0): ?>
            <?php foreach ($students as $student): ?>
              <tr>
                <td class="text-center">
                  <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/../' . $student['photo'])): ?>
                    <img src="../<?= htmlspecialchars($student['photo']) ?>" class="student-photo" alt="<?= htmlspecialchars($student['name']) ?>">
                  <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($student['name']) ?>&background=667eea&color=fff" class="student-photo" alt="<?= htmlspecialchars($student['name']) ?>">
                  <?php endif; ?>
                </td>
                <td>
                  <span class="fw-bold text-primary">#<?= htmlspecialchars($student['roll_no']) ?></span>
                </td>
                <td class="fw-semibold"><?= htmlspecialchars($student['name']) ?></td>
                <td>
                  <?php 
                    $gender = strtolower($student['gender'] ?? '');
                    $badgeClass = $gender === 'male' ? 'badge-male' : ($gender === 'female' ? 'badge-female' : 'bg-secondary');
                    $genderIcon = $gender === 'male' ? 'mars' : ($gender === 'female' ? 'venus' : 'genderless');
                  ?>
                  <span class="gender-badge <?= $badgeClass ?>">
                    <i class="fas fa-<?= $genderIcon ?> me-1"></i>
                    <?= htmlspecialchars($student['gender']) ?>
                  </span>
                </td>
                <td>
                  <i class="fas fa-calendar-day me-1 text-muted"></i>
                  <?= htmlspecialchars($student['dob']) ?>
                </td>
                <td>
                  <div class="fw-semibold"><?= htmlspecialchars($student['parent_name']) ?></div>
                </td>
                <td>
                  <div class="text-muted">
                    <i class="fas fa-phone me-1"></i>
                    <?= htmlspecialchars($student['phone']) ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="empty-state">
                  <div class="empty-icon">
                    <i class="fas fa-user-graduate"></i>
                  </div>
                  <h4 class="text-muted mb-3">
                    <?php if (!empty($search)): ?>
                      No Students Found
                    <?php else: ?>
                      No Students Enrolled
                    <?php endif; ?>
                  </h4>
                  <p class="text-muted mb-4">
                    <?php if (!empty($search)): ?>
                      No students found matching your search criteria.
                    <?php else: ?>
                      This class doesn't have any students yet.
                    <?php endif; ?>
                  </p>
                  <?php if (empty($search)): ?>
                    <a href="../students/add.php" class="btn btn-premium">
                      <i class="fas fa-user-plus me-2"></i>Add New Student
                    </a>
                  <?php else: ?>
                    <a href="?id=<?= $class_id ?>" class="btn btn-premium">
                      <i class="fas fa-eye me-2"></i>View All Students
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Footer -->
  <div class="text-center mt-4">
    <p class="text-white-50">
      <i class="fas fa-shield-alt me-2"></i>
      © 2024 School CMS Premium Edition
    </p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>