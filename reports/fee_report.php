<?php
include '../includes/config.php';

$status = $_GET['status'] ?? '';

if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="fee_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student', 'Class', 'Amount', 'Status', 'Due Date', 'Created At']);
    $query = "SELECT f.*, s.name AS student_name, c.name AS class_name
              FROM fees f
              JOIN students s ON f.student_id = s.id
              JOIN classes c ON s.class_id = c.id";
    if ($status) {
        $query .= " WHERE f.status=:status";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['status' => $status]);
    } else {
        $stmt = $pdo->query($query);
    }
    foreach ($stmt as $row) {
        fputcsv($output, [$row['student_name'], $row['class_name'], $row['amount'], $row['status'], $row['due_date'], $row['created_at']]);
    }
    fclose($output);
    exit;
}

$query = "SELECT f.*, s.name AS student_name, c.name AS class_name
          FROM fees f
          JOIN students s ON f.student_id = s.id
          JOIN classes c ON s.class_id = c.id";
if ($status) {
    $query .= " WHERE f.status=:status";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['status' => $status]);
} else {
    $stmt = $pdo->query($query);
}
$fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Report - School CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            font-family: 'Segoe UI', sans-serif;
        }
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #ffc107;
            margin-bottom: 25px;
        }
        .data-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            border: none;
        }
        table thead {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }
        table th {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            border: none;
        }
        table td {
            vertical-align: middle;
            border-color: #f1f3f4;
            padding: 1rem 0.75rem;
        }
        .badge-paid {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-partial {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-due {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .export-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            color: white;
        }
        .back-btn {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
            color: white;
        }
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            border: 1px solid #f1f3f4;
        }
        .form-select, .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            font-weight: 500;
        }
        .form-select:focus, .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 8px 20px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-1px);
        }
        .amount-cell {
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
</head>
<body>
<div class="container mt-4">

    <!-- Header Section -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1 text-warning"><i class="fas fa-money-bill-wave me-2"></i>Fee Report</h2>
                <p class="text-muted mb-0">Track and manage all fee transactions and payment status</p>
            </div>
            <div>
                <a href="../reports/index.php" class="btn back-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back to Reports
                </a>
                <a href="?status=<?php echo $status; ?>&export=1" class="btn export-btn">
                    <i class="fas fa-file-csv me-2"></i>Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="get" class="row g-3 align-items-center">
            <div class="col-auto">
                <label class="form-label fw-bold text-dark mb-0">Filter by Status:</label>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="paid" <?= $status=='paid'?'selected':'' ?>>Paid</option>
                    <option value="partial" <?= $status=='partial'?'selected':'' ?>>Partial</option>
                    <option value="due" <?= $status=='due'?'selected':'' ?>>Due</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Apply Filter
                </button>
            </div>
            <?php if($status): ?>
            <div class="col-auto">
                <a href="?" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Clear Filter
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Data Table -->
    <div class="data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="px-4 py-3"><i class="fas fa-user me-2"></i>Student</th>
                        <th class="px-4 py-3"><i class="fas fa-graduation-cap me-2"></i>Class</th>
                        <th class="px-4 py-3"><i class="fas fa-money-bill me-2"></i>Amount</th>
                        <th class="px-4 py-3"><i class="fas fa-info-circle me-2"></i>Status</th>
                        <th class="px-4 py-3"><i class="fas fa-calendar-day me-2"></i>Due Date</th>
                        <th class="px-4 py-3"><i class="fas fa-clock me-2"></i>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($fees)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-search fa-2x mb-3 d-block"></i>
                            No fee records found for the selected criteria.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($fees as $f): ?>
                        <tr>
                            <td class="px-4 fw-semibold"><?php echo htmlspecialchars($f['student_name']); ?></td>
                            <td class="px-4">
                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($f['class_name']); ?></span>
                            </td>
                            <td class="px-4 amount-cell">$<?php echo number_format($f['amount'], 2); ?></td>
                            <td class="px-4">
                                <?php if($f['status'] == 'paid'): ?>
                                    <span class="badge-paid"><i class="fas fa-check-circle me-1"></i>Paid</span>
                                <?php elseif($f['status'] == 'partial'): ?>
                                    <span class="badge-partial"><i class="fas fa-clock me-1"></i>Partial</span>
                                <?php else: ?>
                                    <span class="badge-due"><i class="fas fa-exclamation-circle me-1"></i>Due</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 text-muted"><?php echo htmlspecialchars($f['due_date']); ?></td>
                            <td class="px-4 text-muted"><small><?php echo htmlspecialchars($f['created_at']); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>