<?php
require_once __DIR__ . '/../../api/auth/config.php';

$device_incidents = [];
$summary_data = [];

try {
    // Device-Incident Mapping with Assignment Details
    $stmt = $pdo->query("SELECT 
                            d.dev_id, 
                            d.dev_serial, 
                            d.dev_status,
                            COUNT(dl.log_id) as total_uses,
                            COUNT(CASE WHEN i.status IN ('active', 'pending') THEN 1 END) as active_incidents,
                            r.resp_name,
                            m.mgmt_name
                        FROM device d
                        LEFT JOIN device_log dl ON d.dev_id = dl.dev_id
                        LEFT JOIN incident i ON dl.log_id = i.log_id
                        LEFT JOIN responder r ON dl.resp_id = r.resp_id
                        LEFT JOIN management m ON dl.mgmt_id = m.mgmt_id
                        GROUP BY d.dev_id, d.dev_serial, d.dev_status
                        ORDER BY COUNT(dl.log_id) DESC");
    $device_incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Incident Summary by Device
    $stmt = $pdo->query("SELECT 
                            d.dev_serial,
                            COUNT(DISTINCT i.incident_id) as incident_count,
                            COUNT(CASE WHEN i.status IN ('active', 'pending') THEN 1 END) as ongoing,
                            COUNT(CASE WHEN i.status = 'transferred' THEN 1 END) as transferred,
                            COUNT(CASE WHEN i.status IN ('completed', 'resolved') THEN 1 END) as completed
                        FROM device d
                        JOIN device_log dl ON d.dev_id = dl.dev_id
                        JOIN incident i ON dl.log_id = i.log_id
                        GROUP BY d.dev_id, d.dev_serial
                        ORDER BY incident_count DESC");
    $incident_by_device = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Key Metrics
    $stmt = $pdo->query("SELECT COUNT(DISTINCT d.dev_id) as active_devices FROM device d WHERE d.dev_status = 'assigned'");
    $summary_data['active_devices'] = $stmt->fetch(PDO::FETCH_ASSOC)['active_devices'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(DISTINCT i.incident_id) as total_tracked FROM incident i");
    $summary_data['total_tracked'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_tracked'] ?? 0;
    
    $stmt = $pdo->query("SELECT AVG(dl.log_id) as avg_incidents_per_device FROM (SELECT d.dev_id, COUNT(dl.log_id) as log_id FROM device d LEFT JOIN device_log dl ON d.dev_id = dl.dev_id GROUP BY d.dev_id) as subq");
    $summary_data['avg_per_device'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_incidents_per_device'] ?? 0, 1);
    
} catch (Exception $e) {
    error_log('Device-Incident query failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Incident Tracking - Patient Vital Monitoring Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">Device Incident Tracking</h2>
    </nav>

    <div class="page-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h5 class="sidebar-title">Menu</h5>
            </div>
            <nav class="sidebar-nav">
                <a class="nav-link" href="index.php">Dashboard</a>

                <!-- User Management -->
                <a class="nav-link" href="patients.php">User Management</a>
                <a class="nav-link" href="user_status.php">User Status</a>

                <!-- Monitoring -->
                <a class="nav-link" href="incidents.php">Incident Monitoring</a>
                <a class="nav-link active" href="device_incidents.php">Device Overview</a>

                <!-- Reports -->
                <a class="nav-link" href="vitals_analytics.php">Vital Statistics</a>
                <a class="nav-link" href="audit_log.php">System Activity Log</a>

                <!-- Account -->
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php" style="color: #ff4d6d;">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div style="padding: 32px 20px; max-width: 1400px; margin: 0 auto; width: 100%;">
            <h1>📱 Device Incident Tracking</h1>
            <p>Monitor incident assignments and device utilization across all active devices.</p>

            <!-- KEY METRICS -->
            <div class="tracking-metrics">
                <div class="metric-item">
                    <div class="metric-icon">📍</div>
                    <div class="metric-data">
                        <div class="metric-value"><?php echo $summary_data['active_devices']; ?></div>
                        <div class="metric-label">Active Devices</div>
                    </div>
                </div>

                <div class="metric-item">
                    <div class="metric-icon">📊</div>
                    <div class="metric-data">
                        <div class="metric-value"><?php echo $summary_data['total_tracked']; ?></div>
                        <div class="metric-label">Total Incidents Tracked</div>
                    </div>
                </div>

                <div class="metric-item">
                    <div class="metric-icon">⚖️</div>
                    <div class="metric-data">
                        <div class="metric-value"><?php echo $summary_data['avg_per_device']; ?></div>
                        <div class="metric-label">Avg Incidents/Device</div>
                    </div>
                </div>
            </div>

            <!-- DEVICE ASSIGNMENT & INCIDENT COUNT TABLE -->
            <div class="tracking-table-section">
                <h2>🔗 Device-Incident Assignments</h2>
                <p style="margin-bottom: 20px; color: var(--muted);">Current device assignments with active incident counts and responder information.</p>
                
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Device ID</th>
                                    <th>Serial Number</th>
                                    <th>Status</th>
                                    <th>Total Uses</th>
                                    <th>Active Incidents</th>
                                    <th>Assigned To</th>
                                    <th>Managed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($device_incidents as $dev): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dev['dev_id']); ?></td>
                                    <td><?php echo htmlspecialchars($dev['dev_serial']); ?></td>
                                    <td>
                                        <span class="device-badge <?php echo htmlspecialchars($dev['dev_status']); ?>">
                                            <?php echo htmlspecialchars($dev['dev_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $dev['total_uses']; ?></td>
                                    <td>
                                        <span style="color: #ff4d6d; font-weight: 700;">
                                            <?php echo $dev['active_incidents']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $dev['resp_name'] ? htmlspecialchars($dev['resp_name']) : '<span style="color: var(--muted);">Unassigned</span>'; ?></td>
                                    <td><?php echo $dev['mgmt_name'] ? htmlspecialchars($dev['mgmt_name']) : '<span style="color: var(--muted);">N/A</span>'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- INCIDENT BREAKDOWN BY DEVICE -->
            <div class="incident-breakdown-section">
                <h2>📈 Incident Breakdown by Device</h2>
                <p style="margin-bottom: 20px; color: var(--muted);">Detailed incident status distribution for each device.</p>
                
                <div class="breakdown-grid">
                    <?php foreach ($incident_by_device as $dev): ?>
                    <div class="breakdown-card">
                        <div class="card-header-custom">
                            <span class="device-name"><?php echo htmlspecialchars($dev['dev_serial']); ?></span>
                            <span class="total-count"><?php echo $dev['incident_count']; ?> incidents</span>
                        </div>
                        <div class="breakdown-stats">
                            <div class="breakdown-stat">
                                <div class="stat-label">Ongoing</div>
                                <div class="stat-value ongoing-color"><?php echo $dev['ongoing']; ?></div>
                            </div>
                            <div class="breakdown-stat">
                                <div class="stat-label">Transferred</div>
                                <div class="stat-value transferred-color"><?php echo $dev['transferred']; ?></div>
                            </div>
                            <div class="breakdown-stat">
                                <div class="stat-label">Completed</div>
                                <div class="stat-value completed-color"><?php echo $dev['completed']; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- DEVICE EFFICIENCY METRICS -->
            <div class="efficiency-section">
                <h2>⚙️ Device Efficiency Metrics</h2>
                
                <div class="efficiency-grid">
                    <div class="efficiency-box">
                        <div class="efficiency-icon">🎯</div>
                        <div class="efficiency-title">Highest Activity Device</div>
                        <div class="efficiency-value">
                            <?php 
                            echo $device_incidents[0]['dev_serial'] ?? 'N/A';
                            ?>
                        </div>
                        <div class="efficiency-subtext">
                            <?php 
                            echo ($device_incidents[0]['total_uses'] ?? 0) . ' uses';
                            ?>
                        </div>
                    </div>

                    <div class="efficiency-box">
                        <div class="efficiency-icon">📴</div>
                        <div class="efficiency-title">Idle Devices</div>
                        <div class="efficiency-value">
                            <?php 
                            $idle_count = 0;
                            foreach ($device_incidents as $dev) {
                                if ($dev['total_uses'] == 0) $idle_count++;
                            }
                            echo $idle_count;
                            ?>
                        </div>
                        <div class="efficiency-subtext">Not yet deployed</div>
                    </div>

                    <div class="efficiency-box">
                        <div class="efficiency-icon">✅</div>
                        <div class="efficiency-title">Assignment Coverage</div>
                        <div class="efficiency-value">
                            <?php 
                            $assigned = 0;
                            $total = count($device_incidents);
                            foreach ($device_incidents as $dev) {
                                if ($dev['dev_status'] === 'assigned') $assigned++;
                            }
                            echo $total > 0 ? round(($assigned / $total) * 100, 1) : 0;
                            ?>%
                        </div>
                        <div class="efficiency-subtext">Of all devices</div>
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
