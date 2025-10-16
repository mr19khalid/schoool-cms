<?php
// dashboard.php - Pro+ professional dashboard
require_once __DIR__ . '/includes/config.php';

// security: require login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// load settings safely
$settings = $GLOBALS['school_settings'] ?? [
    'school_name' => 'School CMS',
    'school_logo' => null,
    'theme_color' => '#4361ee',
    'session_year' => date('Y')
];
$theme = $settings['theme_color'] ?? '#4361ee';

// ---------- Dashboard stats ----------
try {
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
} catch (Exception $e) { $totalStudents = 0; }

try {
    $totalClasses = (int)$pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
} catch (Exception $e) { $totalClasses = 0; }

try {
    // total fees collected
    $totalFees = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM fees WHERE status='paid'")->fetchColumn();
    $totalFees = $totalFees ?: 0;
} catch (Exception $e) { $totalFees = 0; }

try {
    $attendanceToday = $pdo->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn();
    $attendanceToday = (int)$attendanceToday;
} catch (Exception $e) { $attendanceToday = 0; }

// ---------- Recent Notifications & Activities ----------
try {
    // prefer activities table if exists
    $notificationsStmt = $pdo->query("
        SELECT id, description AS title, created_at
        FROM activities
        ORDER BY created_at DESC
        LIMIT 6
    ");
    $notifications = $notificationsStmt->fetchAll();
} catch (Exception $e) {
    // fallback: union of recent students/classes/fees
    $notifications = $pdo->query("
        SELECT id,title,created_at FROM (
            SELECT id, name AS title, created_at FROM students
            UNION ALL
            SELECT id, name AS title, created_at FROM classes
            UNION ALL
            SELECT id, CONCAT('Fee - Rs ', amount) AS title, created_at FROM fees
        ) t ORDER BY created_at DESC LIMIT 6
    ")->fetchAll();
}

// activity timeline
try {
    $timeline = $pdo->query("SELECT id, description, created_at FROM activities ORDER BY created_at DESC LIMIT 10")->fetchAll();
} catch (Exception $e) {
    $timeline = [];
}

// ---------- Chart data (monthly/yearly) ----------
// Monthly fees for current year (Jan..Dec) - return month short name and total
$monthLabels = [];
$monthlyFeesData = array_fill(0,12,0);
$monthlyAttendanceData = array_fill(0,12,0);
try {
    $year = date('Y');
    // fees grouped by month
    $stmt = $pdo->prepare("SELECT MONTH(created_at) AS m, COALESCE(SUM(amount),0) AS total FROM fees WHERE YEAR(created_at)=? GROUP BY m");
    $stmt->execute([$year]);
    $rows = $stmt->fetchAll();
    foreach($rows as $r){
        $idx = (int)$r['m'] - 1;
        $monthlyFeesData[$idx] = (float)$r['total'];
    }

    // attendance grouped by month (count)
    $stmt = $pdo->prepare("SELECT MONTH(date) AS m, COUNT(*) AS total FROM attendance WHERE YEAR(date)=? GROUP BY m");
    $stmt->execute([$year]);
    $rows = $stmt->fetchAll();
    foreach($rows as $r){
        $idx = (int)$r['m'] - 1;
        $monthlyAttendanceData[$idx] = (int)$r['total'];
    }
} catch (Exception $e) {
    // keep zeros
}

// Yearly fees (last 3 years)
$yearLabels = [];
$yearlyFeesData = [];
try {
    $stmt = $pdo->query("SELECT YEAR(created_at) AS y, COALESCE(SUM(amount),0) AS total FROM fees GROUP BY y ORDER BY y DESC LIMIT 4");
    $rows = $stmt->fetchAll();
    $yearLabels = [];
    $yearlyFeesData = [];
    foreach(array_reverse($rows) as $r){
        $yearLabels[] = (string)$r['y'];
        $yearlyFeesData[] = (float)$r['total'];
    }
    if(empty($yearLabels)){
        // fallback to last 3 years labels
        for($i=2;$i>=0;$i--){
            $yearLabels[] = (string)(date('Y') - $i);
            $yearlyFeesData[] = 0;
        }
    }
} catch (Exception $e) {
    for($i=2;$i>=0;$i--){
        $yearLabels[] = (string)(date('Y') - $i);
        $yearlyFeesData[] = 0;
    }
}

// month short labels
$monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

// convert PHP arrays to JSON for JS
$json_monthLabels = json_encode($monthLabels);
$json_monthlyFees = json_encode($monthlyFeesData);
$json_monthlyAttendance = json_encode($monthlyAttendanceData);
$json_yearLabels = json_encode($yearLabels);
$json_yearlyFees = json_encode($yearlyFeesData);
$json_notifications = json_encode($notifications);
$json_timeline = json_encode($timeline);

// helper for safe outputs
function h($s){ return htmlspecialchars($s ?? ''); }

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dashboard • <?= h($settings['school_name']) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Bootstrap 5 + Chart.js + icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: <?= h($theme) ?>;
      --primary-light: #667eea;
      --primary-dark: #2b2a6d;
      --secondary: #6c757d;
      --success: #28a745;
      --info: #17a2b8;
      --warning: #ffc107;
      --danger: #dc3545;
      --light: #f8f9fa;
      --dark: #343a40;
      --gray-100: #f8f9fa;
      --gray-200: #e9ecef;
      --gray-300: #dee2e6;
      --gray-400: #ced4da;
      --gray-500: #adb5bd;
      --gray-600: #6c757d;
      --gray-700: #495057;
      --gray-800: #343a40;
      --gray-900: #212529;
      --card-radius: 12px;
      --sidebar-width: 260px;
      --sidebar-collapsed-width: 80px;
      --header-height: 70px;
      --transition: all 0.3s ease;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: "Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
      background-color: #f5f7fb;
      margin: 0;
      color: var(--gray-800);
      line-height: 1.5;
      transition: var(--transition);
    }

    /* ---------- Dark Mode Styles ---------- */
    body.dark {
      background-color: #0f172a;
      color: #e2e8f0;
    }

    body.dark .topbar,
    body.dark .stat-card,
    body.dark .card {
      background: #1e293b;
      color: #e2e8f0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    body.dark .searchbar input {
      background: #334155;
      border-color: #475569;
      color: #e2e8f0;
    }

    body.dark .searchbar input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
    }

    body.dark .searchbar input::placeholder {
      color: #94a3b8;
    }

    body.dark .icon-btn {
      color: #94a3b8;
    }

    body.dark .icon-btn:hover {
      background: #334155;
      color: #e2e8f0;
    }

    body.dark .card-header {
      border-bottom-color: #334155;
    }

    body.dark .time-item {
      border-bottom-color: #334155;
    }

    body.dark .stat-card .label {
      color: #94a3b8;
    }

    body.dark .text-muted {
      color: #94a3b8 !important;
    }

    body.dark .dropdown-menu {
      background: #1e293b;
      border-color: #334155;
    }

    body.dark .dropdown-item {
      color: #e2e8f0;
    }

    body.dark .dropdown-item:hover {
      background: #334155;
      color: #e2e8f0;
    }

    body.dark .form-select {
      background: #334155;
      border-color: #475569;
      color: #e2e8f0;
    }

    body.dark .btn-outline-primary {
      color: var(--primary-light);
      border-color: var(--primary-light);
    }

    body.dark .btn-outline-primary:hover {
      background: var(--primary);
      border-color: var(--primary);
    }

    body.dark .btn-light {
      background: #334155;
      border-color: #475569;
      color: #e2e8f0;
    }

    body.dark .btn-light:hover {
      background: #475569;
      border-color: #64748b;
    }

    body.dark .btn-outline-light {
      color: #e2e8f0;
      border-color: #64748b;
    }

    body.dark .btn-outline-light:hover {
      background: #64748b;
      border-color: #94a3b8;
    }

    /* ---------- Sidebar ---------- */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      height: 100vh;
      width: var(--sidebar-width);
      background: linear-gradient(180deg, var(--primary-dark), var(--primary));
      color: #fff;
      padding: 0;
      box-shadow: 0 0 28px rgba(0, 0, 0, 0.08);
      transition: width 0.3s ease;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .sidebar.collapsed {
      width: var(--sidebar-collapsed-width);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 20px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      height: var(--header-height);
    }

    .brand .logo {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      overflow: hidden;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: var(--transition);
    }

    .brand .logo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .brand .title {
      font-weight: 700;
      font-size: 18px;
      white-space: nowrap;
      transition: var(--transition);
    }

    .sidebar.collapsed .brand .title {
      opacity: 0;
      width: 0;
    }

    .nav-container {
      flex: 1;
      padding: 16px 0;
      overflow-y: auto;
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      border-radius: 0;
      color: rgba(255, 255, 255, 0.85);
      text-decoration: none;
      transition: var(--transition);
      border-left: 3px solid transparent;
      margin: 2px 0;
    }

    .nav-link:hover, .nav-link.active {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-left-color: rgba(255, 255, 255, 0.5);
    }

    .nav-link i {
      width: 24px;
      text-align: center;
      font-size: 16px;
    }

    .nav-link .label {
      white-space: nowrap;
      transition: var(--transition);
    }

    .sidebar.collapsed .nav-link .label {
      opacity: 0;
      width: 0;
    }

    /* Submenu */
    .submenu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
      background: rgba(0, 0, 0, 0.1);
    }

    .submenu.open {
      max-height: 300px;
    }

    .submenu .nav-link {
      padding-left: 48px;
      font-size: 14px;
    }

    /* ---------- Main Content ---------- */
    .main {
      margin-left: var(--sidebar-width);
      padding: 20px;
      transition: margin-left 0.3s ease;
      min-height: 100vh;
    }

    .sidebar.collapsed ~ .main {
      margin-left: var(--sidebar-collapsed-width);
    }

    /* Topbar */
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      margin-bottom: 24px;
      background: #fff;
      padding: 16px 24px;
      border-radius: var(--card-radius);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .searchbar {
      flex: 1;
      margin-right: 18px;
    }

    .searchbar input {
      width: 100%;
      padding: 10px 16px;
      border-radius: 8px;
      border: 1px solid var(--gray-300);
      background: var(--gray-100);
      transition: var(--transition);
    }

    .searchbar input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }

    .top-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .icon-btn {
      background: transparent;
      border: 0;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: var(--gray-600);
      transition: var(--transition);
      position: relative;
    }

    .icon-btn:hover {
      background: var(--gray-200);
      color: var(--gray-800);
    }

    .badge {
      position: absolute;
      top: 4px;
      right: 4px;
      font-size: 10px;
      padding: 2px 5px;
    }

    /* Welcome Banner */
    .banner {
      border-radius: var(--card-radius);
      padding: 24px;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 24px;
      box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
    }

    .banner-icon {
      width: 70px;
      height: 70px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }

    .banner .info h2 {
      margin: 0 0 8px 0;
      font-size: 24px;
      font-weight: 600;
    }

    .banner .info p {
      margin: 0;
      opacity: 0.9;
      font-size: 15px;
    }

    .quick-actions {
      display: flex;
      gap: 10px;
      margin-top: 16px;
      flex-wrap: wrap;
    }

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 24px;
    }

    .stat-card {
      padding: 20px;
      border-radius: var(--card-radius);
      background: #fff;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      transition: var(--transition);
      border-top: 4px solid transparent;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .stat-card.students {
      border-top-color: var(--primary);
    }

    .stat-card.classes {
      border-top-color: var(--info);
    }

    .stat-card.fees {
      border-top-color: var(--success);
    }

    .stat-card.attendance {
      border-top-color: var(--warning);
    }

    .stat-card .meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .stat-card .meta .left {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .avatar-sm {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 20px;
    }

    .avatar-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
    }

    .avatar-info {
      background: linear-gradient(135deg, var(--info), #5bc0de);
    }

    .avatar-success {
      background: linear-gradient(135deg, var(--success), #5cb85c);
    }

    .avatar-warning {
      background: linear-gradient(135deg, var(--warning), #f0ad4e);
    }

    .stat-card .value {
      font-size: 24px;
      font-weight: 600;
      margin: 4px 0 0 0;
    }

    .stat-card .label {
      font-size: 14px;
      color: var(--gray-600);
      margin: 0;
    }

    .stat-card .trend {
      font-size: 12px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .trend.up {
      color: var(--success);
    }

    .trend.down {
      color: var(--danger);
    }

    /* Charts + Timeline Layout */
    .grid-two {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 24px;
    }

    .card {
      border-radius: var(--card-radius);
      padding: 20px;
      background: #fff;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-bottom: 24px;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--gray-200);
    }

    .card-title {
      font-size: 18px;
      font-weight: 600;
      margin: 0;
    }

    .timeline {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .time-item {
      display: flex;
      gap: 12px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--gray-200);
    }

    .time-item:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }

    .time-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      flex-shrink: 0;
      margin-top: 4px;
      background: var(--primary);
    }

    .time-content {
      flex: 1;
    }

    .time-title {
      font-weight: 500;
      margin-bottom: 4px;
    }

    .time-date {
      font-size: 12px;
      color: var(--gray-600);
    }

    /* Responsive */
    @media (max-width: 992px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .grid-two {
        grid-template-columns: 1fr;
      }
      
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.mobile-open {
        transform: translateX(0);
      }
      
      .main {
        margin-left: 0;
        padding: 16px;
      }
      
      .topbar {
        padding: 12px 16px;
      }
    }

    @media (max-width: 768px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
      
      .banner {
        flex-direction: column;
        text-align: center;
        gap: 16px;
      }
      
      .quick-actions {
        justify-content: center;
      }
    }
  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="logo">
        <?php if(!empty($settings['school_logo'])): ?>
          <img src="<?= h('uploads/'.$settings['school_logo']) ?>" alt="logo">
        <?php else: ?>
          <i class="fas fa-graduation-cap"></i>
        <?php endif; ?>
      </div>
      <div class="title"><?= h($settings['school_name']) ?></div>
    </div>

    <div class="nav-container">
      <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-house"></i><span class="label">Dashboard</span></a>

      <a href="#" class="nav-link" id="studentsToggle"><i class="fa-solid fa-user-graduate"></i><span class="label">Students</span></a>
      <div class="submenu" id="studentsSub">
        <a href="students/list.php" class="nav-link"><i class="fa-solid fa-list"></i><span>All Students</span></a>
        <a href="students/add.php" class="nav-link"><i class="fa-solid fa-user-plus"></i><span>Add Student</span></a>
      </div>

      <a href="#" class="nav-link" id="classesToggle"><i class="fa-solid fa-chalkboard"></i><span class="label">Classes</span></a>
      <div class="submenu" id="classesSub">
        <a href="classes/list.php" class="nav-link"><i class="fa-solid fa-list"></i><span>List Classes</span></a>
        <a href="classes/add.php" class="nav-link"><i class="fa-solid fa-plus"></i><span>Add Class</span></a>
      </div>

      <a href="attendance/index.php" class="nav-link"><i class="fa-solid fa-calendar-check"></i><span class="label">Attendance</span></a>

      <a href="#" class="nav-link" id="feesToggle"><i class="fa-solid fa-money-bill-wave"></i><span class="label">Fees</span></a>
      <div class="submenu" id="feesSub">
        <a href="fees/create_fee.php" class="nav-link"><i class="fa-solid fa-file-invoice-dollar"></i><span>Create Fee</span></a>
        <a href="fees/payments.php" class="nav-link"><i class="fa-solid fa-receipt"></i><span>Payments</span></a>
      </div>

      <a href="reports/index.php" class="nav-link"><i class="fa-solid fa-chart-line"></i><span class="label">Reports</span></a>
      <a href="settings/index.php" class="nav-link"><i class="fa-solid fa-cog"></i><span class="label">Settings</span></a>
    </div>

    <div style="padding: 16px; border-top: 1px solid rgba(255,255,255,0.1);">
      <a href="logout.php" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i><span class="label">Logout</span></a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main" id="main">
    <!-- TOP BAR -->
    <div class="topbar">
      <div class="d-flex align-items-center gap-3" style="flex:1">
        <button class="icon-btn" id="btnToggleSidebar" title="Toggle sidebar">
          <i class="fa fa-bars"></i>
        </button>
        <div class="searchbar">
          <input id="searchInput" placeholder="Search students, classes, fees..." />
        </div>
      </div>

      <div class="top-actions">
        <!-- Notifications -->
        <div class="dropdown">
          <button class="icon-btn" id="btnNot" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <?php if(count($notifications) > 0): ?>
              <span class="badge bg-danger" id="notCount"><?= count($notifications) ?></span>
            <?php endif; ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:320px;">
            <li class="mb-1"><strong>Notifications</strong></li>
            <div id="notList">
              <?php if(empty($notifications)): ?>
                <li class="small text-muted">No notifications</li>
              <?php else: ?>
                <?php foreach($notifications as $n): ?>
                  <li style="list-style:none;padding:6px 0;border-bottom:1px solid #f1f1f1">
                    <div><strong><?= h($n['title']) ?></strong></div>
                    <div class="small text-muted"><?= h($n['created_at']) ?></div>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <li><a class="dropdown-item text-center small" href="activities.php">View all</a></li>
          </ul>
        </div>

        <!-- Theme Toggle -->
        <button class="icon-btn" id="themeBtn" title="Toggle dark mode"><i class="fa fa-moon"></i></button>

        <!-- Profile -->
        <div class="dropdown">
          <a class="btn btn-sm btn-outline-secondary dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="fa-solid fa-user"></i> <?= h($_SESSION['admin_name'] ?? 'Admin') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#">Profile</a></li>
            <li><a class="dropdown-item" href="settings/index.php">Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- WELCOME BANNER -->
    <section class="banner">
      <div class="banner-icon">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <div class="info">
        <h2>Welcome back, <?= h($_SESSION['admin_name'] ?? 'Admin') ?></h2>
        <p>Overview of classes, students, attendance and fees — quick insights for <?= h($settings['session_year'] ?? date('Y')) ?> session.</p>
        <div class="quick-actions">
          <a class="btn btn-light btn-sm" href="students/add.php"><i class="fa fa-plus me-1"></i> Add Student</a>
          <a class="btn btn-outline-light btn-sm" href="fees/create_fee.php"><i class="fa fa-money-bill me-1"></i> Create Fee</a>
          <a class="btn btn-outline-light btn-sm" href="attendance/mark.php"><i class="fa fa-check me-1"></i> Mark Attendance</a>
        </div>
      </div>
    </section>

    <!-- STATS GRID -->
    <section class="stats-grid">
      <div class="stat-card students">
        <div class="meta">
          <div class="left">
            <div class="avatar-sm avatar-primary">
              <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div>
              <p class="value"><?= number_format($totalStudents) ?></p>
              <p class="label">Total Students</p>
            </div>
          </div>
          <div class="right">
            <span class="trend up"><i class="fa-solid fa-arrow-up"></i> 5.2%</span>
          </div>
        </div>
      </div>

      <div class="stat-card classes">
        <div class="meta">
          <div class="left">
            <div class="avatar-sm avatar-info">
              <i class="fa-solid fa-chalkboard"></i>
            </div>
            <div>
              <p class="value"><?= number_format($totalClasses) ?></p>
              <p class="label">Active Classes</p>
            </div>
          </div>
          <div class="right">
            <span class="trend up"><i class="fa-solid fa-arrow-up"></i> 2.1%</span>
          </div>
        </div>
      </div>

      <div class="stat-card fees">
        <div class="meta">
          <div class="left">
            <div class="avatar-sm avatar-success">
              <i class="fa-solid fa-money-bill"></i>
            </div>
            <div>
              <p class="value">Rs <?= number_format($totalFees, 2) ?></p>
              <p class="label">Fees Collected</p>
            </div>
          </div>
          <div class="right">
            <span class="trend up"><i class="fa-solid fa-arrow-up"></i> 12.5%</span>
          </div>
        </div>
      </div>

      <div class="stat-card attendance">
        <div class="meta">
          <div class="left">
            <div class="avatar-sm avatar-warning">
              <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
              <p class="value"><?= number_format($attendanceToday) ?></p>
              <p class="label">Today's Attendance</p>
            </div>
          </div>
          <div class="right">
            <span class="trend down"><i class="fa-solid fa-arrow-down"></i> 1.8%</span>
          </div>
        </div>
      </div>
    </section>

    <!-- CHARTS + TIMELINE -->
    <section class="grid-two">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Fees Collection</h5>
          <div>
            <select id="feesRange" class="form-select form-select-sm">
              <option value="monthly" selected>Monthly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>
        </div>
        <canvas id="feesChart" height="120"></canvas>
        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-sm btn-outline-primary" id="exportFeesCSV">
            <i class="fa-solid fa-download me-1"></i> Export CSV
          </button>
          <small class="text-muted align-self-center ms-auto">Data shown based on selection</small>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Recent Activity</h5>
        </div>
        <div class="timeline" id="timeline">
          <?php if(empty($timeline)): ?>
            <div class="small text-muted text-center p-3">No activities yet</div>
          <?php else: ?>
            <?php foreach($timeline as $item): ?>
              <div class="time-item">
                <div class="time-dot"></div>
                <div class="time-content">
                  <div class="time-title"><?= h($item['description']) ?></div>
                  <div class="time-date"><?= h($item['created_at']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="mt-3 text-end">
          <a href="activities.php" class="small">View all activities <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>
      </div>
    </section>

  </main>

  <!-- inject JSON data for JS -->
  <script>
    const MONTH_LABELS = <?= $json_monthLabels ?>;
    const MONTHLY_FEES = <?= $json_monthlyFees ?>;
    const MONTHLY_ATTENDANCE = <?= $json_monthlyAttendance ?>;
    const YEAR_LABELS = <?= $json_yearLabels ?>;
    const YEARLY_FEES = <?= $json_yearlyFees ?>;
    const NOTIFICATIONS = <?= $json_notifications ?>;
    const TIMELINE = <?= $json_timeline ?>;
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Chart rendering + UI behavior -->
  <script>
  // ---------- sidebar toggle & submenu ----------
  const sidebar = document.getElementById('sidebar');
  document.getElementById('btnToggleSidebar')?.addEventListener('click', ()=>{
    sidebar.classList.toggle('collapsed');
  });

  function setupToggle(btnId, subId){
    const btn = document.getElementById(btnId);
    const sub = document.getElementById(subId);
    btn?.addEventListener('click', (e)=>{
      e.preventDefault();
      sub.classList.toggle('open');
    });
  }
  setupToggle('classesToggle','classesSub');
  setupToggle('studentsToggle','studentsSub');
  setupToggle('feesToggle','feesSub');

  // ---------- charts ----------
  const feesCtx = document.getElementById('feesChart').getContext('2d');
  let feesChart = new Chart(feesCtx, {
    type: 'bar',
    data: {
      labels: MONTH_LABELS,
      datasets: [{
        label: 'Fees Collected (Rs)',
        data: MONTHLY_FEES,
        backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || 'rgba(67,97,238,0.9)',
        borderRadius: 6
      }]
    },
    options: {
      responsive:true,
      plugins:{legend:{display:false}},
      scales:{y:{beginAtZero:true}}
    }
  });

  // change dataset for monthly/yearly
  document.getElementById('feesRange').addEventListener('change', function(){
    const val = this.value;
    if(val==='monthly'){
      feesChart.data.labels = MONTH_LABELS;
      feesChart.data.datasets[0].data = MONTHLY_FEES;
    } else {
      feesChart.data.labels = YEAR_LABELS;
      feesChart.data.datasets[0].data = YEARLY_FEES;
    }
    feesChart.update();
  });

  // CSV export
  document.getElementById('exportFeesCSV').addEventListener('click', ()=>{
    const sel = document.getElementById('feesRange').value;
    let labels = sel==='monthly' ? MONTH_LABELS : YEAR_LABELS;
    let data = sel==='monthly' ? MONTHLY_FEES : YEARLY_FEES;
    let csv = 'label,value\n';
    for(let i=0;i<labels.length;i++) csv += `${labels[i]},${data[i] || 0}\n`;
    const blob = new Blob([csv], {type:'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href=url; a.download = `fees_${sel}.csv`; a.click();
    URL.revokeObjectURL(url);
  });

  // ---------- dark mode toggle (saved in localStorage) ----------
  const themeBtn = document.getElementById('themeBtn');
  const body = document.body;
  const saved = localStorage.getItem('dashboardDark');
  
  // Initialize dark mode based on saved preference
  if(saved === '1'){ 
    body.classList.add('dark'); 
    themeBtn.innerHTML='<i class="fa fa-sun"></i>'; 
  } else { 
    body.classList.remove('dark'); 
    themeBtn.innerHTML='<i class="fa fa-moon"></i>'; 
  }
  
  themeBtn.addEventListener('click', ()=>{
    body.classList.toggle('dark');
    const isDark = body.classList.contains('dark');
    localStorage.setItem('dashboardDark', isDark ? '1' : '0');
    themeBtn.innerHTML = isDark ? '<i class="fa fa-sun"></i>' : '<i class="fa fa-moon"></i>';
    
    // Update chart colors for dark mode
    if(feesChart) {
      feesChart.update();
    }
  });

  // search quick jump (simple client-side)
  document.getElementById('searchInput').addEventListener('keydown', (e)=>{
    if(e.key==='Enter'){
      const q = e.target.value.trim();
      if(!q) return;
      // naive: redirect to students list with query param (you can implement server-side search)
      location.href = 'students/list.php?search=' + encodeURIComponent(q);
    }
  });

  </script>
</body>
</html>