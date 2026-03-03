<?php
require_once __DIR__ . '/../../api/auth/config.php';

$manager_devices = [];
$manager_metrics = [];

try {
    // Manager Overview: Devices assigned to each manager
    $stmt = $pdo->query("SELECT 
                            m.mgmt_id,
                            m.mgmt_name,
                            m.mgmt_email,
                            COUNT(DISTINCT dl.dev_id) as devices_assigned,
                            COUNT(DISTINCT CASE WHEN i.status IN ('active', 'pending') THEN i.incident_id END) as active_incidents,
                            COUNT(DISTINCT CASE WHEN i.status IN ('completed', 'resolved') THEN i.incident_id END) as completed_incidents,
                            MAX(i.start_time) as last_activity
                        FROM management m
                        LEFT JOIN device_log dl ON m.mgmt_id = dl.mgmt_id
                        LEFT JOIN incident i ON dl.log_id = i.log_id
                        GROUP BY m.mgmt_id, m.mgmt_name, m.mgmt_email
                        ORDER BY devices_assigned DESC");
    $manager_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Resource Distribution Summary
    $stmt = $pdo->query("SELECT 
                            COUNT(DISTINCT m.mgmt_id) as total_managers,
                            COUNT(DISTINCT d.dev_id) as total_devices,
                            COUNT(DISTINCT CASE WHEN i.status IN ('active', 'pending') THEN i.incident_id END) as active_incidents
                        FROM management m
                        LEFT JOIN device_log dl ON m.mgmt_id = dl.mgmt_id
                        LEFT JOIN device d ON dl.dev_id = d.dev_id
                        LEFT JOIN incident i ON dl.log_id = i.log_id");
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    $manager_metrics['total_managers'] = $summary['total_managers'] ?? 0;
    $manager_metrics['total_devices'] = $summary['total_devices'] ?? 0;
    $manager_metrics['active_incidents'] = $summary['active_incidents'] ?? 0;
    $manager_metrics['avg_devices_per_manager'] = $manager_metrics['total_managers'] > 0 
        ? round($manager_metrics['total_devices'] / $manager_metrics['total_managers'], 1) 
        : 0;
    
} catch (Exception $e) {
    error_log('Manager query failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Device Overview - Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">Manager Device Overview</h2>
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
                        <a class="nav-link active" href="device_incidents.php">Device Overview</a>
                        <a class="nav-link" href="vitals.php">User Activity</a>
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
            <div style="padding: 32px 20px; max-width: 1400px; margin: 0 auto; width: 100%;">
            <h1>�‍💼 Manager Device Distribution</h1>
            <p>Overview of device assignments and incident management by field managers.</p>

            <!-- KEY METRICS -->
            <div class="tracking-metrics">
                <div class="metric-item">
                    <div class="metric-icon">👥</div>
                    <div class="metric-data">
                        <div class="metric-value"><?php echo $manager_metrics['total_managers']; ?></div>
                        <div class="metric-label">Total Managers</div>
                    </div>
                </div>

                <div class="metric-item">
                    <div class="metric-icon">📱</div>
                    <div class="metric-data">
                        <div class="metric-value"><?php echo $manager_metrics['total_devices']; ?></div>
                        <div class="metric-label">Total Devices</div>
                    </div>
                </div>

                <div class="metric-item">
                    <div class="metric-icon">⚖️</div>
                    <div class="metric-data">
                        <div class="metric-value"><?php echo $manager_metrics['avg_devices_per_manager']; ?></div>
                        <div class="metric-label">Avg Devices/Manager</div>
                    </div>
                </div>
                
                <div class="metric-item">
                    <div class="metric-icon">🔴</div>
                    <div class="metric-data">
                        <div class="metric-value"><?php echo $manager_metrics['active_incidents']; ?></div>
                        <div class="metric-label">Active Incidents</div>
                    </div>
                </div>
            </div>

            <!-- MANAGER DEVICE ASSIGNMENTS TABLE -->
            <div class="tracking-table-section">
                <h2>🔗 Manager Device Assignments</h2>
                <p style="margin-bottom: 20px; color: var(--muted);">Device distribution across field managers with incident oversight metrics.</p>
                
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Manager</th>
                                    <th>Email</th>
                                    <th>Devices Assigned</th>
                                    <th>Active Incidents</th>
                                    <th>Completed</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($manager_devices as $mgr): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mgr['mgmt_name']); ?></td>
                                    <td><?php echo htmlspecialchars($mgr['mgmt_email']); ?></td>
                                    <td>
                                        <span style="font-weight: 700; color: #00e5ff;">
                                            <?php echo $mgr['devices_assigned']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: #ff4d6d;">
                                            <?php echo $mgr['active_incidents']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color: #39ff14;">
                                            <?php echo $mgr['completed_incidents']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $mgr['last_activity'] ? htmlspecialchars($mgr['last_activity']) : '<span style="color: var(--muted);">Never</span>'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- MANAGER WORKLOAD SUMMARY -->
            <div class="efficiency-section">
                <h2>📊 Manager Workload Analysis</h2>
                
                <div class="efficiency-grid">
                    <div class="efficiency-box">
                        <div class="efficiency-icon">⭐</div>
                        <div class="efficiency-title">Top Manager</div>
                        <div class="efficiency-value">
                            <?php 
                            echo ($manager_devices[0] ? htmlspecialchars($manager_devices[0]['mgmt_name']) : 'N/A');
                            ?>
                        </div>
                        <div class="efficiency-subtext">
                            <?php 
                            echo ($manager_devices[0] ? $manager_devices[0]['devices_assigned'] . ' devices' : 'No managers');
                            ?>
                        </div>
                    </div>

                    <div class="efficiency-box">
                        <div class="efficiency-icon">✅</div>
                        <div class="efficiency-title">Total Device Coverage</div>
                        <div class="efficiency-value">
                            <?php 
                            echo $manager_metrics['total_devices'];
                            ?>
                        </div>
                        <div class="efficiency-subtext">Across all managers</div>
                    </div>

                    <div class="efficiency-box">
                        <div class="efficiency-icon">📈</div>
                        <div class="efficiency-title">Avg Workload</div>
                        <div class="efficiency-value">
                            <?php 
                            echo $manager_metrics['avg_devices_per_manager'];
                            ?>
                        </div>
                        <div class="efficiency-subtext">Devices per manager</div>
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
