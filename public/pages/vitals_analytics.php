<?php
require_once '../../api/auth/config.php';

// Get average BP this month
$avgBpQuery = $pdo->prepare("
    SELECT 
        AVG(bp_systolic) as avg_systolic,
        AVG(bp_diastolic) as avg_diastolic,
        COUNT(*) as total_readings
    FROM vitalstat 
    WHERE MONTH(recorded_at) = MONTH(CURDATE()) 
    AND YEAR(recorded_at) = YEAR(CURDATE())
");
$avgBpQuery->execute();
$avgBp = $avgBpQuery->fetch(PDO::FETCH_ASSOC);

// Get high BP incidents this month
$highBpQuery = $pdo->prepare("
    SELECT COUNT(*) as high_bp_count
    FROM vitalstat
    WHERE (bp_systolic > 140 OR bp_diastolic > 90)
    AND MONTH(recorded_at) = MONTH(CURDATE())
    AND YEAR(recorded_at) = YEAR(CURDATE())
");
$highBpQuery->execute();
$highBpData = $highBpQuery->fetch(PDO::FETCH_ASSOC);
$highBpCount = $highBpData['high_bp_count'];

// Get peak hour of incidents
$peakHourQuery = $pdo->prepare("
    SELECT 
        HOUR(start_time) as hour,
        COUNT(*) as incident_count
    FROM incident
    WHERE MONTH(start_time) = MONTH(CURDATE())
    AND YEAR(start_time) = YEAR(CURDATE())
    GROUP BY HOUR(start_time)
    ORDER BY incident_count DESC
    LIMIT 1
");
$peakHourQuery->execute();
$peakHourData = $peakHourQuery->fetch(PDO::FETCH_ASSOC);
$peakHour = $peakHourData ? $peakHourData['hour'] : 0;

// Get monitoring frequency (avg readings per day)
$monitoringFreqQuery = $pdo->prepare("
    SELECT 
        COUNT(*) as total_readings,
        COUNT(DISTINCT DATE(recorded_at)) as days_monitored,
        ROUND(COUNT(*) / COUNT(DISTINCT DATE(recorded_at)), 1) as readings_per_day
    FROM vitalstat
    WHERE MONTH(recorded_at) = MONTH(CURDATE())
    AND YEAR(recorded_at) = YEAR(CURDATE())
");
$monitoringFreqQuery->execute();
$monitoringFreq = $monitoringFreqQuery->fetch(PDO::FETCH_ASSOC);

// Get BP trend by week
$bpTrendQuery = $pdo->prepare("
    SELECT 
        WEEK(recorded_at) as week_num,
        AVG(bp_systolic) as avg_systolic,
        AVG(bp_diastolic) as avg_diastolic
    FROM vitalstat
    WHERE YEAR(recorded_at) = YEAR(CURDATE())
    GROUP BY WEEK(recorded_at)
    ORDER BY WEEK(recorded_at) DESC
    LIMIT 5
");
$bpTrendQuery->execute();
$bpTrends = array_reverse($bpTrendQuery->fetchAll(PDO::FETCH_ASSOC));

// Get incident distribution by hour
$incidentHourlyQuery = $pdo->prepare("
    SELECT 
        HOUR(start_time) as hour,
        COUNT(*) as incident_count
    FROM incident
    WHERE YEAR(start_time) = YEAR(CURDATE())
    AND MONTH(start_time) = MONTH(CURDATE())
    GROUP BY HOUR(start_time)
    ORDER BY hour ASC
");
$incidentHourlyQuery->execute();
$incidentHourly = $incidentHourlyQuery->fetchAll(PDO::FETCH_ASSOC);

// Get vital signs distribution
$vitalDistQuery = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN bp_systolic <= 120 AND bp_diastolic <= 80 THEN 1 END) as normal,
        COUNT(CASE WHEN (bp_systolic > 120 AND bp_systolic <= 140) OR (bp_diastolic > 80 AND bp_diastolic <= 90) THEN 1 END) as elevated,
        COUNT(CASE WHEN bp_systolic > 140 OR bp_diastolic > 90 THEN 1 END) as high
    FROM vitalstat
    WHERE MONTH(recorded_at) = MONTH(CURDATE())
    AND YEAR(recorded_at) = YEAR(CURDATE())
");
$vitalDistQuery->execute();
$vitalDist = $vitalDistQuery->fetch(PDO::FETCH_ASSOC);

// Get detailed vital statistics table
$vitalTableQuery = $pdo->prepare("
    SELECT 
        p.pat_name,
        v.bp_systolic,
        v.bp_diastolic,
        v.heart_rate,
        v.temperature,
        v.oxygen_level,
        v.recorded_at,
        CASE 
            WHEN v.bp_systolic <= 120 AND v.bp_diastolic <= 80 THEN 'Normal'
            WHEN (v.bp_systolic > 120 AND v.bp_systolic <= 140) OR (v.bp_diastolic > 80 AND v.bp_diastolic <= 90) THEN 'Elevated'
            WHEN v.bp_systolic > 140 OR v.bp_diastolic > 90 THEN 'High'
        END as bp_status
    FROM vitalstat v
    JOIN patient p ON v.pat_id = p.pat_id
    WHERE MONTH(v.recorded_at) = MONTH(CURDATE())
    AND YEAR(v.recorded_at) = YEAR(CURDATE())
    ORDER BY v.recorded_at DESC
    LIMIT 50
");
$vitalTableQuery->execute();
$vitalTableData = $vitalTableQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vital Statistics Analytics</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">Vital Statistics Analytics</h2>
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
                <a class="nav-link active" href="vitals_analytics.php">Vital Statistics</a>
                <a class="nav-link" href="incidents.php">Incident Monitoring</a>
                <a class="nav-link" href="device_incidents.php">Device Tracking</a>
                <a class="nav-link" href="audit_log.php">Activity Log</a>
                <a class="nav-link" href="alerts.php">Alert Records</a>
                <a class="nav-link" href="user_status.php">User Status</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div style="padding: 32px 20px; max-width: 1400px; margin: 0 auto; width: 100%;">
                
                <!-- Top Row: Key Metrics -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
                    
                    <!-- Average BP Card -->
                    <div class="metric-card">
                        <div class="metric-header">
                            <h4 style="margin: 0; font-size: 16px; color: #a0aec0;">Average BP</h4>
                            <span style="font-size: 28px; color: #00e5ff;">📊</span>
                        </div>
                        <div style="margin-top: 16px;">
                            <div style="font-size: 32px; font-weight: bold; color: #00e5ff;">
                                <?php echo round($avgBp['avg_systolic'] ?? 0) . "/" . round($avgBp['avg_diastolic'] ?? 0) . " mmHg"; ?>
                            </div>
                            <p style="margin: 8px 0 0 0; color: #718096; font-size: 13px;">
                                Based on <?php echo intval($avgBp['total_readings'] ?? 0); ?> readings this month
                            </p>
                        </div>
                    </div>

                    <!-- High BP Incidents Card -->
                    <div class="metric-card">
                        <div class="metric-header">
                            <h4 style="margin: 0; font-size: 16px; color: #a0aec0;">High BP Incidents</h4>
                            <span style="font-size: 28px; color: #ff4d6d;">⚠️</span>
                        </div>
                        <div style="margin-top: 16px;">
                            <div style="font-size: 32px; font-weight: bold; color: #ff4d6d;">
                                <?php echo $highBpCount; ?>
                            </div>
                            <p style="margin: 8px 0 0 0; color: #718096; font-size: 13px;">
                                Readings above 140/90 this month
                            </p>
                        </div>
                    </div>

                    <!-- Peak Hour Card -->
                    <div class="metric-card">
                        <div class="metric-header">
                            <h4 style="margin: 0; font-size: 16px; color: #a0aec0;">Peak Incident Hour</h4>
                            <span style="font-size: 28px; color: #39ff14;">🕐</span>
                        </div>
                        <div style="margin-top: 16px;">
                            <div style="font-size: 32px; font-weight: bold; color: #39ff14;">
                                <?php echo str_pad($peakHour, 2, '0', STR_PAD_LEFT) . ":00"; ?>
                            </div>
                            <p style="margin: 8px 0 0 0; color: #718096; font-size: 13px;">
                                Most incidents occur around this time
                            </p>
                        </div>
                    </div>

                    <!-- Monitoring Frequency Card -->
                    <div class="metric-card">
                        <div class="metric-header">
                            <h4 style="margin: 0; font-size: 16px; color: #a0aec0;">Monitoring Frequency</h4>
                            <span style="font-size: 28px; color: #f59e0b;">📈</span>
                        </div>
                        <div style="margin-top: 16px;">
                            <div style="font-size: 32px; font-weight: bold; color: #f59e0b;">
                                <?php echo round($monitoringFreq['readings_per_day'] ?? 0, 1); ?>
                            </div>
                            <p style="margin: 8px 0 0 0; color: #718096; font-size: 13px;">
                                Average readings per day
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Middle Section: Charts -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-bottom: 40px;">
                    
                    <!-- BP Trend Chart -->
                    <div class="chart-container" style="background: #0f1419; padding: 24px; border-radius: 12px; border: 1px solid #1a2332;">
                        <h3 style="margin: 0 0 24px 0; color: #e2e8f0; font-size: 16px;">📊 BP Trend (Last 5 Weeks)</h3>
                        <canvas id="bpTrendChart" style="max-height: 300px;"></canvas>
                    </div>

                    <!-- Vital Status Distribution Chart -->
                    <div class="chart-container" style="background: #0f1419; padding: 24px; border-radius: 12px; border: 1px solid #1a2332;">
                        <h3 style="margin: 0 0 24px 0; color: #e2e8f0; font-size: 16px;">🏥 Vital Signs Distribution</h3>
                        <canvas id="vitalDistChart" style="max-height: 300px;"></canvas>
                    </div>

                </div>

                <!-- Incident Distribution by Hour -->
                <div style="background: #0f1419; padding: 24px; border-radius: 12px; border: 1px solid #1a2332; margin-bottom: 40px;">
                    <h3 style="margin: 0 0 24px 0; color: #e2e8f0; font-size: 16px;">⏰ Incident Distribution by Hour</h3>
                    <canvas id="incidentHourlyChart" style="max-height: 350px;"></canvas>
                </div>

                <!-- Detailed Vital Statistics Table -->
                <div style="background: #0f1419; padding: 24px; border-radius: 12px; border: 1px solid #1a2332; overflow-x: auto;">
                    <h3 style="margin: 0 0 24px 0; color: #e2e8f0; font-size: 16px;">📋 Recent Vital Readings (This Month)</h3>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 2px solid #1a2332;">
                                <th style="padding: 12px; text-align: left; color: #a0aec0; font-weight: 600; white-space: nowrap;">Patient</th>
                                <th style="padding: 12px; text-align: center; color: #a0aec0; font-weight: 600; white-space: nowrap;">Systolic</th>
                                <th style="padding: 12px; text-align: center; color: #a0aec0; font-weight: 600; white-space: nowrap;">Diastolic</th>
                                <th style="padding: 12px; text-align: center; color: #a0aec0; font-weight: 600; white-space: nowrap;">Heart Rate</th>
                                <th style="padding: 12px; text-align: center; color: #a0aec0; font-weight: 600; white-space: nowrap;">Temp (°F)</th>
                                <th style="padding: 12px; text-align: center; color: #a0aec0; font-weight: 600; white-space: nowrap;">O₂ Level (%)</th>
                                <th style="padding: 12px; text-align: center; color: #a0aec0; font-weight: 600; white-space: nowrap;">BP Status</th>
                                <th style="padding: 12px; text-align: center; color: #a0aec0; font-weight: 600; white-space: nowrap;">Recorded</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vitalTableData as $vital): ?>
                            <tr style="border-bottom: 1px solid #1a2332; hover-background: #1a2332;">
                                <td style="padding: 12px; color: #e2e8f0; white-space: nowrap;"><?php echo htmlspecialchars($vital['pat_name']); ?></td>
                                <td style="padding: 12px; text-align: center; color: #e2e8f0;"><?php echo intval($vital['bp_systolic']); ?></td>
                                <td style="padding: 12px; text-align: center; color: #e2e8f0;"><?php echo intval($vital['bp_diastolic']); ?></td>
                                <td style="padding: 12px; text-align: center; color: #e2e8f0;"><?php echo intval($vital['heart_rate']); ?> bpm</td>
                                <td style="padding: 12px; text-align: center; color: #e2e8f0;"><?php echo number_format($vital['temperature'], 1); ?></td>
                                <td style="padding: 12px; text-align: center; color: #e2e8f0;"><?php echo intval($vital['oxygen_level']); ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php 
                                    $statusColor = '#39ff14';
                                    if ($vital['bp_status'] === 'Elevated') $statusColor = '#f59e0b';
                                    if ($vital['bp_status'] === 'High') $statusColor = '#ff4d6d';
                                    ?>
                                    <span style="padding: 6px 12px; border-radius: 6px; background: rgba(<?php echo($statusColor === '#ff4d6d' ? '255,77,109' : ($statusColor === '#f59e0b' ? '245,158,11' : '57,255,20')); ?>, 0.15); color: <?php echo $statusColor; ?>; font-weight: 600;">
                                        <?php echo htmlspecialchars($vital['bp_status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center; color: #718096; font-size: 12px; white-space: nowrap;">
                                    <?php echo date('M d, H:i', strtotime($vital['recorded_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <script src="../js/script.js"></script>
    <script>
        if (!localStorage.getItem('vw_token')) {
            window.location.href = '../login.php';
        }

        // BP Trend Chart
        const bpTrendCtx = document.getElementById('bpTrendChart').getContext('2d');
        new Chart(bpTrendCtx, {
            type: 'line',
            data: {
                labels: [<?php foreach ($bpTrends as $trend) echo "'Week " . $trend['week_num'] . "', "; ?>],
                datasets: [
                    {
                        label: 'Average Systolic',
                        data: [<?php foreach ($bpTrends as $trend) echo round($trend['avg_systolic']) . ", "; ?>],
                        borderColor: '#ff4d6d',
                        backgroundColor: 'rgba(255, 77, 109, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Average Diastolic',
                        data: [<?php foreach ($bpTrends as $trend) echo round($trend['avg_diastolic']) . ", "; ?>],
                        borderColor: '#00e5ff',
                        backgroundColor: 'rgba(0, 229, 255, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
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
                        grid: { color: 'rgba(160, 174, 192, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#a0aec0' },
                        grid: { color: 'rgba(160, 174, 192, 0.1)' }
                    }
                }
            }
        });

        // Vital Status Distribution
        const vitalDistCtx = document.getElementById('vitalDistChart').getContext('2d');
        new Chart(vitalDistCtx, {
            type: 'doughnut',
            data: {
                labels: ['Normal', 'Elevated', 'High'],
                datasets: [{
                    data: [<?php echo $vitalDist['normal'] . ", " . $vitalDist['elevated'] . ", " . $vitalDist['high']; ?>],
                    backgroundColor: ['#39ff14', '#f59e0b', '#ff4d6d'],
                    borderColor: '#0a0e1a',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#a0aec0' }
                    }
                }
            }
        });

        // Incident Distribution by Hour
        const incidentCtx = document.getElementById('incidentHourlyChart').getContext('2d');
        new Chart(incidentCtx, {
            type: 'bar',
            data: {
                labels: [<?php foreach ($incidentHourly as $ih) echo "'" . str_pad($ih['hour'], 2, '0', STR_PAD_LEFT) . ":00', "; ?>],
                datasets: [{
                    label: 'Incident Count',
                    data: [<?php foreach ($incidentHourly as $ih) echo $ih['incident_count'] . ", "; ?>],
                    backgroundColor: '#00e5ff',
                    borderColor: '#00c9e8',
                    borderWidth: 1
                }]
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
                        grid: { color: 'rgba(160, 174, 192, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#a0aec0' },
                        grid: { color: 'rgba(160, 174, 192, 0.1)' }
                    }
                }
            }
        });
    </script>
</body>
</html>
