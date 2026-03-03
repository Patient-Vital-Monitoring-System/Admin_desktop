<?php
require_once '../../api/auth/config.php';

// Active responders (last 7 days)
$activeResQuery = $pdo->prepare("SELECT r.resp_id, r.resp_name, MAX(i.start_time) as last_incident
    FROM responder r
    LEFT JOIN incident i ON r.resp_id = i.resp_id
    GROUP BY r.resp_id, r.resp_name");
$activeResQuery->execute();
$responders = $activeResQuery->fetchAll(PDO::FETCH_ASSOC);

// Active rescuers (last 7 days)
$activeRescQuery = $pdo->prepare("SELECT resc.resc_id, resc.resc_name, MAX(i.end_time) as last_transfer
    FROM rescuer resc
    LEFT JOIN incident i ON resc.resc_id = i.resc_id
    GROUP BY resc.resc_id, resc.resc_name");
$activeRescQuery->execute();
$rescuers = $activeRescQuery->fetchAll(PDO::FETCH_ASSOC);

// Determine counts
$totalResponders = count($responders);
$activeResponders = 0;
foreach ($responders as $r) {
    if ($r['last_incident'] && strtotime($r['last_incident']) >= strtotime('-7 days')) {
        $activeResponders++;
    }
}
$inactiveResponders = $totalResponders - $activeResponders;

$totalRescuers = count($rescuers);
$activeRescuers = 0;
foreach ($rescuers as $r) {
    if ($r['last_transfer'] && strtotime($r['last_transfer']) >= strtotime('-7 days')) {
        $activeRescuers++;
    }
}
$inactiveRescuers = $totalRescuers - $activeRescuers;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Status Monitoring</title>
    <link rel="stylesheet" href="../css/vitalwear.css" />
</head>
<body>
    <nav class="navbar-top">
        <h2 class="navbar-brand">User Status Monitoring</h2>
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
                <a class="nav-link" href="incidents.php">Incident Monitoring</a>
                <a class="nav-link" href="device_incidents.php">Device Tracking</a>
                <a class="nav-link" href="audit_log.php">Activity Log</a>

                <a class="nav-link active" href="user_status.php">User Status</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div style="padding:32px 20px; max-width:1200px; margin:0 auto; width:100%;">
                <h1>User Status Monitoring</h1>
                <p class="muted">Active = any involvement within the last 7 days. Login history not tracked.</p>

                <div class="metric-grid" style="margin-top:24px;">
                    <div class="metric-card">
                        <div class="metric-header"><h4>Active Responders</h4></div>
                        <div class="metric-value"><?php echo $activeResponders; ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-header"><h4>Inactive Responders</h4></div>
                        <div class="metric-value"><?php echo $inactiveResponders; ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-header"><h4>Active Rescuers</h4></div>
                        <div class="metric-value"><?php echo $activeRescuers; ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-header"><h4>Inactive Rescuers</h4></div>
                        <div class="metric-value"><?php echo $inactiveRescuers; ?></div>
                    </div>
                </div>

                <section style="margin-top:40px;">
                    <h3>Responder Activity Details</h3>
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr><th>Name</th><th>Last Incident</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responders as $r):
                                $last = $r['last_incident'];
                                $isActive = $last && strtotime($last) >= strtotime('-7 days');
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['resp_name']); ?></td>
                                <td><?php echo $last ? date('M d, H:i', strtotime($last)) : '<span style="color:var(--muted)">None</span>'; ?></td>
                                <td><span class="badge" style="background:<?php echo $isActive ? 'var(--accent)' : 'var(--accent3)'; ?>; color:#0a0e1a"><?php echo $isActive ? 'Active' : 'Inactive'; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <section style="margin-top:40px;">
                    <h3>Rescuer Activity Details</h3>
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr><th>Name</th><th>Last Transfer</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rescuers as $r):
                                $last = $r['last_transfer'];
                                $isActive = $last && strtotime($last) >= strtotime('-7 days');
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['resc_name']); ?></td>
                                <td><?php echo $last ? date('M d, H:i', strtotime($last)) : '<span style="color:var(--muted)">None</span>'; ?></td>
                                <td><span class="badge" style="background:<?php echo $isActive ? 'var(--accent)' : 'var(--accent3)'; ?>; color:#0a0e1a"><?php echo $isActive ? 'Active' : 'Inactive'; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

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