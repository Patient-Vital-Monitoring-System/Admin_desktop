<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Vital Monitoring Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a class="nav-link active" href="index.php">Home</a>
                <a class="nav-link" href="patients.php">Patient Records</a>
                <a class="nav-link" href="vitals.php">Vitals Reports</a>
                <a class="nav-link" href="alerts.php">Alert Records</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div style="padding: 32px 20px; max-width: 1400px; margin: 0 auto; width: 100%;">
            <h1>Dashboard</h1>
            <p>Real-time system overview and analytics.</p>

            <?php
            require_once __DIR__ . '/../../api/auth/config.php';
            $data = [];
            
            try {
                // TOP METRICS
                $userCount = 0;
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM admin");
                $userCount += $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM responder");
                $userCount += $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM rescuer");
                $userCount += $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
                $data['users'] = $userCount;
                
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM device");
                $data['devices'] = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
                
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM incident WHERE status IN ('active', 'pending')");
                $data['active'] = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
                
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM incident WHERE status IN ('completed', 'transferred', 'resolved')");
                $data['completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
                
                // INCIDENT STATUS CHART DATA
                $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM incident GROUP BY status");
                $incident_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // DEVICE STATUS CHART DATA
                $stmt = $pdo->query("SELECT dev_status, COUNT(*) as count FROM device GROUP BY dev_status");
                $device_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // RECENT ACTIVITY (last 5 incidents)
                $stmt = $pdo->query("SELECT i.incident_id, p.pat_name, i.status, i.created_at FROM incident i 
                                     JOIN patient p ON i.pat_id = p.pat_id 
                                     ORDER BY i.created_at DESC LIMIT 5");
                $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // RECENTLY COMPLETED INCIDENTS (last 5)
                $stmt = $pdo->query("SELECT i.incident_id, p.pat_name, i.updated_at FROM incident i 
                                     JOIN patient p ON i.pat_id = p.pat_id 
                                     WHERE i.status IN ('completed', 'resolved') 
                                     ORDER BY i.updated_at DESC LIMIT 5");
                $completed_incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // DEVICES PENDING VERIFICATION (maintenance status)
                $stmt = $pdo->query("SELECT d.dev_id, d.dev_serial, d.dev_status FROM device d 
                                     WHERE d.dev_status IN ('maintenance', 'inactive') 
                                     LIMIT 5");
                $pending_devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                error_log('Dashboard query failed: ' . $e->getMessage());
            }
            ?>

            <!-- TOP ROW: METRIC CARDS -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon">👥</div>
                    <div class="metric-value"><?php echo $data['users'] ?? 0; ?></div>
                    <div class="metric-label">Total Users</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">📱</div>
                    <div class="metric-value"><?php echo $data['devices'] ?? 0; ?></div>
                    <div class="metric-label">Total Devices</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">🚨</div>
                    <div class="metric-value"><?php echo $data['active'] ?? 0; ?></div>
                    <div class="metric-label">Active Incidents</div>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">✓</div>
                    <div class="metric-value"><?php echo $data['completed'] ?? 0; ?></div>
                    <div class="metric-label">Completed Incidents</div>
                </div>
            </div>

            <!-- MIDDLE SECTION: CHARTS -->
            <div class="charts-section">
                <div class="chart-container">
                    <h3>Incident Status Distribution</h3>
                    <canvas id="incidentChart" style="max-width: 100%; height: 300px;"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Device Status Distribution</h3>
                    <canvas id="deviceChart" style="max-width: 100%; height: 300px;"></canvas>
                </div>
            </div>

            <!-- BOTTOM SECTION: ACTIVITY LOGS -->
            <div class="activity-section">
                <div class="activity-card">
                    <h3>📋 Recent Activity Log</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Incident ID</th>
                                <th>Patient</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activity as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['incident_id']); ?></td>
                                <td><?php echo htmlspecialchars($activity['pat_name']); ?></td>
                                <td><span class="status-badge <?php echo htmlspecialchars($activity['status']); ?>"><?php echo htmlspecialchars($activity['status']); ?></span></td>
                                <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($activity['created_at']))); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="activity-card">
                    <h3>✓ Recently Completed Incidents</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Incident ID</th>
                                <th>Patient</th>
                                <th>Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completed_incidents as $incident): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($incident['incident_id']); ?></td>
                                <td><?php echo htmlspecialchars($incident['pat_name']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($incident['updated_at']))); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="activity-card">
                    <h3>⚙️ Devices Pending Verification</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Device ID</th>
                                <th>Serial</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_devices as $device): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($device['dev_id']); ?></td>
                                <td><?php echo htmlspecialchars($device['dev_serial']); ?></td>
                                <td><span class="device-badge <?php echo htmlspecialchars($device['dev_status']); ?>"><?php echo htmlspecialchars($device['dev_status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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

        // INCIDENT STATUS CHART
        const incidentCtx = document.getElementById('incidentChart').getContext('2d');
        const incidentData = <?php echo json_encode($incident_status ?? []); ?>;
        const incidentLabels = incidentData.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1));
        const incidentCounts = incidentData.map(d => d.count);
        
        new Chart(incidentCtx, {
            type: 'doughnut',
            data: {
                labels: incidentLabels,
                datasets: [{
                    data: incidentCounts,
                    backgroundColor: ['#ff4d6d', '#00e5ff', '#39ff14', '#f59e0b', '#a78bfa'],
                    borderColor: var(--surface),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#e2e8f0',
                            font: { family: "'Space Mono', monospace", size: 12 }
                        }
                    }
                }
            }
        });

        // DEVICE STATUS CHART
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        const deviceData = <?php echo json_encode($device_status ?? []); ?>;
        const deviceLabels = deviceData.map(d => d.dev_status.charAt(0).toUpperCase() + d.dev_status.slice(1));
        const deviceCounts = deviceData.map(d => d.count);
        
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: deviceLabels,
                datasets: [{
                    data: deviceCounts,
                    backgroundColor: ['#00e5ff', '#39ff14', '#ff4d6d', '#f59e0b'],
                    borderColor: var(--surface),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#e2e8f0',
                            font: { family: "'Space Mono', monospace", size: 12 }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>