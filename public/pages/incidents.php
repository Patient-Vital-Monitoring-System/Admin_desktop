<?php
require_once __DIR__ . '/../../api/auth/config.php';

$incident_data = [];
try {
    // Ongoing incidents
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM incident WHERE status IN ('active', 'pending')");
    $incident_data['ongoing'] = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    
    // Transferred incidents
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM incident WHERE status = 'transferred'");
    $incident_data['transferred'] = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    
    // Completed incidents
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM incident WHERE status IN ('completed', 'resolved')");
    $incident_data['completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    
    // Average incident duration (in hours)
    $stmt = $pdo->query("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, IFNULL(updated_at, NOW()))) as avg_duration FROM incident");
    $incident_data['avg_duration'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg_duration'] ?? 0;
    $incident_data['avg_duration'] = round($incident_data['avg_duration'], 1);
    
    // Detailed incident list
    $stmt = $pdo->query("SELECT i.incident_id, p.pat_name, i.status, i.created_at, i.updated_at, 
                                TIMESTAMPDIFF(HOUR, i.created_at, IFNULL(i.updated_at, NOW())) as duration_hours
                        FROM incident i 
                        JOIN patient p ON i.pat_id = p.pat_id 
                        ORDER BY i.created_at DESC");
    $incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log('Incidents query failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Monitoring - Patient Vital Monitoring Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">Incident Monitoring</h2>
    </nav>

    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h5 class="sidebar-title">Menu</h5>
            </div>
            <nav class="sidebar-nav">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="patients.php">Patient Records</a>
                <a class="nav-link" href="vitals.php">Vitals Reports</a>
                <a class="nav-link" href="vitals_analytics.php">Vital Statistics</a>
                <a class="nav-link active" href="incidents.php">Incident Monitoring</a>
                <a class="nav-link" href="device_incidents.php">Device Tracking</a>
                <a class="nav-link" href="audit_log.php">Activity Log</a>
                <a class="nav-link" href="alerts.php">Alert Records</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div style="padding: 32px 20px; max-width: 1400px; margin: 0 auto; width: 100%;">
            <h1>🚨 Incident Monitoring</h1>
            <p>Real-time monitoring and management of all incidents in the system.</p>

            <!-- INCIDENT SUMMARY CARDS -->
            <div class="incident-summary">
                <div class="summary-card">
                    <div class="summary-icon">📍</div>
                    <div class="summary-value"><?php echo $incident_data['ongoing']; ?></div>
                    <div class="summary-label">Ongoing Incidents</div>
                    <div class="summary-subtext">Active & Pending</div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">🔄</div>
                    <div class="summary-value"><?php echo $incident_data['transferred']; ?></div>
                    <div class="summary-label">Transferred Incidents</div>
                    <div class="summary-subtext">To Medical Facilities</div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">✅</div>
                    <div class="summary-value"><?php echo $incident_data['completed']; ?></div>
                    <div class="summary-label">Completed Incidents</div>
                    <div class="summary-subtext">Resolved & Closed</div>
                </div>

                <div class="summary-card">
                    <div class="summary-icon">⏱️</div>
                    <div class="summary-value"><?php echo $incident_data['avg_duration']; ?> hrs</div>
                    <div class="summary-label">Average Duration</div>
                    <div class="summary-subtext">Per Incident</div>
                </div>
            </div>

            <!-- DETAILED INCIDENTS TABLE -->
            <div class="incidents-table-section">
                <h2>📋 All Incidents</h2>
                <p style="margin-bottom: 20px; color: var(--muted);">Complete history of all incidents with duration and status tracking.</p>
                
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Incident ID</th>
                                    <th>Patient Name</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Completed</th>
                                    <th>Duration (hrs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incidents as $incident): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($incident['incident_id']); ?></td>
                                    <td><?php echo htmlspecialchars($incident['pat_name']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo htmlspecialchars($incident['status']); ?>">
                                            <?php echo htmlspecialchars($incident['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($incident['created_at']))); ?></td>
                                    <td><?php echo $incident['updated_at'] ? htmlspecialchars(date('M d, Y H:i', strtotime($incident['updated_at']))) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($incident['duration_hours']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- STATISTICS SECTION -->
            <div class="statistics-section">
                <h2>📊 Incident Statistics</h2>
                
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-title">Total Incidents</div>
                        <div class="stat-number"><?php echo $incident_data['ongoing'] + $incident_data['transferred'] + $incident_data['completed']; ?></div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-title">Completion Rate</div>
                        <div class="stat-number">
                            <?php 
                            $total = $incident_data['ongoing'] + $incident_data['transferred'] + $incident_data['completed'];
                            $rate = $total > 0 ? round(($incident_data['completed'] / $total) * 100, 1) : 0;
                            echo $rate . '%';
                            ?>
                        </div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-title">Transfer Rate</div>
                        <div class="stat-number">
                            <?php 
                            $transfer_rate = $total > 0 ? round(($incident_data['transferred'] / $total) * 100, 1) : 0;
                            echo $transfer_rate . '%';
                            ?>
                        </div>
                    </div>

                    <div class="stat-box">
                        <div class="stat-title">Active Rate</div>
                        <div class="stat-number">
                            <?php 
                            $active_rate = $total > 0 ? round(($incident_data['ongoing'] / $total) * 100, 1) : 0;
                            echo $active_rate . '%';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

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
