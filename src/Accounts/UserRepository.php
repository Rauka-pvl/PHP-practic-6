<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Http\HttpError;

final class UserRepository
{
    public function __construct(private \PDO $pdo) {}
    private function user(array|false $row): ?User
    {
        return $row === false ? null : new User((int) $row['id'], $row['name'],
            $row['email'], $row['password_hash'], $row['role']);
    }
    public function find(int $id): ?User
    {
        $statement = $this->pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $this->user($statement->fetch());
    }
    public function byEmail(string $email): ?User
    {
        $statement = $this->pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email');
        $statement->execute(['email' => $email]);
        return $this->user($statement->fetch());
    }
    public function create(string $name, string $email, string $hash): void
    {
        $statement = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, 'user')");
        try {
            $statement->execute(['name' => $name, 'email' => $email, 'hash' => $hash]);
        } catch (\PDOException $error) {
            if ((int) ($error->errorInfo[1] ?? 0) === 1062) {
                throw new HttpError(422, 'validation_error', 'Проверьте поля', ['email' => 'Этот email уже зарегистрирован']);
            }
            throw $error;
        }
    }
}
