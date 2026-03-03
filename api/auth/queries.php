<?php
// reusable SQL query helpers for auth-related operations

/**
 * Fetch a user by email and role.
 *
 * @param PDO $pdo
 * @param string $email
 * @param string $role
 * @return array|null
 */
function getUserByEmailRole(PDO $pdo, string $email, string $role): ?array {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
    $stmt->execute([$email, $role]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Fetch a user by ID.
 *
 * @param PDO $pdo
 * @param int $id
 * @return array|null
 */
function getUserById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Fetch all users from original database tables (management, admin, responder, rescuer).
 *
 * @param PDO $pdo
 * @param string $search
 * @param string $roleFilter
 * @return array
 */
function getAllUsers(PDO $pdo, string $search = '', string $roleFilter = ''): array {
    $users = [];
    $searchTerm = "%$search%";
    
    try {
        // Fetch from management table (role: staff)
        if (empty($roleFilter) || $roleFilter === 'staff') {
            $sql = "SELECT mgmt_id as id, mgmt_email as email, mgmt_password as password, 'staff' as role, mgmt_name as name, 'active' as status 
                    FROM management WHERE 1=1";
            $params = [];
            if (!empty($search)) {
                $sql .= " AND (mgmt_email LIKE ? OR mgmt_name LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $users = array_merge($users, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        // Fetch from admin table (role: admin)
        if (empty($roleFilter) || $roleFilter === 'admin') {
            $sql = "SELECT admin_id as id, admin_email as email, admin_password as password, 'admin' as role, admin_name as name, 'active' as status 
                    FROM admin WHERE 1=1";
            $params = [];
            if (!empty($search)) {
                $sql .= " AND (admin_email LIKE ? OR admin_name LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $users = array_merge($users, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        // Fetch from responder table (role: responder)
        if (empty($roleFilter) || $roleFilter === 'responder') {
            $sql = "SELECT resp_id as id, resp_email as email, resp_password as password, 'responder' as role, resp_name as name, 'active' as status 
                    FROM responder WHERE 1=1";
            $params = [];
            if (!empty($search)) {
                $sql .= " AND (resp_email LIKE ? OR resp_name LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $users = array_merge($users, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
        // Fetch from rescuer table (role: rescuer)
        if (empty($roleFilter) || $roleFilter === 'rescuer') {
            $sql = "SELECT resc_id as id, resc_email as email, resc_password as password, 'rescuer' as role, resc_name as name, 'active' as status 
                    FROM rescuer WHERE 1=1";
            $params = [];
            if (!empty($search)) {
                $sql .= " AND (resc_email LIKE ? OR resc_name LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $users = array_merge($users, $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    } catch (Exception $e) {
        error_log("Error fetching users: " . $e->getMessage());
    }
    
    return $users;
}

/**
 * Create a new user record.
 *
 * @param PDO $pdo
 * @param string $email
 * @param string $passwordHash
 * @param string $role
 * @return int Inserted user ID
 */
function createUser(PDO $pdo, string $email, string $passwordHash, string $role): int {
    $stmt = $pdo->prepare('INSERT INTO users (email, password, role) VALUES (?, ?, ?)');
    $stmt->execute([$email, $passwordHash, $role]);
    return (int)$pdo->lastInsertId();
}

/**
 * Create a new user in the original database tables based on role.
 *
 * @param PDO $pdo
 * @param string $email
 * @param string $passwordHash
 * @param string $role
 * @param string $name
 * @return bool
 */
function createUserByRole(PDO $pdo, string $email, string $passwordHash, string $role, string $name = ''): bool {
    try {
        $result = false;
        
        switch ($role) {
            case 'staff':
                $stmt = $pdo->prepare("INSERT INTO management (mgmt_email, mgmt_password, mgmt_name) VALUES (?, ?, ?)");
                $stmt->execute([$email, $passwordHash, $name]);
                $result = $stmt->rowCount() > 0;
                break;
                
            case 'admin':
                $stmt = $pdo->prepare("INSERT INTO admin (admin_email, admin_password, admin_name) VALUES (?, ?, ?)");
                $stmt->execute([$email, $passwordHash, $name]);
                $result = $stmt->rowCount() > 0;
                break;
                
            case 'responder':
                $stmt = $pdo->prepare("INSERT INTO responder (resp_email, resp_password, resp_name) VALUES (?, ?, ?)");
                $stmt->execute([$email, $passwordHash, $name]);
                $result = $stmt->rowCount() > 0;
                break;
                
            case 'rescuer':
                $stmt = $pdo->prepare("INSERT INTO rescuer (resc_email, resc_password, resc_name) VALUES (?, ?, ?)");
                $stmt->execute([$email, $passwordHash, $name]);
                $result = $stmt->rowCount() > 0;
                break;
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Create user error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update an existing user record in the original database tables.
 *
 * @param PDO $pdo
 * @param int $id
 * @param string $email
 * @param string $role
 * @param string|null $passwordHash
 * @param string|null $name
 * @return bool
 */
function updateUser(PDO $pdo, int $id, string $email, string $role, ?string $passwordHash = null, ?string $name = null): bool {
    try {
        $result = false;
        
        switch ($role) {
            case 'staff':
                if ($name !== null) {
                    $sql = $passwordHash 
                        ? "UPDATE management SET mgmt_email = ?, mgmt_password = ?, mgmt_name = ? WHERE mgmt_id = ?"
                        : "UPDATE management SET mgmt_email = ?, mgmt_name = ? WHERE mgmt_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($passwordHash ? [$email, $passwordHash, $name, $id] : [$email, $name, $id]);
                } else {
                    $sql = $passwordHash 
                        ? "UPDATE management SET mgmt_email = ?, mgmt_password = ? WHERE mgmt_id = ?"
                        : "UPDATE management SET mgmt_email = ? WHERE mgmt_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($passwordHash ? [$email, $passwordHash, $id] : [$email, $id]);
                }
                $result = $stmt->rowCount() > 0;
                break;
                
            case 'admin':
                if ($name !== null) {
                    $sql = $passwordHash 
                        ? "UPDATE admin SET admin_email = ?, admin_password = ?, admin_name = ? WHERE admin_id = ?"
                        : "UPDATE admin SET admin_email = ?, admin_name = ? WHERE admin_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($passwordHash ? [$email, $passwordHash, $name, $id] : [$email, $name, $id]);
                } else {
                    $sql = $passwordHash 
                        ? "UPDATE admin SET admin_email = ?, admin_password = ? WHERE admin_id = ?"
                        : "UPDATE admin SET admin_email = ? WHERE admin_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($passwordHash ? [$email, $passwordHash, $id] : [$email, $id]);
                }
                $result = $stmt->rowCount() > 0;
                break;
                
            case 'responder':
                if ($name !== null) {
                    $sql = $passwordHash 
                        ? "UPDATE responder SET resp_email = ?, resp_password = ?, resp_name = ? WHERE resp_id = ?"
                        : "UPDATE responder SET resp_email = ?, resp_name = ? WHERE resp_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($passwordHash ? [$email, $passwordHash, $name, $id] : [$email, $name, $id]);
                } else {
                    $sql = $passwordHash 
                        ? "UPDATE responder SET resp_email = ?, resp_password = ? WHERE resp_id = ?"
                        : "UPDATE responder SET resp_email = ? WHERE resp_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($passwordHash ? [$email, $passwordHash, $id] : [$email, $id]);
                }
                $result = $stmt->rowCount() > 0;
                break;
                
            case 'rescuer':
                if ($name !== null) {
                    $sql = $passwordHash 
                        ? "UPDATE rescuer SET resc_email = ?, resc_password = ?, resc_name = ? WHERE resc_id = ?"
                        : "UPDATE rescuer SET resc_email = ?, resc_name = ? WHERE resc_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($passwordHash ? [$email, $passwordHash, $name, $id] : [$email, $name, $id]);
                } else {
                    $sql = $passwordHash 
                        ? "UPDATE rescuer SET resc_email = ?, resc_password = ? WHERE resc_id = ?"
                        : "UPDATE rescuer SET resc_email = ? WHERE resc_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($passwordHash ? [$email, $passwordHash, $id] : [$email, $id]);
                }
                $result = $stmt->rowCount() > 0;
                break;
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Update error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a user record from the original database tables.
 *
 * @param PDO $pdo
 * @param int $id
 * @param string $role
 * @return bool
 */
function deleteUser(PDO $pdo, int $id, string $role = ''): bool {
    try {
        $result = false;
        
        // If role is provided, delete from specific table
        if (!empty($role)) {
            switch ($role) {
                case 'staff':
                    $stmt = $pdo->prepare("DELETE FROM management WHERE mgmt_id = ?");
                    $stmt->execute([$id]);
                    $result = $stmt->rowCount() > 0;
                    break;
                    
                case 'admin':
                    $stmt = $pdo->prepare("DELETE FROM admin WHERE admin_id = ?");
                    $stmt->execute([$id]);
                    $result = $stmt->rowCount() > 0;
                    break;
                    
                case 'responder':
                    $stmt = $pdo->prepare("DELETE FROM responder WHERE resp_id = ?");
                    $stmt->execute([$id]);
                    $result = $stmt->rowCount() > 0;
                    break;
                    
                case 'rescuer':
                    $stmt = $pdo->prepare("DELETE FROM rescuer WHERE resc_id = ?");
                    $stmt->execute([$id]);
                    $result = $stmt->rowCount() > 0;
                    break;
            }
        } else {
            // Try each table
            $tables = [
                ['table' => 'management', 'id' => 'mgmt_id'],
                ['table' => 'admin', 'id' => 'admin_id'],
                ['table' => 'responder', 'id' => 'resp_id'],
                ['table' => 'rescuer', 'id' => 'resc_id']
            ];
            
            foreach ($tables as $t) {
                $stmt = $pdo->prepare("DELETE FROM {$t['table']} WHERE {$t['id']} = ?");
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    $result = true;
                    break;
                }
            }
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Delete error: " . $e->getMessage());
        return false;
    }
}

/**
 * Ensure the `users` table exists. Use when initializing the database.
 *
 * @param PDO $pdo
 * @return void
 */
function ensureUsersTable(PDO $pdo): void {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL,
        name VARCHAR(255) DEFAULT '',
        status VARCHAR(50) DEFAULT 'active',
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARSET=utf8;");
}

/**
 * Sync users from legacy tables (management, admin, responders, rescuers).
 *
 * @param PDO $pdo
 * @return void
 */
function syncUsersFromLegacyTables(PDO $pdo): void {
    try {
        // Sync from management table
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO users (email, password, role, name, status) 
            SELECT mgmt_email, mgmt_password, 'staff', mgmt_name, 'active' 
            FROM management
        ");
        $stmt->execute();

        // Sync from admin table
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO users (email, password, role, name, status) 
            SELECT admin_email, admin_password, 'admin', admin_name, 'active' 
            FROM admin
        ");
        $stmt->execute();

        // Sync from responder table
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO users (email, password, role, name, status) 
            SELECT resp_email, resp_password, 'responder', resp_name, 'active' 
            FROM responder
        ");
        $stmt->execute();

        // Sync from rescuer table
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO users (email, password, role, name, status) 
            SELECT resc_email, resc_password, 'rescuer', resc_name, 'active' 
            FROM rescuer
        ");
        $stmt->execute();
    } catch (Exception $e) {
        // Silently fail if tables don't exist yet
        error_log("Sync error: " . $e->getMessage());
    }
}

/**
 * Get role color for display.
 *
 * @param string $role
 * @return string
 */
function getRoleColor(string $role): string {
    $colors = [
        'admin' => '#ff4d6d',
        'staff' => '#00e5ff',
        'responder' => '#ffd700',
        'rescuer' => '#00ff88'
    ];
    return $colors[$role] ?? '#888';
}

/**
 * Get last login display text.
 *
 * @param string|null $lastLogin
 * @return string
 */
function getLastLoginDisplay(?string $lastLogin): string {
    if (empty($lastLogin)) {
        return 'Never';
    }
    
    $date = new DateTime($lastLogin);
    $now = new DateTime();
    $diff = $now->diff($date);
    
    if ($diff->d == 0) {
        return 'Today at ' . $date->format('g:i A');
    } elseif ($diff->d == 1) {
        return 'Yesterday at ' . $date->format('g:i A');
    } elseif ($diff->d < 7) {
        return $diff->d . ' days ago';
    } else {
        return $date->format('M j, Y');
    }
}

?>
