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
                <a class="nav-link" href="vitals.php">Vitals Reports</a>
                <a class="nav-link" href="vitals_analytics.php">Vital Statistics</a>
                <a class="nav-link" href="incidents.php">Incident Monitoring</a>
                <a class="nav-link" href="device_incidents.php">Device Tracking</a>
                <a class="nav-link" href="audit_log.php">Activity Log</a>
                <a class="nav-link active" href="alerts.php">Alert Records</a>                <a class="nav-link" href="user_status.php">User Status</a>                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div style="padding: 32px 20px; max-width: 1200px; margin: 0 auto; width: 100%;">
            <h1>Alert Records</h1>
            <p>Monitor and manage critical alerts for patients below.</p>
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