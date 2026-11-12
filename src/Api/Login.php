<?php
declare(strict_types=1);
namespace Course\Api;
use Course\Accounts\UserRepository;
use Course\Http\{Request, Response, HttpError};
final class Login
{
    public function __construct(private UserRepository $users, private Tokens $tokens) {}
    public function __invoke(Request $request): Response
    {
        $input = Json::object($request);
        $errors = [];
        if (array_diff(array_keys($input), ['email', 'password']) !== []) {
            $errors['fields'] = 'Разрешены только email и password';
        }
        $email = $input['email'] ?? null;
        if (!is_string($email) || strlen($email) > 254) {
            $errors['email'] = 'Email: строка не более 254 байтов';
        }
        $password = $input['password'] ?? null;
        if (!is_string($password) || strlen($password) > 72) {
            $errors['password'] = 'Пароль: строка не более 72 байтов';
        }
        if ($errors !== []) {
            throw new HttpError(422, 'validation_error', 'Проверьте поля', $errors);
        }

        $user = $this->users->byEmail(strtolower(trim($email)));
        if ($user === null || !password_verify($password, $user->passwordHash)) {
            throw new HttpError(401, 'invalid_credentials', 'Неверный email или пароль');
        }

        return Json::response([
            'access_token' => $this->tokens->issue($user->id),
            'token_type' => 'Bearer',
            'expires_in' => 900,
            'user' => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
        ]);
    }
}
