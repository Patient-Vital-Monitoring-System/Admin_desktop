<?php
require_once __DIR__ . '/../../api/auth/config.php';

$audit_logs = [];
$audit_summary = [];
$login_logs = [];
$login_stats = [];

try {
    // Ensure login_audit table exists for login/logout history
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_audit (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL,
        action ENUM('login','logout') NOT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    // COMPREHENSIVE AUDIT LOG - All important system actions
    $stmt = $pdo->query("SELECT 
                            dl.log_id,
                            d.dev_serial,
                            r.resp_name as assigned_to,
                            m.mgmt_name as assigned_by,
                            dl.date_assigned as assignment_date,
                            CASE WHEN dl.date_returned IS NOT NULL THEN 'Yes' ELSE 'No' END as device_returned,
                            dl.date_returned as returned_at,
                            dl.verified_return as verification_status,
                            'Device Assignment' as action_type
                        FROM device_log dl
                        JOIN device d ON dl.dev_id = d.dev_id
                        LEFT JOIN responder r ON dl.resp_id = r.resp_id
                        LEFT JOIN management m ON dl.mgmt_id = m.mgmt_id
                        ORDER BY dl.date_assigned DESC
                        LIMIT 100");
    $device_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // INCIDENT TRANSFER LOG
    $stmt = $pdo->query("SELECT 
                            i.incident_id,
                            p.pat_name,
                            r.resp_name as transferred_by_responder,
                            resc.resc_name as transferred_to_rescuer,
                            i.start_time as incident_created,
                            i.end_time as transfer_date,
                            'Incident Transfer' as action_type
                        FROM incident i
                        JOIN patient p ON i.pat_id = p.pat_id
                        LEFT JOIN responder r ON i.resp_id = r.resp_id
                        LEFT JOIN rescuer resc ON i.resc_id = resc.resc_id
                        WHERE i.status = 'transferred'
                        ORDER BY i.end_time DESC
                        LIMIT 100");
    $transfer_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // INCIDENT COMPLETION LOG
    $stmt = $pdo->query("SELECT 
                            i.incident_id,
                            p.pat_name,
                            CASE WHEN i.resp_id IS NOT NULL THEN r.resp_name ELSE resc.resc_name END as completed_by,
                            i.start_time as incident_created,
                            i.end_time as completion_date,
                            TIMESTAMPDIFF(HOUR, i.start_time, i.end_time) as duration_hours,
                            'Incident Completion' as action_type
                        FROM incident i
                        JOIN patient p ON i.pat_id = p.pat_id
                        LEFT JOIN responder r ON i.resp_id = r.resp_id
                        LEFT JOIN rescuer resc ON i.resc_id = resc.resc_id
                        WHERE i.status IN ('completed', 'resolved')
                        ORDER BY i.end_time DESC
                        LIMIT 100");
    $completion_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // AUDIT SUMMARY STATISTICS
    $stmt = $pdo->query("SELECT COUNT(*) as total_assignments FROM device_log");
    $audit_summary['total_assignments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_assignments'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as devices_returned FROM device_log WHERE date_returned IS NOT NULL");
    $audit_summary['devices_returned'] = $stmt->fetch(PDO::FETCH_ASSOC)['devices_returned'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as verifications_completed FROM device_log WHERE verified_return = 1");
    $audit_summary['verifications_completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['verifications_completed'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as transfers_completed FROM incident WHERE status = 'transferred'");
    $audit_summary['transfers_completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['transfers_completed'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as completions FROM incident WHERE status IN ('completed', 'resolved')");
    $audit_summary['completions'] = $stmt->fetch(PDO::FETCH_ASSOC)['completions'] ?? 0;
    
    // Return rate calculation
    $audit_summary['return_rate'] = $audit_summary['total_assignments'] > 0 
        ? round(($audit_summary['devices_returned'] / $audit_summary['total_assignments']) * 100, 1)
        : 0;
    
    // Verification rate
    $audit_summary['verification_rate'] = $audit_summary['devices_returned'] > 0 
        ? round(($audit_summary['verifications_completed'] / $audit_summary['devices_returned']) * 100, 1)
        : 0;

    // LOGIN / LOGOUT STATS (last 7 days)
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) AS day,
            SUM(action = 'login') AS logins,
            SUM(action = 'logout') AS logouts
        FROM login_audit
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY day ASC
    ");
    $login_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Latest login/logout events
    $stmt = $pdo->query("
        SELECT email, role, action, ip_address, created_at
        FROM login_audit
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $login_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log('Audit log query failed: ' . $e->getMessage());
}

// GENERAL ACTIVITY LOG
$stmt = $pdo->query("
    SELECT user_name, user_role, action_type, module, created_at
    FROM activity_log
    ORDER BY created_at DESC
    LIMIT 50
");
$activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ACTIVITY SUMMARY FOR CHART
$stmt = $pdo->query("
    SELECT action_type, COUNT(*) as total
    FROM activity_log
    GROUP BY action_type
");
$activity_chart = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log & Audit Trail - Patient Vital Monitoring Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">Activity Log & Audit Trail</h2>
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
                        <a class="nav-link active" href="audit_log.php">System Activity Log</a>
                    </div>
                </div>

                <!-- Monitoring -->
                <div class="nav-group">
                    <button class="nav-group-toggle">Monitoring <span class="dropdown-arrow">▼</span></button>
                    <div class="nav-group-items">
                        <a class="nav-link" href="incidents.php">Incident Monitoring</a>
                        <a class="nav-link" href="device_incidents.php">Device Overview</a>
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
            <div style="padding: 32px 20px; max-width: 1600px; margin: 0 auto; width: 100%;">
            <h1>📊 Activity Log & Audit Trail</h1>
            <p>Complete system audit trail tracking all critical actions, assignments, transfers, and verifications.</p>

            <!-- AUDIT SUMMARY METRICS -->
            <div class="audit-summary">
                <div class="audit-metric">
                    <div class="audit-icon">📝</div>
                    <div class="audit-value"><?php echo $audit_summary['total_assignments']; ?></div>
                    <div class="audit-label">Total Assignments</div>
                </div>

                <div class="audit-metric">
                    <div class="audit-icon">↩️</div>
                    <div class="audit-value"><?php echo $audit_summary['devices_returned']; ?></div>
                    <div class="audit-label">Devices Returned</div>
                </div>

                <div class="audit-metric">
                    <div class="audit-icon">✅</div>
                    <div class="audit-value"><?php echo $audit_summary['verifications_completed']; ?></div>
                    <div class="audit-label">Verifications</div>
                </div>

                <div class="audit-metric">
                    <div class="audit-icon">🔄</div>
                    <div class="audit-value"><?php echo $audit_summary['transfers_completed']; ?></div>
                    <div class="audit-label">Transfers</div>
                </div>

                <div class="audit-metric">
                    <div class="audit-icon">✓</div>
                    <div class="audit-value"><?php echo $audit_summary['completions']; ?></div>
                    <div class="audit-label">Completions</div>
                </div>

                <div class="audit-metric">
                    <div class="audit-icon">📊</div>
                    <div class="audit-value"><?php echo $audit_summary['return_rate']; ?>%</div>
                    <div class="audit-label">Return Rate</div>
                </div>
            </div>

            <!-- LOGIN / LOGOUT ACTIVITY OVERVIEW -->
            <div class="audit-section">
                <h2>👤 User Session Activity</h2>
                <p style="margin-bottom: 20px; color: var(--muted);">
                    Login and logout history across all admin and field accounts (last 7 days).
                </p>

                <div class="charts-section" style="margin: 0 0 32px 0;">
                    <div class="chart-container">
                        <h3>Logins vs Logouts (Last 7 Days)</h3>
                        <canvas id="loginActivityChart" style="max-height: 260px;"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($login_logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['email']); ?></td>
                                    <td style="text-transform: capitalize;"><?php echo htmlspecialchars($log['role']); ?></td>
                                    <td>
                                        <span class="return-badge <?php echo $log['action'] === 'login' ? 'yes' : 'no'; ?>">
                                            <?php echo strtoupper($log['action']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['ip_address'] ? htmlspecialchars($log['ip_address']) : '<span style="color: var(--muted);">N/A</span>'; ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y g:i A', strtotime($log['created_at']))); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="audit-section">
<h2>📋 System Activity Log</h2>
<p style="margin-bottom:20px;color:var(--muted);">
Complete history of system actions performed by responders, rescuers, management, and administrators.
</p>

<div class="charts-section" style="margin:0 0 32px 0;">
<div class="chart-container">
<h3>System Activity Distribution</h3>
<canvas id="activityChart" style="max-height:260px;"></canvas>
</div>
</div>

<div class="card">
<div class="card-body">
<table class="table">
<thead>
<tr>
<th>User</th>
<th>Role</th>
<th>Action</th>
<th>Module</th>
<th>Timestamp</th>
</tr>
</thead>

<tbody>
<?php foreach ($activity_logs as $log): ?>
<tr>
<td><?php echo htmlspecialchars($log['user_name']); ?></td>

<td style="text-transform:capitalize;">
<?php echo htmlspecialchars($log['user_role']); ?>
</td>

<td><?php echo htmlspecialchars($log['action_type']); ?></td>

<td><?php echo htmlspecialchars($log['module']); ?></td>

<td>
<?php echo date('M d, Y g:i A', strtotime($log['created_at'])); ?>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>
</div>

            <!-- DEVICE ASSIGNMENT AUDIT LOG -->
            <div class="audit-section">
                <h2>🔑 Device Assignment Audit Log</h2>
                <p style="margin-bottom: 20px; color: var(--muted);">Track all device assignments, returns, and verifications.</p>
                
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Log ID</th>
                                    <th>Device Serial</th>
                                    <th>Assigned To</th>
                                    <th>Assigned By</th>
                                    <th>Assignment Date</th>
                                    <th>Returned</th>
                                    <th>Return Date</th>
                                    <th>Verified Return</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($device_logs as $log): ?>
                                <tr>
                                    <td><span class="log-id-badge"><?php echo htmlspecialchars($log['log_id']); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['dev_serial']); ?></td>
                                    <td><?php echo $log['assigned_to'] ? htmlspecialchars($log['assigned_to']) : '<span style="color: var(--muted);">N/A</span>'; ?></td>
                                    <td><?php echo $log['assigned_by'] ? htmlspecialchars($log['assigned_by']) : '<span style="color: var(--muted);">N/A</span>'; ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($log['assignment_date']))); ?></td>
                                    <td>
                                        <span class="return-badge <?php echo strtolower($log['device_returned']); ?>">
                                            <?php echo htmlspecialchars($log['device_returned']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['returned_at'] ? htmlspecialchars(date('M d, Y H:i', strtotime($log['returned_at']))) : '<span style="color: var(--muted);">—</span>'; ?></td>
                                    <td><?php echo $log['verification_status'] ? '<span class="return-badge yes">Yes</span>' : '<span class="return-badge no">No</span>'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- INCIDENT TRANSFER AUDIT LOG -->
            <div class="audit-section">
                <h2>🚑 Incident Transfer Audit Log</h2>
                <p style="margin-bottom: 20px; color: var(--muted);">Track all incident transfers from responders to rescuers/medical facilities.</p>
                
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Incident ID</th>
                                    <th>Patient Name</th>
                                    <th>Transferred By</th>
                                    <th>Transferred To</th>
                                    <th>Incident Created</th>
                                    <th>Transfer Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transfer_logs as $log): ?>
                                <tr>
                                    <td><span class="log-id-badge"><?php echo htmlspecialchars($log['incident_id']); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['pat_name']); ?></td>
                                    <td><?php echo $log['transferred_by_responder'] ? htmlspecialchars($log['transferred_by_responder']) : '<span style="color: var(--muted);">N/A</span>'; ?></td>
                                    <td><?php echo $log['transferred_to_rescuer'] ? htmlspecialchars($log['transferred_to_rescuer']) : '<span style="color: var(--muted);">N/A</span>'; ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($log['incident_created']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($log['transfer_date']))); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- INCIDENT COMPLETION AUDIT LOG -->
            <div class="audit-section">
                <h2>✅ Incident Completion Audit Log</h2>
                <p style="margin-bottom: 20px; color: var(--muted);">Track all completed and resolved incidents with duration and personnel responsible.</p>
                
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Incident ID</th>
                                    <th>Patient Name</th>
                                    <th>Completed By</th>
                                    <th>Created</th>
                                    <th>Completed</th>
                                    <th>Duration (hrs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completion_logs as $log): ?>
                                <tr>
                                    <td><span class="log-id-badge"><?php echo htmlspecialchars($log['incident_id']); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['pat_name']); ?></td>
                                    <td><?php echo $log['completed_by'] ? htmlspecialchars($log['completed_by']) : '<span style="color: var(--muted);">N/A</span>'; ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($log['incident_created']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($log['completion_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($log['duration_hours']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- COMPLIANCE METRICS -->
            <div class="compliance-section">
                <h2>🏛️ Compliance Metrics</h2>
                <div class="compliance-grid">
                    <div class="compliance-card">
                        <div class="comp-label">Device Return Compliance</div>
                        <div class="comp-value"><?php echo $audit_summary['return_rate']; ?>%</div>
                        <div class="comp-bar">
                            <div class="comp-bar-fill" style="width: <?php echo $audit_summary['return_rate']; ?>%"></div>
                        </div>
                    </div>

                    <div class="compliance-card">
                        <div class="comp-label">Verification Completion Rate</div>
                        <div class="comp-value"><?php echo $audit_summary['verification_rate']; ?>%</div>
                        <div class="comp-bar">
                            <div class="comp-bar-fill" style="width: <?php echo $audit_summary['verification_rate']; ?>%"></div>
                        </div>
                    </div>

                    <div class="compliance-card">
                        <div class="comp-label">Incident Transfer Compliance</div>
                        <div class="comp-value">
                            <?php 
                            $transfer_rate = $audit_summary['completions'] > 0 
                                ? round(($audit_summary['transfers_completed'] / $audit_summary['completions']) * 100, 1)
                                : 0;
                            echo $transfer_rate;
                            ?>%
                        </div>
                        <div class="comp-bar">
                            <div class="comp-bar-fill" style="width: <?php echo $transfer_rate; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        </main>
    </div>

    <script src="../js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if (!localStorage.getItem('vw_token')) {
            window.location.href = '../login.php';
        }

        // Login / Logout Activity Chart
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('loginActivityChart');
            if (!ctx) return;

            const stats = <?php echo json_encode($login_stats); ?>;
            const labels = stats.map(s => s.day);
            const logins = stats.map(s => Number(s.logins));
            const logouts = stats.map(s => Number(s.logouts));

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Logins',
                            data: logins,
                            backgroundColor: '#00e5ff',
                            borderColor: '#00c9e8',
                            borderWidth: 1
                        },
                        {
                            label: 'Logouts',
                            data: logouts,
                            backgroundColor: '#ff4d6d',
                            borderColor: '#ff3358',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#a0aec0' }
                        }
                    },
                    scales: {
                        y: {
                            ticks: { color: '#a0aec0' },
                            grid: { color: 'rgba(160, 174, 192, 0.1)' },
                            beginAtZero: true,
                            precision: 0
                        },
                        x: {
                            ticks: { color: '#a0aec0' },
                            grid: { color: 'rgba(160, 174, 192, 0.05)' }
                        }
                    }
                }
            });
        });

        // System Activity Chart (HORIZONTAL BAR)
document.addEventListener('DOMContentLoaded', function () {

    const activityCtx = document.getElementById('activityChart');
    if (!activityCtx) return;

    const activityData = <?php echo json_encode($activity_chart); ?>;

    const labels = activityData.map(a => a.action_type);
    const values = activityData.map(a => Number(a.total));

    new Chart(activityCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Activities',
                data: values,
                backgroundColor: [
                    '#00e5ff',
                    '#ff4d6d',
                    '#2ecc71',
                    '#f1c40f',
                    '#9b59b6',
                    '#3498db'
                ],
                borderRadius: 6,
                barThickness: 30
            }]
        },
        options: {
            indexAxis: 'y', // makes bars horizontal
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#a0aec0'
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        color: '#a0aec0'
                    },
                    grid: {
                        color: 'rgba(255,255,255,0.05)'
                    }
                },
                y: {
                    ticks: {
                        color: '#a0aec0'
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

});

    </script>
</body>
</html>
