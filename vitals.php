<?php
require_once __DIR__ . '/api/auth/config.php';
$rows = [];
try {
    $stmt = $pdo->query("SELECT v.*, i.incident_id, p.pat_name FROM vitalstat v
        JOIN incident i ON v.incident_id = i.incident_id
        JOIN patient p ON i.pat_id = p.pat_id
        ORDER BY v.vital_id DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Vitals query failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Vital Monitoring Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <nav class="navbar-top">
        <button class="btn btn-primary menu-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop" aria-controls="staticBackdrop">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h2 class="navbar-brand">Patient Vitals Admin</h2>
    </nav>

    <div class="offcanvas offcanvas-start" data-bs-backdrop="static" tabindex="-1" id="staticBackdrop" aria-labelledby="staticBackdropLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="staticBackdropLabel">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="navbar-nav flex-column">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="patients.php">Patient Records</a>
                <a class="nav-link" href="vitals.php">Vitals Reports</a>
                <a class="nav-link" href="alerts.php">Alert Records</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </nav>
        </div>
    </div>

    <h1>Vitals Reports Page</h1>
    <p>View and analyze patient vital statistics below.</p>
    <?php if (!empty($rows)): ?>
    <div class="card" style="margin:20px;">
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Patient</th><th>Incident</th><th>BP</th><th>HR</th><th>O₂</th><th>Recorded</th></tr></thead>
                <tbody>
                    <?php foreach($rows as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['vital_id']); ?></td>
                        <td><?php echo htmlspecialchars($r['pat_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['incident_id']); ?></td>
                        <td><?php echo htmlspecialchars($r['bp_systolic'].'/'.$r['bp_diastolic']); ?></td>
                        <td><?php echo htmlspecialchars($r['heart_rate']); ?></td>
                        <td><?php echo htmlspecialchars($r['oxygen_level']); ?>%</td>
                        <td><?php echo htmlspecialchars($r['recorded_at'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info" style="margin:20px;">No vital records found.</div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        if (!localStorage.getItem('vw_token')) {
            window.location.href = 'login.php';
        }
    </script>
</body>
</html>