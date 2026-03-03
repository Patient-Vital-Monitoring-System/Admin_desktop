<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User - Admin</title>
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
                        <a class="nav-link active" href="users.php">User</a>
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
            <div style="padding: 32px 20px; max-width: 1400px; margin: 0 auto; width: 100%;">
                <h1>User</h1>
                <p>View and manage all system users and assigned roles.</p>

                <!-- Top Controls -->
                <div class="controls-section">
                    <div class="controls-row">
                        <button type="button" class="btn btn-primary" onclick="openAddUserModal()">+ Add New User</button>
                        
                        <div class="filter-group">
                            <label for="roleFilter">Filter by Role:</label>
                            <select id="roleFilter" class="filter-select" onchange="filterByRole()">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff / Management</option>
                                <option value="responder">Responder</option>
                                <option value="rescuer">Rescuer</option>
                            </select>
                        </div>

                        <form id="searchForm" class="search-form">
                            <input type="text" id="searchInput" placeholder="Search by name or email..." class="search-input">
                            <button type="submit" class="search-button">Search</button>
                        </form>
                    </div>
                </div>

                <?php
                require_once __DIR__ . '/../../api/auth/config.php';
                require_once __DIR__ . '/../../api/auth/queries.php';

                // Ensure users table exists and sync data from legacy tables
                ensureUsersTable($pdo);
                syncUsersFromLegacyTables($pdo);

                $search = $_GET['search'] ?? '';
                $roleFilter = $_GET['role'] ?? '';
                $users = getAllUsers($pdo, $search, $roleFilter);
                ?>

                <!-- Users Table -->
                <div class="card" style="margin-top: 24px;">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px; color: #888;">No users found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="user-row" data-user-id="<?php echo htmlspecialchars($user['id']); ?>">
                                            <td style="font-weight: 600; color: #00e5ff;">#<?php echo htmlspecialchars($user['id']); ?></td>
                                            <td>
                                                <button class="link-button" onclick="viewUserDetails(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['role']); ?>', '<?php echo htmlspecialchars($user['name'] ?? ''); ?>', '<?php echo htmlspecialchars($user['status'] ?? 'active'); ?>')">
                                                    <?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?>
                                                </button>
                                            </td>
                                            <td>
                                                <span class="role-badge" style="background: <?php echo getRoleColor($user['role']); ?>20; color: <?php echo getRoleColor($user['role']); ?>; padding: 4px 12px; border-radius: 4px; font-weight: 600; text-transform: capitalize;">
                                                    <?php echo htmlspecialchars($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <label class="status-toggle">
                                                    <input type="checkbox" class="status-checkbox" <?php echo ($user['status'] ?? 'active') === 'active' ? 'checked' : ''; ?> onchange="toggleUserStatus(<?php echo $user['id']; ?>, this)">
                                                    <span class="toggle-slider"></span>
                                                    <span class="status-text"></span>
                                                </label>
                                            </td>
                                            <td style="color: #888; font-size: 12px;">
                                                <?php echo getLastLoginDisplay($user['last_login'] ?? null); ?>
                                            </td>
                                            <td style="white-space: nowrap;">
                                                <button type="button" style="background: #6b7280; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-right: 4px; font-size: 12px;" onclick="viewUserDetails(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['role']); ?>', '<?php echo htmlspecialchars($user['name'] ?? ''); ?>', '<?php echo htmlspecialchars($user['status'] ?? 'active'); ?>')">View</button>
                                                <button type="button" style="background: #00e5ff; color: black; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-right: 4px; font-size: 12px; font-weight: bold;" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['role']); ?>', '<?php echo htmlspecialchars($user['name'] ?? ''); ?>')">Update</button>
                                                <button type="button" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['role']); ?>')">Delete</button>
                                            </td>
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

    <!-- User Details Modal -->
    <div id="userDetailsModal" class="modal" style="display: none;">
        <div class="modal-content" style="width: 500px;">
            <div class="modal-header">
                <h2>User Details</h2>
                <button class="modal-close" onclick="closeUserDetailsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detail-section">
                    <h4>Email</h4>
                    <p id="detailEmail"></p>
                </div>
                <div class="detail-section">
                    <h4>Role</h4>
                    <p id="detailRole"></p>
                </div>
                <div class="detail-section">
                    <h4>Account Created</h4>
                    <p id="detailCreated"></p>
                </div>
                <div class="detail-section">
                    <h4>Status</h4>
                    <p id="detailStatus"></p>
                </div>
                <div class="detail-section">
                    <h4>Last Login</h4>
                    <p id="detailLastLogin"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeUserDetailsModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userFormModal" class="modal" style="display: none;">
        <div class="modal-content" style="width: 500px;">
            <div class="modal-header">
                <h2 id="formTitle">Add New User</h2>
                <button class="modal-close" onclick="closeUserFormModal()">&times;</button>
            </div>
            <form id="userForm" onsubmit="handleUserFormSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" id="userId" value="">
                    
                    <div class="form-group">
                        <label for="userName">Full Name *</label>
                        <input type="text" id="userName" name="name" required style="width: 100%; padding: 8px; border: 1px solid #444; border-radius: 4px; background: #222; color: #fff;">
                    </div>

                    <div class="form-group">
                        <label for="userEmail">Email Address *</label>
                        <input type="email" id="userEmail" name="email" required style="width: 100%; padding: 8px; border: 1px solid #444; border-radius: 4px; background: #222; color: #fff;">
                    </div>

                    <div class="form-group">
                        <label for="userRole">Role *</label>
                        <select id="userRole" name="role" required style="width: 100%; padding: 8px; border: 1px solid #444; border-radius: 4px; background: #222; color: #fff;">
                            <option value="">Select a role...</option>
                            <option value="staff">Staff / Management</option>
                            <option value="admin">Admin</option>
                            <option value="responder">Responder</option>
                            <option value="rescuer">Rescuer</option>
                        </select>
                    </div>

                    <div class="form-group" id="passwordGroup">
                        <label for="userPassword">Password *</label>
                        <input type="password" id="userPassword" name="password" required style="width: 100%; padding: 8px; border: 1px solid #444; border-radius: 4px; background: #222; color: #fff;">
                        <small style="color: #888;">Leave blank to keep existing password when editing</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUserFormModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/script.js"></script>
    <script>
        // Check authentication
        if (!localStorage.getItem('vw_token')) {
            window.location.href = '../login.php';
        }

        // Get role color
        function getRoleColor(role) {
            const colors = {
                'admin': '#ff4d6d',
                'staff': '#00e5ff',
                'responder': '#ffd700',
                'rescuer': '#00ff88'
            };
            return colors[role] || '#888';
        }

        // View User Details - Updated with proper role badge styling
        function viewUserDetails(id, email, role, name, status) {
            document.getElementById('detailEmail').textContent = email;
            
            // Create proper role badge
            const roleColors = {
                'admin': { bg: 'rgba(255, 77, 109, 0.15)', color: '#ff4d6d', border: 'rgba(255, 77, 109, 0.3)' },
                'staff': { bg: 'rgba(0, 229, 255, 0.15)', color: '#00e5ff', border: 'rgba(0, 229, 255, 0.3)' },
                'responder': { bg: 'rgba(245, 158, 11, 0.15)', color: '#f59e0b', border: 'rgba(245, 158, 11, 0.3)' },
                'rescuer': { bg: 'rgba(57, 255, 20, 0.15)', color: '#39ff14', border: 'rgba(57, 255, 20, 0.3)' }
            };
            const roleStyle = roleColors[role] || roleColors['staff'];
            const roleBadge = `<span class="role-badge ${role}" style="background: ${roleStyle.bg}; color: ${roleStyle.color}; border: 1px solid ${roleStyle.border}; padding: 6px 14px; border-radius: 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">${role}</span>`;
            
            document.getElementById('detailRole').innerHTML = roleBadge;
            document.getElementById('detailCreated').textContent = 'N/A';
            document.getElementById('detailStatus').innerHTML = status === 'active' ? '<span style="color: #10b981; font-weight: 600;">● Active</span>' : '<span style="color: #ff4d6d; font-weight: 600;">● Inactive</span>';
            document.getElementById('detailLastLogin').textContent = 'Never';
            
            document.getElementById('userDetailsModal').style.display = 'flex';
        }

        function closeUserDetailsModal() {
            document.getElementById('userDetailsModal').style.display = 'none';
        }

        // Add User Modal
        function openAddUserModal() {
            document.getElementById('formTitle').textContent = 'Add New User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('passwordGroup').style.display = 'block';
            document.getElementById('userPassword').required = true;
            document.getElementById('userFormModal').style.display = 'flex';
        }

        function closeUserFormModal() {
            document.getElementById('userFormModal').style.display = 'none';
        }

        // Edit User
        function editUser(id, email, role, name) {
            document.getElementById('formTitle').textContent = 'Edit User';
            document.getElementById('userId').value = id;
            document.getElementById('userName').value = name || '';
            document.getElementById('userEmail').value = email;
            document.getElementById('userRole').value = role;
            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('userPassword').required = false;
            document.getElementById('userFormModal').style.display = 'flex';
        }

        // Handle Form Submit
        async function handleUserFormSubmit(event) {
            event.preventDefault();
            const userId = document.getElementById('userId').value;
            const name = document.getElementById('userName').value;
            const email = document.getElementById('userEmail').value;
            const role = document.getElementById('userRole').value;
            const password = document.getElementById('userPassword').value;

            try {
                if (userId) {
                    // Update existing user
                    const response = await fetch('../../api/auth/update_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: userId,
                            name: name,
                            email: email,
                            role: role,
                            password: password
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('User updated successfully!');
                        closeUserFormModal();
                        window.location.reload();
                    } else {
                        alert('Error: ' + result.error);
                    }
                } else {
                    // Create new user using the new create_user API
                    const response = await fetch('../../api/auth/create_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: name,
                            email: email,
                            password: password,
                            role: role
                        })
                    });
                    
                    const result = await response.json();
                    console.log('Create result:', result);
                    
                    if (result.success) {
                        alert('User created successfully!');
                        closeUserFormModal();
                        window.location.reload();
                    } else {
                        alert('Error: ' + (result.error || 'Failed to create user'));
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }

        // Filter by Role
        function filterByRole() {
            const role = document.getElementById('roleFilter').value;
            let url = 'users.php';
            if (role) {
                url += '?role=' + encodeURIComponent(role);
            }
            window.location.href = url;
        }

        // Search Form
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const search = document.getElementById('searchInput').value;
            let url = 'users.php';
            if (search) {
                url += '?search=' + encodeURIComponent(search);
            }
            window.location.href = url;
        });

        // Toggle User Status
        function toggleUserStatus(id, checkbox) {
            const isActive = checkbox.checked;
            alert((isActive ? 'Activating' : 'Deactivating') + ' user ID: ' + id);
        }

        // Delete User
        async function deleteUser(id, role) {
            console.log('Delete called - ID:', id, 'Role:', role);
            if (confirm('Are you sure you want to permanently delete this user?')) {
                try {
                    const response = await fetch('../../api/auth/delete_user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: id, role: role })
                    });
                    
                    console.log('Response status:', response.status);
                    const result = await response.json();
                    console.log('Delete result:', result);
                    
                    if (result.success) {
                        alert('User deleted successfully!');
                        // Remove the row from the table
                        const row = document.querySelector(`.user-row[data-user-id="${id}"]`);
                        if (row) {
                            row.remove();
                        }
                        // Optionally reload the page
                        // window.location.reload();
                    } else {
                        alert('Error: ' + result.error);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            }
        }

        // Reset Password
        function resetPassword(id) {
            if (confirm('Send password reset link to this user?')) {
                alert('Password reset link sent to user ' + id);
            }
        }

        // Get Last Login (dummy implementation)
        function getLastLogin(userId) {
            const logins = ['Today at 2:30 PM', 'Yesterday at 10:15 AM', '2 days ago', 'Jan 30, 2026', 'Jan 25, 2026'];
            return logins[userId % logins.length] || 'Never';
        }

        // Modal backdrop click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });
    </script>

    <style>
        /* Use vitalwear.css variables */
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

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            color: var(--muted);
            font-weight: 600;
            white-space: nowrap;
        }

        .filter-select {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--surface2);
            color: var(--text);
            cursor: pointer;
            font-size: 13px;
            font-family: 'Syne', sans-serif;
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

        .search-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 229, 255, 0.1);
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

        .btn-reset {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warn);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .btn-reset:hover {
            background: rgba(245, 158, 11, 0.25);
        }

        .status-toggle {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            position: relative;
        }

        .status-checkbox {
            display: none;
        }

        .toggle-slider {
            display: inline-block;
            width: 40px;
            height: 22px;
            background: var(--border);
            border-radius: 11px;
            margin-right: 8px;
            transition: background 0.3s ease;
            position: relative;
        }

        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: left 0.3s ease;
        }

        .status-checkbox:checked + .toggle-slider {
            background: var(--success);
        }

        .status-checkbox:checked + .toggle-slider::before {
            left: 20px;
        }

        .status-text {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            font-family: 'Space Mono', monospace;
        }

        .status-checkbox:checked ~ .status-text::after {
            content: 'Active';
            color: var(--success);
        }

        .status-checkbox:not(:checked) ~ .status-text::after {
            content: 'Inactive';
            color: var(--accent2);
        }

        /* Modal Styles - Updated to match vitalwear.css */
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

        .modal-body .form-group input::placeholder {
            color: var(--muted);
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

        .detail-section p .badge {
            margin-top: 4px;
        }

        /* Role badges matching vitalwear.css */
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

        .role-badge.admin {
            background: rgba(255, 77, 109, 0.15);
            color: #ff4d6d;
            border-color: rgba(255, 77, 109, 0.3);
        }

        .role-badge.staff {
            background: rgba(0, 229, 255, 0.15);
            color: var(--accent);
            border-color: rgba(0, 229, 255, 0.3);
        }

        .role-badge.responder {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warn);
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

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select {
                width: 100%;
            }

            .search-form {
                flex-direction: column;
            }

            .search-input {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
            }
        }
    </style>
</body>
</html>
