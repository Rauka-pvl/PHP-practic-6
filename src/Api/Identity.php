<?php
declare(strict_types=1);
namespace Course\Api;
use Course\Accounts\{User, UserRepository};
use Course\Http\{Request, HttpError};
final class Identity
{
    public function __construct(private UserRepository $users, private Tokens $tokens) {}
    public function require(Request $request, bool $admin = false): User
    {
        $header = $request->header('Authorization') ?? '';
        if (preg_match('/^Bearer ([A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+\.[A-Za-z0-9_\-]+)$/D', $header, $match) !== 1) {
            throw new HttpError(401, 'token_required', 'Нужен Bearer JWT', [], ['WWW-Authenticate' => 'Bearer']);
        }
        $user = $this->users->find($this->tokens->subject($match[1]));
        if ($user === null) {
            throw new HttpError(401, 'invalid_token', 'Пользователь больше не существует', [],
                ['WWW-Authenticate' => 'Bearer error="invalid_token"']);
        }
        if ($admin && $user->role !== 'admin') {
            throw new HttpError(403, 'forbidden', 'Операция доступна только администратору');
        }
        return $user;
    }
}
