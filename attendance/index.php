<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Dashboard - School CMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --success-color: #4cc9f0;
      --warning-color: #f8961e;
      --danger-color: #f72585;
      --light-bg: #f8f9fa;
      --dark-bg: #1e1e2f;
      --card-bg: #ffffff;
      --dark-card-bg: #2a2a3f;
      --text-color: #2a2a2a;
      --dark-text-color: #ffffff;
    }

    body {
      background: linear-gradient(135deg, #f8f9fa, #eef2f7);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      transition: all 0.3s ease-in-out;
      color: var(--text-color);
    }

    .dark-mode {
      background: var(--dark-bg);
      color: var(--dark-text-color);
    }

    .dashboard-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
    }

    .header-section {
      text-align: center;
      margin-bottom: 40px;
      position: relative;
    }

    .header-title {
      font-weight: 800;
      color: var(--primary-color);
      font-size: 2.5rem;
      margin-bottom: 10px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .header-subtitle {
      color: #6c757d;
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
    }

    .dark-mode .header-subtitle {
      color: #b0b0b0;
    }

    .dark-toggle {
      position: absolute;
      top: 0;
      right: 0;
      border: none;
      background: rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      border-radius: 50px;
      padding: 10px 15px;
      color: var(--primary-color);
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .dark-toggle:hover {
      transform: scale(1.05);
      background: rgba(255,255,255,0.3);
    }

    /* NEW: Back to Dashboard Button */
    .back-dashboard {
      position: absolute;
      top: 0;
      left: 0;
      background: rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      border: none;
      border-radius: 50px;
      padding: 10px 20px;
      color: var(--primary-color);
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      font-weight: 600;
    }

    .back-dashboard:hover {
      transform: scale(1.05);
      background: rgba(255,255,255,0.3);
      color: var(--primary-color);
    }

    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }

    .feature-card {
      background: var(--card-bg);
      border: none;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      transition: all 0.3s ease-in-out;
      overflow: hidden;
      padding: 0;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .dark-mode .feature-card {
      background: var(--dark-card-bg);
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .card-icon {
      height: 120px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3.5rem;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .card-icon::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255,255,255,0.1);
      transform: translateX(-100%);
      transition: transform 0.5s ease;
    }

    .feature-card:hover .card-icon::after {
      transform: translateX(100%);
    }

    .card-content {
      padding: 25px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .card-title {
      font-weight: 700;
      font-size: 1.4rem;
      margin-bottom: 10px;
    }

    .card-description {
      color: #6c757d;
      font-size: 0.95rem;
      margin-bottom: 20px;
      flex-grow: 1;
    }

    .dark-mode .card-description {
      color: #b0b0b0;
    }

    .card-btn {
      border: none;
      border-radius: 12px;
      padding: 12px 20px;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      text-align: center;
      color: white;
      text-decoration: none;
      display: block;
    }

    .card-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      color: white;
    }

    .stats-section {
      margin-top: 40px;
    }

    .stats-title {
      font-weight: 700;
      font-size: 1.5rem;
      margin-bottom: 25px;
      text-align: center;
      color: var(--primary-color);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .stat-card {
      background: var(--card-bg);
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      display: flex;
      align-items: center;
      transition: all 0.3s ease;
    }

    .dark-mode .stat-card {
      background: var(--dark-card-bg);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      margin-right: 15px;
      color: white;
    }

    .stat-info {
      flex-grow: 1;
    }

    .stat-value {
      font-weight: 700;
      font-size: 1.8rem;
      margin-bottom: 5px;
    }

    .stat-label {
      color: #6c757d;
      font-size: 0.9rem;
    }

    .dark-mode .stat-label {
      color: #b0b0b0;
    }

    @media (max-width: 768px) {
      .header-title {
        font-size: 2rem;
      }
      
      .dashboard-container {
        padding: 20px 15px;
      }
      
      .card-grid {
        grid-template-columns: 1fr;
      }
      
      .dark-toggle, .back-dashboard {
        position: relative;
        margin-bottom: 10px;
        display: inline-block;
      }
      
      .header-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
      }
    }
  </style>
</head>
<body>

<div class="dashboard-container">
  <div class="header-section">
    <!-- NEW: Back to Dashboard Button -->
    <a href="../dashboard.php" class="back-dashboard">
      <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>
    
    <button class="btn dark-toggle">
      <i class="fas fa-moon"></i> Dark Mode
    </button>
    
    <h1 class="header-title">
      <i class="fas fa-user-check"></i> Attendance Management
    </h1>
    <p class="header-subtitle">
      Efficiently manage student attendance records, generate reports, and track attendance patterns.
    </p>
  </div>

  <div class="card-grid">
    <div class="feature-card">
      <div class="card-icon" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
        <i class="fas fa-pen"></i>
      </div>
      <div class="card-content">
        <h3 class="card-title">Mark Attendance</h3>
        <p class="card-description">Record daily attendance for classes, mark present/absent students, and add notes for special cases.</p>
        <a href="mark.php" class="card-btn" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
          <i class="fas fa-arrow-right"></i> Mark Now
        </a>
      </div>
    </div>

    <div class="feature-card">
      <div class="card-icon" style="background: linear-gradient(135deg, #4cc9f0, #4895ef);">
        <i class="fas fa-eye"></i>
      </div>
      <div class="card-content">
        <h3 class="card-title">View Attendance</h3>
        <p class="card-description">Browse and search attendance records by date, class, or student with filtering options.</p>
        <a href="view.php" class="card-btn" style="background: linear-gradient(135deg, #4cc9f0, #4895ef);">
          <i class="fas fa-arrow-right"></i> View Records
        </a>
      </div>
    </div>

    <div class="feature-card">
      <div class="card-icon" style="background: linear-gradient(135deg, var(--warning-color), #f3722c);">
        <i class="fas fa-list"></i>
      </div>
      <div class="card-content">
        <h3 class="card-title">Attendance List</h3>
        <p class="card-description">Generate comprehensive lists of attendance data for specific periods or classes.</p>
        <a href="list.php" class="card-btn" style="background: linear-gradient(135deg, var(--warning-color), #f3722c);">
          <i class="fas fa-arrow-right"></i> Generate List
        </a>
      </div>
    </div>

    <div class="feature-card">
      <div class="card-icon" style="background: linear-gradient(135deg, var(--danger-color), #b5179e);">
        <i class="fas fa-chart-bar"></i>
      </div>
      <div class="card-content">
        <h3 class="card-title">Attendance Report</h3>
        <p class="card-description">Create detailed reports with analytics, trends, and insights on attendance patterns.</p>
        <a href="report.php" class="card-btn" style="background: linear-gradient(135deg, var(--danger-color), #b5179e);">
          <i class="fas fa-arrow-right"></i> Generate Report
        </a>
      </div>
    </div>
  </div>

  <div class="stats-section">
    <h2 class="stats-title">Attendance Overview</h2>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
          <div class="stat-value">92.5%</div>
          <div class="stat-label">Overall Attendance Rate</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4cc9f0, #4895ef);">
          <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-info">
          <div class="stat-value">1,248</div>
          <div class="stat-label">Present Today</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning-color), #f3722c);">
          <i class="fas fa-user-times"></i>
        </div>
        <div class="stat-info">
          <div class="stat-value">42</div>
          <div class="stat-label">Absent Today</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, var(--danger-color), #b5179e);">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-info">
          <div class="stat-value">18</div>
          <div class="stat-label">Students at Risk</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const toggle = document.querySelector('.dark-toggle');
  
  // Check for saved theme preference or respect OS preference
  const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
  const currentTheme = localStorage.getItem('theme');
  
  if (currentTheme === 'dark' || (!currentTheme && prefersDarkScheme.matches)) {
    document.body.classList.add('dark-mode');
    toggle.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
  }
  
  toggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    
    if (document.body.classList.contains('dark-mode')) {
      toggle.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
      localStorage.setItem('theme', 'dark');
    } else {
      toggle.innerHTML = '<i class="fas fa-moon"></i> Dark Mode';
      localStorage.setItem('theme', 'light');
    }
  });
</script>

</body>
</html>
