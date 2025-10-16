<?php
// fees/receipt.php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['admin_id'])) { 
    header('Location: ../login.php'); 
    exit; 
}

$fee_id = isset($_GET['fee_id']) ? (int)$_GET['fee_id'] : 0;
if (!$fee_id) {
    die("Invalid request.");
}

// Initialize variables with default values
$fee = null;
$receiptNo = 'N/A';

try {
    $q = "
     SELECT f.*, s.name AS student_name, s.parent_name, s.photo, s.roll_no, c.name AS class_name
     FROM fees f
     JOIN students s ON f.student_id = s.id
     LEFT JOIN classes c ON s.class_id = c.id
     WHERE f.id = ?
    ";
    $stmt = $pdo->prepare($q);
    $stmt->execute([$fee_id]);
    $fee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($fee) {
        // receipt number format: CMS-YYYY-XXX
        $receiptNo = 'CMS-' . date('Y') . '-' . str_pad($fee['id'], 3, '0', STR_PAD_LEFT);
    } else {
        die("Fee record not found.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Check if fee data exists before proceeding
if (!$fee) {
    die("No fee data available.");
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Receipt - CMS School</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .receipt { 
      max-width:800px; 
      margin:auto; 
      background:#fff; 
      padding:30px; 
      border-radius:8px; 
      box-shadow:0 0 10px rgba(0,0,0,0.08); 
      border: 2px solid #e0e0e0;
    }
    .photo{
      width:90px;
      height:90px;
      object-fit:cover;
      border-radius:50%;
      border: 2px solid #dee2e6;
    }
    .header-section {
      border-bottom: 3px double #6c757d;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }
    .receipt-table th {
      background-color: #f8f9fa;
      font-weight: 600;
    }
    .signature-section {
      border-top: 2px dashed #6c757d;
      padding-top: 20px;
      margin-top: 30px;
    }
    .school-name {
      color: #2c3e50;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .receipt-title {
      color: #495057;
      font-size: 1.2rem;
      font-weight: 500;
    }
    @media print { 
      .no-print{display:none;} 
      .receipt {
        box-shadow: none;
        border: 1px solid #000;
      }
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="receipt">
    <!-- Header Section -->
    <div class="header-section text-center">
      <h2 class="school-name">CMS School</h2>
      <div class="receipt-title">Fee Payment Receipt</div>
    </div>

    <!-- Student & Receipt Info -->
    <div class="d-flex mb-4">
      <div class="d-flex align-items-start">
        <div>
          <?php if (!empty($fee['photo']) && file_exists(__DIR__ . '/../' . $fee['photo'])): ?>
            <img src="../<?= htmlspecialchars($fee['photo']) ?>" class="photo" alt="photo">
          <?php else: ?>
            <img src="https://via.placeholder.com/90" class="photo" alt="no photo">
          <?php endif; ?>
        </div>
        <div class="ms-3">
          <h5 class="text-primary"><?= htmlspecialchars($fee['student_name'] ?? 'N/A') ?></h5>
          <div><strong>Parent:</strong> <?= htmlspecialchars($fee['parent_name'] ?? '-') ?></div>
          <div><strong>Class:</strong> <?= htmlspecialchars($fee['class_name'] ?? '-') ?></div>
          <div><strong>Roll:</strong> <?= htmlspecialchars($fee['roll_no'] ?? '-') ?></div>
        </div>
      </div>
      <div class="ms-auto text-end bg-light p-3 rounded">
        <div><strong>Receipt #</strong></div>
        <div class="text-success fw-bold"><?= htmlspecialchars($receiptNo) ?></div>
        <div class="mt-2"><small class="text-muted"><?= date('Y-m-d') ?></small></div>
      </div>
    </div>

    <!-- Payment Details Table -->
    <table class="table table-bordered mb-4 receipt-table">
      <tr>
        <th width="30%">Amount Paid</th>
        <td class="fw-bold text-success">PKR <?= number_format($fee['amount'] ?? 0, 2) ?></td>
      </tr>
      <tr>
        <th>Due Date</th>
        <td><?= htmlspecialchars($fee['due_date'] ?? '-') ?></td>
      </tr>
      <tr>
        <th>Payment Status</th>
        <td>
          <?php if (isset($fee['status'])): ?>
            <span class="badge bg-<?= $fee['status'] == 'paid' ? 'success' : 'warning' ?>">
              <?= ucfirst(htmlspecialchars($fee['status'])) ?>
            </span>
          <?php else: ?>
            <span class="badge bg-secondary">Unknown</span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <th>Description</th>
        <td><?= htmlspecialchars($fee['description'] ?? '-') ?></td>
      </tr>
    </table>

    <!-- Signature Section -->
    <div class="signature-section d-flex justify-content-between">
      <div>
        <p><strong>Received By:</strong> ____________________</p>
        <small class="text-muted">School Authority</small>
      </div>
      <div class="text-end">
        <p><strong>Signature</strong> ____________________</p>
        <small class="text-muted">Generated by CMS School</small>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center mt-4 no-print">
      <button onclick="window.print()" class="btn btn-primary px-4">🖨️ Print Receipt</button>
      <a href="payments.php" class="btn btn-outline-secondary px-4">← Back to Payments</a>
    </div>
  </div>
</div>
</body>
</html>