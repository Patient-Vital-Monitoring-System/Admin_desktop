<?php
require_once __DIR__ . '/../../api/auth/config.php';
$activity_rows = [];
try {
    // Responder-Rescuer Activity: Show incidents handled by each responder/rescuer
    $stmt = $pdo->query("SELECT 
                            r.resp_id,
                            r.resp_name,
                            r.resp_email,
                            COUNT(DISTINCT i.incident_id) as incidents_handled,
                            COUNT(DISTINCT CASE WHEN i.status IN ('active', 'pending') THEN i.incident_id END) as active_incidents,
                            MAX(i.start_time) as last_activity,
                            'responder' as role
                        FROM responder r
                        LEFT JOIN incident i ON i.resp_id = r.resp_id
                        GROUP BY r.resp_id, r.resp_name, r.resp_email
                        ORDER BY incidents_handled DESC");
    $responders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT 
                            rc.resc_id,
                            rc.resc_name,
                            rc.resc_email,
                            COUNT(DISTINCT i.incident_id) as incidents_handled,
                            COUNT(DISTINCT CASE WHEN i.status IN ('active', 'pending') THEN i.incident_id END) as active_incidents,
                            MAX(i.start_time) as last_activity,
                            'rescuer' as role
                        FROM rescuer rc
                        LEFT JOIN incident i ON i.resc_id = rc.resc_id
                        GROUP BY rc.resc_id, rc.resc_name, rc.resc_email
                        ORDER BY incidents_handled DESC");
    $rescuers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $activity_rows = array_merge($responders, $rescuers);
} catch (Exception $e) {
    error_log('Activity query failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Monitoring - Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">User Activity Monitor</h2>
    </nav>

    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h5 class="sidebar-title">Menu</h5>
            </div>
            <nav class="sidebar-nav">
                <a class="nav-link" href="index.php">Dashboard</a>

                <!-- User Management -->
                <div class="nav-group">
                    <button class="nav-group-toggle">User Management <span class="dropdown-arrow">▼</span></button>
                    <div class="nav-group-items">
                        <a class="nav-link" href="patients.php">Staff Directory</a>
                        <a class="nav-link" href="user_status.php">User Status</a>
                    </div>
                </div>

                <!-- Reports -->
                <div class="nav-group">
                    <button class="nav-group-toggle">Reports <span class="dropdown-arrow">▼</span></button>
                    <div class="nav-group-items">
                        <a class="nav-link" href="vitals_analytics.php">Vital Statistics</a>
                        <a class="nav-link" href="audit_log.php">System Activity Log</a>
                    </div>
                </div>

                <!-- Monitoring -->
                <div class="nav-group">
                    <button class="nav-group-toggle">Monitoring <span class="dropdown-arrow">▼</span></button>
                    <div class="nav-group-items">
                        <a class="nav-link" href="incidents.php">Incident Monitoring</a>
                        <a class="nav-link" href="device_incidents.php">Device Overview</a>
                        <a class="nav-link active" href="vitals.php">User Activity</a>
                    </div>
                </div>

                <!-- Accounts -->
                <div class="nav-group">
                    <button class="nav-group-toggle">Accounts <span class="dropdown-arrow">▼</span></button>
                    <div class="nav-group-items">
                        <a class="nav-link" href="profile.php">Profile</a>
                        <a class="nav-link" href="logout.php" style="color: #ff4d6d;">Logout</a>
                    </div>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div style="padding: 32px 20px; max-width: 1200px; margin: 0 auto; width: 100%;">
            <h1>👤 Responder & Rescuer Activity</h1>
            <p>Track incident involvement and activity levels for all field staff members.</p>
            <?php if (!empty($activity_rows)): ?>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Incidents Handled</th><th>Active</th><th>Last Activity</th></tr></thead>
                        <tbody>
                            <?php foreach($activity_rows as $person): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($person['resp_name'] ?? $person['resc_name']); ?></td>
                                <td><?php echo htmlspecialchars($person['resp_email'] ?? $person['resc_email']); ?></td>
                                <td>
                                    <span style="padding: 4px 8px; border-radius: 4px; background: <?php echo $person['role'] === 'responder' ? '#00e5ff' : '#ff4d6d'; ?>20; color: <?php echo $person['role'] === 'responder' ? '#00e5ff' : '#ff4d6d'; ?>; font-weight: 600;">
                                        <?php echo htmlspecialchars($person['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo $person['incidents_handled']; ?></td>
                                <td><?php echo $person['active_incidents']; ?></td>
                                <td><?php echo $person['last_activity'] ? htmlspecialchars($person['last_activity']) : '<span style="color: var(--muted);">Never</span>'; ?></td>
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