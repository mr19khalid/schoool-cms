<?php
include '../includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Reports - CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .report-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
        }
        .report-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        .card-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }
        .gradient-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
        .gradient-success {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }
        .gradient-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }
        .card-body {
            padding: 2.5rem 1.5rem;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0 0 30px 30px;
            padding: 3rem 0;
            margin-bottom: 3rem;
        }
        .btn-report {
            border: 2px solid transparent;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 1rem 0;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .btn-report:hover {
            border-color: currentColor;
            background: white;
        }
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Header Section -->
    <div class="header-section text-white position-relative">
        <!-- Back to Dashboard Button -->
        <div class="back-btn">
            <a href="../dashboard.php" class="btn btn-light btn-sm shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
        <div class="container">
            <div class="text-center">
                <h1 class="fw-bold display-4 mb-3">📊 School Reports</h1>
                <p class="lead mb-0">Comprehensive analytics and insights for your school management</p>
            </div>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="container mb-5">
        <div class="row g-4 justify-content-center">
            <!-- Student Report Card -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card shadow-sm">
                    <div class="card-body text-center text-white gradient-primary">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-3">Student Report</h4>
                        <p class="card-text mb-4 opacity-90">
                            View detailed student information, class-wise distribution, and academic performance
                        </p>
                        <a href="student_report.php" class="btn btn-light btn-lg w-75 fw-bold text-primary">
                            <i class="fas fa-chart-bar me-2"></i>View Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Attendance Report Card -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card shadow-sm">
                    <div class="card-body text-center text-white gradient-success">
                        <div class="card-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-3">Attendance Report</h4>
                        <p class="card-text mb-4 opacity-90">
                            Track student attendance patterns, monthly reports, and absence statistics
                        </p>
                        <a href="attendance_report.php" class="btn btn-light btn-lg w-75 fw-bold text-success">
                            <i class="fas fa-chart-line me-2"></i>View Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Fee Report Card -->
            <div class="col-lg-4 col-md-6">
                <div class="report-card shadow-sm">
                    <div class="card-body text-center text-white gradient-warning">
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h4 class="card-title fw-bold mb-3">Fee Report</h4>
                        <p class="card-text mb-4 opacity-90">
                            Monitor fee collections, pending payments, and financial summaries
                        </p>
                        <a href="fee_report.php" class="btn btn-light btn-lg w-75 fw-bold text-warning">
                            <i class="fas fa-chart-pie me-2"></i>View Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <h5 class="text-muted mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Select a report category above to view detailed analytics and export data
                        </h5>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <i class="fas fa-download text-primary me-2"></i>
                                <span class="text-muted">Export to PDF/Excel</span>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-filter text-success me-2"></i>
                                <span class="text-muted">Filter by Date & Class</span>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-chart-bar text-warning me-2"></i>
                                <span class="text-muted">Visual Charts & Graphs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>