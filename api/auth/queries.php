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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARSET=utf8;");
}

?>