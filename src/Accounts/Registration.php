<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Http\HttpError;

final class Registration
{
    /** @return array{name:string,email:string,password:string} */
    public static function validate(array $input): array
    {
        $errors = [];
        if (array_diff(array_keys($input), ['name', 'email', 'password', 'confirmation']) !== []) {
            $errors['fields'] = 'Неизвестные поля формы';
        }

        $name = $input['name'] ?? null;
        if (!is_string($name) || preg_match('//u', $name) !== 1) {
            $errors['name'] = 'Ожидается строка UTF-8';
            $name = '';
        } else {
            $name = trim($name);
            $length = mb_strlen($name, 'UTF-8');
            if ($length < 2 || $length > 80) $errors['name'] = 'Имя: от 2 до 80 символов';
        }

        $email = $input['email'] ?? null;
        if (!is_string($email)) {
            $errors['email'] = 'Укажите email';
            $email = '';
        } else {
            $email = strtolower(trim($email));
            if (preg_match('/^[\x21-\x7E]+$/D', $email) !== 1 || strlen($email) > 254
                || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Некорректный email (латиница, не более 254 байтов)';
            }
        }

        $password = $input['password'] ?? null;
        if (!is_string($password) || preg_match('//u', $password) !== 1 || str_contains($password, "\0")) {
            $errors['password'] = 'Пароль должен быть строкой UTF-8 без нулевого байта';
            $password = '';
        } elseif (mb_strlen($password, 'UTF-8') < 8) {
            $errors['password'] = 'Пароль: не менее 8 символов';
        } elseif (strlen($password) > 72) {
            $errors['password'] = 'Пароль: не более 72 байтов';
        }

        $confirmation = $input['confirmation'] ?? null;
        if (!is_string($confirmation) || $confirmation !== $password) {
            $errors['confirmation'] = 'Пароли не совпадают';
        }

        if ($errors !== []) {
            throw new HttpError(422, 'validation_error', 'Проверьте поля', $errors);
        }
        return ['name' => $name, 'email' => $email, 'password' => $password];
    }
}
