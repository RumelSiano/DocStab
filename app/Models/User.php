<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Covers all three roles: requestor (individual accounts, created via
 * self-registration with a personal email), sta (a single shared counter
 * account), and admin (the supervisor account).
 */
class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function create(string $email, string $password, string $fullName, string $role = 'requestor'): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (email, password_hash, full_name, role, created_at)
             VALUES (:email, :password, :full_name, :role, NOW())"
        );
        $stmt->execute([
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'full_name' => $fullName,
            'role'      => $role,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}