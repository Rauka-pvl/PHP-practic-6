<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Http\HttpError;
use Course\Http\Response;
use Course\Security\AccountSession;

final class Auth
{
    public function __construct(private UserRepository $users) {}
    public function current(): ?User
    {
        $id = $_SESSION['user_id'] ?? null;
        if (!is_int($id)) {
            return null;
        }
        $user = $this->users->find($id);
        if ($user === null) {
            unset($_SESSION['user_id']);
        }
        return $user;
    }
    public function login(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        if (preg_match('/^[\x21-\x7E]+$/D', $email) !== 1) {
            return false;
        }
        $user = $this->users->byEmail($email);
        if ($user === null || !password_verify($password, $user->passwordHash)) {
            return false;
        }
        AccountSession::renew();
        $_SESSION['user_id'] = $user->id;
        return true;
    }
    public function logout(): void
    {
        AccountSession::finish();
    }
    public function guard(bool $admin = false): User|Response
    {
        $user = $this->current();
        if ($user === null) {
            return Response::redirect('/login');
        }
        if ($admin && $user->role !== 'admin') {
            throw new HttpError(403, 'forbidden', 'Действие доступно только администратору');
        }
        return $user;
    }
}
