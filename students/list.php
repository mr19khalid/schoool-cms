<?php
// students/list.php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// fetch students with class name
$stmt = $pdo->query("SELECT s.*, c.name AS class_name, c.section AS class_section FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.id DESC");
$students = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Students - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --info: #4895ef;
      --warning: #f72585;
      --light: #f8f9fa;
      --dark: #212529;
    }
    
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
    }
    
    .container-main {
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .page-header {
      background: white;
      border-radius: 15px;
      padding: 25px 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
      border-left: 5px solid var(--primary);
    }
    
    .back-btn {
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 8px 15px;
      color: var(--primary);
      text-decoration: none;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .back-btn:hover {
      background: var(--primary);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
    }
    
    .page-title {
      color: #2c3e50;
      font-weight: 700;
      margin-bottom: 5px;
    }
    
    .page-subtitle {
      color: #7f8c8d;
      font-size: 16px;
    }
    
    .add-btn {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border: none;
      border-radius: 10px;
      padding: 12px 25px;
      font-weight: 600;
      box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
    }
    
    .add-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
    }
    
    .data-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      overflow: hidden;
      border: none;
    }
    
    .table-container {
      overflow-x: auto;
    }
    
    .custom-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }
    
    .custom-table thead {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
    }
    
    .custom-table thead th {
      color: black;
      font-weight: 600;
      padding: 18px 15px;
      border: none;
      font-size: 15px;
    }
    
    .custom-table tbody tr {
      transition: all 0.3s;
    }
    
    .custom-table tbody tr:hover {
      background-color: rgba(67, 97, 238, 0.05);
      transform: translateY(-2px);
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
    }
    
    .custom-table tbody td {
      padding: 16px 15px;
      border-bottom: 1px solid #f0f0f0;
      vertical-align: middle;
    }
    
    .student-avatar {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      object-fit: cover;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }
    
    .no-photo {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      background: linear-gradient(135deg, #f1f2f6, #dfe4ea);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #a4b0be;
      font-size: 12px;
      font-weight: 600;
    }
    
    .student-name {
      font-weight: 600;
      color: #2c3e50;
    }
    
    .student-roll {
      font-weight: 600;
      color: var(--primary);
    }
    
    .class-badge {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
    }
    
    .parent-info {
      color: #7f8c8d;
      font-size: 14px;
    }
    
    .phone-info {
      color: #2c3e50;
      font-weight: 500;
    }
    
    .action-btn {
      border-radius: 8px;
      padding: 8px 15px;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    
    .edit-btn {
      background: rgba(76, 201, 240, 0.1);
      color: #4cc9f0;
      border: 1px solid rgba(76, 201, 240, 0.3);
    }
    
    .edit-btn:hover {
      background: #4cc9f0;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(76, 201, 240, 0.3);
    }
    
    .delete-btn {
      background: rgba(247, 37, 133, 0.1);
      color: #f72585;
      border: 1px solid rgba(247, 37, 133, 0.3);
    }
    
    .delete-btn:hover {
      background: #f72585;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(247, 37, 133, 0.3);
    }
    
    .empty-state {
      padding: 60px 20px;
      text-align: center;
      color: #7f8c8d;
    }
    
    .empty-icon {
      font-size: 70px;
      color: #dfe4ea;
      margin-bottom: 20px;
    }
    
    .empty-text {
      font-size: 18px;
      margin-bottom: 15px;
    }
    
    @media (max-width: 768px) {
      .page-header {
        padding: 20px;
      }
      
      .custom-table thead {
        display: none;
      }
      
      .custom-table tbody tr {
        display: block;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        padding: 15px;
      }
      
      .custom-table tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 10px;
        border-bottom: 1px solid #000000ff;
      }
      
      .custom-table tbody td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #7f8c8d;
        margin-right: 10px;
      }
      
      .action-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
      }
      
      .custom-table tbody td:last-child {
        border-bottom: none;
      }
    }
  </style>
  <script>
    function confirmDelete(id){
      if(confirm('Are you sure you want to delete this student? This action cannot be undone.')){
        window.location = 'delete.php?id=' + id;
      }
    }
  </script>
</head>
<body>
  <div class="container container-main py-4">
    <a href="../dashboard.php" class="back-btn">
      <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>
    
    <div class="page-header">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1 class="page-title"><i class="fas fa-user-graduate me-2"></i>Students Management</h1>
          <p class="page-subtitle">View and manage all student records in the system</p>
        </div>
        <div class="col-md-6 text-md-end">
          <a href="add.php" class="btn add-btn">
            <i class="fas fa-plus-circle me-2"></i> Add New Student
          </a>
        </div>
      </div>
    </div>

    <div class="data-card">
      <div class="table-container">
        <table class="table custom-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Photo</th>
              <th>Roll No</th>
              <th>Name</th>
              <th>Class</th>
              <th>Parent</th>
              <th>Phone</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!$students): ?>
              <tr>
                <td colspan="8">
                  <div class="empty-state">
                    <div class="empty-icon">
                      <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3 class="empty-text">No Students Found</h3>
                    <p>Get started by adding your first student to the system.</p>
                    <a href="add.php" class="btn add-btn mt-3">
                      <i class="fas fa-plus-circle me-2"></i> Add Student
                    </a>
                  </div>
                </td>
              </tr>
            <?php else: foreach($students as $i => $s): ?>
              <tr>
                <td data-label="#"><?= $i+1 ?></td>
                <td data-label="Photo">
                  <?php if($s['photo']): ?>
                    <img src="../<?= htmlspecialchars($s['photo']) ?>" class="student-avatar" alt="Student Photo">
                  <?php else: ?>
                    <div class="no-photo">N/A</div>
                  <?php endif; ?>
                </td>
                <td data-label="Roll No">
                  <span class="student-roll"><?= htmlspecialchars($s['roll_no']) ?></span>
                </td>
                <td data-label="Name">
                  <span class="student-name"><?= htmlspecialchars($s['name']) ?></span>
                </td>
                <td data-label="Class">
                  <span class="class-badge"><?= htmlspecialchars($s['class_name'] ? $s['class_name'] . ($s['class_section'] ? ' - '.$s['class_section'] : '') : '—') ?></span>
                </td>
                <td data-label="Parent">
                  <span class="parent-info"><?= htmlspecialchars($s['parent_name']) ?></span>
                </td>
                <td data-label="Phone">
                  <span class="phone-info"><?= htmlspecialchars($s['phone']) ?></span>
                </td>
                <td data-label="Actions">
                  <div class="action-buttons">
                    <a class="btn action-btn edit-btn me-2" href="edit.php?id=<?= $s['id'] ?>">
                      <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <button class="btn action-btn delete-btn" onclick="confirmDelete(<?= $s['id'] ?>)">
                      <i class="fas fa-trash me-1"></i> Delete
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>