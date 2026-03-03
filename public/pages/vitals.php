<?php
require_once __DIR__ . '/../../api/auth/config.php';
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
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">Patient Vitals Admin</h2>
    </nav>

    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h5 class="sidebar-title">Menu</h5>
            </div>
            <nav class="sidebar-nav">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="patients.php">Patient Records</a>
                <a class="nav-link active" href="vitals.php">Vitals Reports</a>
                <a class="nav-link" href="incidents.php">Incident Monitoring</a>
                <a class="nav-link" href="device_incidents.php">Device Tracking</a>
                <a class="nav-link" href="audit_log.php">Activity Log</a>
                <a class="nav-link" href="alerts.php">Alert Records</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div style="padding: 32px 20px; max-width: 1200px; margin: 0 auto; width: 100%;">
            <h1>Vitals Reports Page</h1>
            <p>View and analyze patient vital statistics below.</p>
            <?php if (!empty($rows)): ?>
            <div class="card">
                <div class="card-body">
                    <table class="table">
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
            <div class="alert alert-info">No vital records found.</div>
            <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../js/script.js"></script>
    <script>
        if (!localStorage.getItem('vw_token')) {
            window.location.href = '../login.php';
        }
    </script>
</body>
</html>