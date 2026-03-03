<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Directory - Admin</title>
    <link rel="stylesheet" href="../css/vitalwear.css">
</head>
<body>

    <nav class="navbar-top">
        <h2 class="navbar-brand">Staff Directory</h2>
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
                        $query .= " WHERE resp_name LIKE :search OR resp_email LIKE :search";
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
                        $query .= " WHERE resc_name LIKE :search OR resc_email LIKE :search";
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

                <!-- Top Controls -->
                <div class="controls-section">
                    <div class="controls-row">
                        <button type="button" class="btn btn-primary" onclick="openAddStaffModal()">+ Add Field Staff</button>

                        <form id="searchForm" class="search-form" method="GET">
                            <input
                                type="text"
                                id="searchInput"
                                name="search"
                                placeholder="Search by name or email..."
                                value="<?php echo htmlspecialchars($search); ?>"
                                class="search-input"
                            >
                            <button type="submit" class="search-button">Search</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Staff Table -->
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($staff)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px; color: #888;">No staff found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($staff as $person): ?>
                                    <tr class="staff-row" data-staff-id="<?php echo htmlspecialchars($person['id']); ?>" data-role="<?php echo htmlspecialchars($person['role']); ?>">
                                        <td>
                                            <button
                                                class="link-button"
                                                onclick="viewStaffDetails(
                                                    '<?php echo htmlspecialchars($person['name']); ?>',
                                                    '<?php echo htmlspecialchars($person['email']); ?>',
                                                    '<?php echo htmlspecialchars($person['contact']); ?>',
                                                    '<?php echo htmlspecialchars($person['role']); ?>'
                                                )"
                                            >
                                                <?php echo htmlspecialchars($person['name']); ?>
                                            </button>
                                        </td>
                                        <td><?php echo htmlspecialchars($person['email']); ?></td>
                                        <td><?php echo htmlspecialchars($person['contact']); ?></td>
                                        <td>
                                            <span
                                                class="role-badge <?php echo htmlspecialchars($person['role']); ?>"
                                            >
                                                <?php echo htmlspecialchars($person['role']); ?>
                                            </span>
                                        </td>
                                        <td style="white-space: nowrap;">
                                            <button
                                                type="button"
                                                class="btn-action btn-view"
                                                onclick="viewStaffDetails(
                                                    '<?php echo htmlspecialchars($person['name']); ?>',
                                                    '<?php echo htmlspecialchars($person['email']); ?>',
                                                    '<?php echo htmlspecialchars($person['contact']); ?>',
                                                    '<?php echo htmlspecialchars($person['role']); ?>'
                                                )"
                                            >
                                                View
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-action btn-edit"
                                                onclick="editStaff(
                                                    <?php echo (int)$person['id']; ?>,
                                                    '<?php echo htmlspecialchars($person['name']); ?>',
                                                    '<?php echo htmlspecialchars($person['email']); ?>',
                                                    '<?php echo htmlspecialchars($person['role']); ?>',
                                                    '<?php echo htmlspecialchars($person['contact']); ?>'
                                                )"
                                            >
                                                Update
                                            </button>
                                            <button
                                                type="button"
                                                class="btn-action btn-delete"
                                                onclick="deleteStaff(<?php echo (int)$person['id']; ?>, '<?php echo htmlspecialchars($person['role']); ?>')"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Staff Details Modal -->
    <div id="staffDetailsModal" class="modal" style="display: none;">
        <div class="modal-content" style="width: 500px;">
            <div class="modal-header">
                <h2>Staff Details</h2>
                <button class="modal-close" onclick="closeStaffDetailsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detail-section">
                    <h4>Name</h4>
                    <p id="detailName"></p>
                </div>
                <div class="detail-section">
                    <h4>Email</h4>
                    <p id="detailEmail"></p>
                </div>
                <div class="detail-section">
                    <h4>Contact</h4>
                    <p id="detailContact"></p>
                </div>
                <div class="detail-section">
                    <h4>Role</h4>
                    <p id="detailRole"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeStaffDetailsModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Staff Modal -->
    <div id="staffFormModal" class="modal" style="display: none;">
        <div class="modal-content" style="width: 500px;">
            <div class="modal-header">
                <h2 id="staffFormTitle">Add Field Staff</h2>
                <button class="modal-close" onclick="closeStaffFormModal()">&times;</button>
            </div>
            <form id="staffForm" onsubmit="handleStaffFormSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="staffId" value="">

                    <div class="form-group">
                        <label for="staffName">Full Name *</label>
                        <input type="text" id="staffName" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="staffEmail">Email Address *</label>
                        <input type="email" id="staffEmail" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="staffContact">Contact</label>
                        <input type="text" id="staffContact" name="contact">
                    </div>

                    <div class="form-group">
                        <label for="staffRole">Role *</label>
                        <select id="staffRole" name="role" required>
                            <option value="">Select role...</option>
                            <option value="responder">Responder</option>
                            <option value="rescuer">Rescuer</option>
                        </select>
                    </div>

                    <div class="form-group" id="staffPasswordGroup">
                        <label for="staffPassword">Password *</label>
                        <input type="password" id="staffPassword" name="password" required>
                        <small>Leave blank to keep existing password when editing</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeStaffFormModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <script>
        // Auth check
        if (!localStorage.getItem('vw_token')) {
            window.location.href = '../login.php';
        }

        // View staff details
        function viewStaffDetails(name, email, contact, role) {
            document.getElementById('detailName').textContent = name || 'N/A';
            document.getElementById('detailEmail').textContent = email || 'N/A';
            document.getElementById('detailContact').textContent = contact || 'N/A';

            const roleColors = {
                'responder': { bg: 'rgba(245, 158, 11, 0.15)', color: '#f59e0b', border: 'rgba(245, 158, 11, 0.3)' },
                'rescuer': { bg: 'rgba(57, 255, 20, 0.15)', color: '#39ff14', border: 'rgba(57, 255, 20, 0.3)' }
            };
            const style = roleColors[role] || roleColors['responder'];
            const badge = '<span class="role-badge ' + role + '" style="background: ' + style.bg + '; color: ' + style.color + '; border: 1px solid ' + style.border + ';">' + role + '</span>';
            document.getElementById('detailRole').innerHTML = badge;

            document.getElementById('staffDetailsModal').style.display = 'flex';
        }

        function closeStaffDetailsModal() {
            document.getElementById('staffDetailsModal').style.display = 'none';
        }

        function openAddStaffModal() {
            document.getElementById('staffFormTitle').textContent = 'Add Field Staff';
            document.getElementById('staffForm').reset();
            document.getElementById('staffId').value = '';
            document.getElementById('staffPasswordGroup').style.display = 'block';
            document.getElementById('staffPassword').required = true;
            document.getElementById('staffFormModal').style.display = 'flex';
        }

        function closeStaffFormModal() {
            document.getElementById('staffFormModal').style.display = 'none';
        }

        function editStaff(id, name, email, role, contact) {
            document.getElementById('staffFormTitle').textContent = 'Edit Field Staff';
            document.getElementById('staffId').value = id;
            document.getElementById('staffName').value = name || '';
            document.getElementById('staffEmail').value = email || '';
            document.getElementById('staffRole').value = role || '';
            document.getElementById('staffContact').value = contact || '';
            document.getElementById('staffPasswordGroup').style.display = 'none';
            document.getElementById('staffPassword').required = false;
            document.getElementById('staffFormModal').style.display = 'flex';
        }

        async function handleStaffFormSubmit(event) {
            event.preventDefault();
            const id = document.getElementById('staffId').value;
            const name = document.getElementById('staffName').value;
            const email = document.getElementById('staffEmail').value;
            const role = document.getElementById('staffRole').value;
            const password = document.getElementById('staffPassword').value;

            try {
                if (id) {
                    // Update existing staff (responder/rescuer) via update_user API
                    const response = await fetch('../../api/auth/update_user.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: id,
                            name: name,
                            email: email,
                            role: role,
                            password: password
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert('Staff updated successfully!');
                        closeStaffFormModal();
                        window.location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Failed to update staff'));
                    }
                } else {
                    // Create new staff via create_user API
                    const response = await fetch('../../api/auth/create_user.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            name: name,
                            email: email,
                            password: password,
                            role: role
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert('Staff created successfully!');
                        closeStaffFormModal();
                        window.location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Failed to create staff'));
                    }
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred. Please try again.');
            }
        }

        async function deleteStaff(id, role) {
            if (!confirm('Are you sure you want to permanently delete this staff member?')) {
                return;
            }
            try {
                const response = await fetch('../../api/auth/delete_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, role: role })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Staff deleted successfully!');
                    const row = document.querySelector('.staff-row[data-staff-id="' + id + '"]');
                    if (row) {
                        row.remove();
                    }
                } else {
                    alert('Error: ' + (result.error || 'Failed to delete staff'));
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred. Please try again.');
            }
        }

        // Modal backdrop click to close
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });
    </script>

    <style>
        .controls-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .controls-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--surface2);
            color: var(--text);
            font-size: 13px;
            font-family: 'Syne', sans-serif;
            min-width: 250px;
        }

        .search-input::placeholder {
            color: var(--muted);
        }

        .search-button {
            padding: 10px 20px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            font-family: 'Syne', sans-serif;
            font-size: 13px;
            transition: all 0.2s ease;
        }

        .search-button:hover {
            background: #33eeff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 229, 255, 0.3);
        }

        .link-button {
            background: none;
            border: none;
            color: var(--accent);
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            padding: 0;
            font-family: 'Syne', sans-serif;
        }

        .link-button:hover {
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-family: 'Syne', sans-serif;
        }

        .btn-edit {
            background: rgba(0, 229, 255, 0.15);
            color: var(--accent);
            border: 1px solid rgba(0, 229, 255, 0.3);
        }

        .btn-edit:hover {
            background: rgba(0, 229, 255, 0.25);
        }

        .btn-view {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-view:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-delete {
            background: rgba(255, 77, 109, 0.15);
            color: #ff4d6d;
            border: 1px solid rgba(255, 77, 109, 0.3);
        }

        .btn-delete:hover {
            background: rgba(255, 77, 109, 0.25);
        }

        /* Modal styles (matching users.php) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            max-height: 90vh;
            overflow-y: auto;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface2);
            border-radius: 12px 12px 0 0;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--accent);
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--muted);
            font-size: 28px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            color: var(--text);
            background: rgba(255, 255, 255, 0.1);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: var(--surface2);
            border-radius: 0 0 12px 12px;
        }

        .modal-body .form-group {
            margin-bottom: 20px;
        }

        .modal-body .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-weight: 600;
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-family: 'Space Mono', monospace;
        }

        .modal-body .form-group input,
        .modal-body .form-group select {
            width: 100%;
            padding: 12px 16px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: 'Syne', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s;
            outline: none;
        }

        .modal-body .form-group input:focus,
        .modal-body .form-group select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 229, 255, 0.1);
        }

        .modal-body .form-group small {
            display: block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 12px;
            font-family: 'Space Mono', monospace;
        }

        .detail-section {
            margin-bottom: 20px;
        }

        .detail-section h4 {
            color: var(--muted);
            margin: 0 0 8px 0;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            font-family: 'Space Mono', monospace;
        }

        .detail-section p {
            color: var(--text);
            margin: 0;
            font-size: 15px;
            font-weight: 500;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-family: 'Space Mono', monospace;
            text-transform: uppercase;
            border: 1px solid;
        }

        .role-badge.responder {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.3);
        }

        .role-badge.rescuer {
            background: rgba(57, 255, 20, 0.15);
            color: #39ff14;
            border-color: rgba(57, 255, 20, 0.3);
        }

        @media (max-width: 768px) {
            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form {
                flex-direction: column;
            }

            .search-input {
                width: 100%;
            }

            .btn-action {
                width: 100%;
                margin-bottom: 4px;
            }
        }
    </style>
</body>
</html>