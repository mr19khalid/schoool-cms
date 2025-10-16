<?php
include '../includes/config.php';

$date = $_GET['date'] ?? date('Y-m-d');

if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student', 'Class', 'Status', 'Date']);
    $stmt = $pdo->prepare("SELECT a.*, s.name AS student_name, c.name AS class_name
                           FROM attendance a
                           JOIN students s ON a.student_id = s.id
                           JOIN classes c ON a.class_id = c.id
                           WHERE a.date=?");
    $stmt->execute([$date]);
    foreach ($stmt as $row) {
        fputcsv($output, [$row['student_name'], $row['class_name'], $row['status'], $row['date']]);
    }
    fclose($output);
    exit;
}

$stmt = $pdo->prepare("SELECT a.*, s.name AS student_name, c.name AS class_name
                       FROM attendance a
                       JOIN students s ON a.student_id = s.id
                       JOIN classes c ON a.class_id = c.id
                       WHERE a.date=?");
$stmt->execute([$date]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .table th {
            border-top: none;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            vertical-align: middle;
            border-color: #f1f1f1;
        }
        .bg-success {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
        }
        .bg-primary {
            background: linear-gradient(135deg, #007bff, #6f42c1) !important;
        }
        .bg-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14) !important;
        }
        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268, #3d4348);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 p-4 bg-white rounded-3 shadow-sm border">
        <div class="d-flex align-items-center">
            <div class="bg-success rounded-circle p-3 me-3">
                <i class="fas fa-calendar-check text-white fa-xl"></i>
            </div>
            <div>
                <h2 class="fw-bold text-success mb-1">Attendance Report</h2>
                <p class="text-muted mb-0">View and export daily attendance records</p>
            </div>
        </div>
        
        <div>
            <a href="../reports/index.php" class="btn btn-secondary px-4 py-2 fw-semibold me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
            <a href="?date=<?php echo $date; ?>&export=1" class="btn btn-success px-4 py-2 fw-semibold">
                <i class="fas fa-download me-2"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Date Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="col-form-label fw-bold text-dark">Select Date:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="date" class="form-control" value="<?php echo $date; ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-success px-4">
                        <i class="fas fa-eye me-2"></i>View Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0"><?php echo count($rows); ?></h4>
                            <span class="opacity-75">Total Records</span>
                        </div>
                        <i class="fas fa-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0">
                                <?php 
                                $present = array_filter($rows, function($r) { 
                                    return strtolower($r['status']) === 'present'; 
                                }); 
                                echo count($present);
                                ?>
                            </h4>
                            <span class="opacity-75">Present</span>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0">
                                <?php 
                                $absent = array_filter($rows, function($r) { 
                                    return strtolower($r['status']) === 'absent'; 
                                }); 
                                echo count($absent);
                                ?>
                            </h4>
                            <span class="opacity-75">Absent</span>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white py-3">
            <h5 class="card-title mb-0 fw-bold">
                <i class="fas fa-table me-2"></i>
                Attendance Records for <?php echo date('F j, Y', strtotime($date)); ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-4 py-3">
                                <i class="fas fa-user me-2"></i>Student
                            </th>
                            <th class="px-4 py-3">
                                <i class="fas fa-graduation-cap me-2"></i>Class
                            </th>
                            <th class="px-4 py-3">
                                <i class="fas fa-info-circle me-2"></i>Status
                            </th>
                            <th class="px-4 py-3">
                                <i class="fas fa-calendar me-2"></i>Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td class="px-4 py-3 fw-semibold"><?php echo htmlspecialchars($r['student_name']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($r['class_name']); ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (strtolower($r['status']) === 'present'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i><?php echo htmlspecialchars($r['status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i><?php echo htmlspecialchars($r['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-muted">
                                    <i class="fas fa-calendar-day me-2"></i><?php echo htmlspecialchars($r['date']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <?php if (empty($rows)): ?>
    <div class="text-center py-5">
        <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">No attendance records found</h4>
        <p class="text-muted">No attendance data available for the selected date.</p>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>