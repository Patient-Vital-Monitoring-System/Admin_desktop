<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">User Management</h2>
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
                        <a class="nav-link active" href="patients.php">Staff Directory</a>
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
            <div style="padding: 32px 20px; max-width: 1200px; margin: 0 auto; width: 100%;">
            <h1>Responders & Rescuers</h1>
            <p>View and manage all field staff (Responders and Rescuers) below.</p>

            <?php
            require_once __DIR__ . '/../../api/auth/config.php';

            $search = $_GET['search'] ?? '';
$staff = [];
try {
    $responders = [];
    $rescuers = [];
    
    // Get responders
    $query = "SELECT resp_id as id, resp_name as name, resp_email as email, resp_contact as contact, 'responder' as role FROM responder";
    if ($search) {
        $query .= " WHERE resp_name LIKE :search";
    }
    $stmt = $pdo->prepare($query);
    if ($search) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    $responders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get rescuers
    $query = "SELECT resc_id as id, resc_name as name, resc_email as email, resc_contact as contact, 'rescuer' as role FROM rescuer";
    if ($search) {
        $query .= " WHERE resc_name LIKE :search";
    }
    $stmt = $pdo->prepare($query);
    if ($search) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    $rescuers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $staff = array_merge($responders, $rescuers);
} catch (Exception $e) {
    error_log('Staff query failed: ' . $e->getMessage());
}
?>
                <form action="" method="GET">
                    <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($staff)): ?>
                                <tr>
                                    <td colspan="4">No staff found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($staff as $person): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($person['name']); ?></td>
                                        <td><?php echo htmlspecialchars($person['email']); ?></td>
                                        <td><?php echo htmlspecialchars($person['contact']); ?></td>
                                        <td><span style="padding: 4px 8px; border-radius: 4px; background: <?php echo $person['role'] === 'responder' ? '#00e5ff' : '#ff4d6d'; ?>20; color: <?php echo $person['role'] === 'responder' ? '#00e5ff' : '#ff4d6d'; ?>; font-weight: 600; text-transform: capitalize;"><?php echo htmlspecialchars($person['role']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
    </script>
</body>
</html>