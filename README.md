# Практическая работа № 6: JSON API и JWT

**Студент:** группа CS-302(С)
**Вариант:** A (техника, категории `device` и `accessory`)

Каталог из работ № 4–5 получил JSON API по адресу `/api/...`. Клиент входит через `POST /api/login` и получает JWT (HS256, срок 15 минут), затем передаёт его в заголовке `Authorization: Bearer <token>`. Чтение каталога доступно всем, добавление — любому вошедшему пользователю, изменение и удаление — только администратору. Роль не хранится в токене: после проверки подписи пользователь каждый раз читается из таблицы `users`. Обычный сайт с сессиями из работы № 5 продолжает работать.

## Среда

| Компонент | Версия |
|---|---|
| PHP | 8.4.11 |
| Composer | 2.8.1 |
| firebase/php-jwt | 7.2.1 |
| Сервер БД | MySQL 9.4.0 |
| Адрес | http://localhost:8080/ |
| Веб-корень | `practice_04/public` (папка проекта из работ № 4–5) |

## Запуск через Uniform Server

1. Остановить Apache. В учебный VirtualHost внутри `<Directory ".../public">` добавить `CGIPassAuth On` (готовый вариант — `apache-course.conf`), иначе PHP не получит заголовок `Authorization`. Запустить Apache и MySQL.
2. В `public/.htaccess` правило API стоит сразу после `RewriteEngine On`, до общего правила сайта:
   ```apache
   RewriteRule ^api(?:/.*)?$ api.php [QSA,END]
   ```
3. Установить библиотеку JWT и зависимости:
   ```
   composer require firebase/php-jwt:7.2.1
   composer install
   ```
4. Создать новый локальный ключ подписи (32 случайных байта в `var/jwt.key`, вне веб-корня, не сдаётся):
   ```
   php bin/p06-key.php
   ```
5. БД — проверочная база из работы № 5 (`config.local.php` указывает на неё). Зарегистрировать через `/register` учётные записи `user@example.test` и `admin@example.test`, затем в phpMyAdmin:
   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'admin@example.test';
   ```
6. Открыть http://localhost:8080/api/items и страницу проверки http://localhost:8080/api-lab.php.
7. Запуск проверки (пароли — только в переменных окружения, в файлы не записываются):
   ```powershell
   $env:P06_USER_PASSWORD = '...'
   $env:P06_ADMIN_PASSWORD = '...'
   php checks/check-json.php .
   php checks/check.php http://localhost:8080 all user@example.test admin@example.test
   ```

## Путь запроса

`public/api.php` → `p06-compose.php` (PDO, ключ из `var/jwt.key`, репозитории) → `Router` из работы № 4 (маршруты в `p06-routes.php`) → `Api\Catalog` / `Api\Login` → для защищённых операций `Api\Identity`: заголовок `Bearer` → `Tokens::subject()` проверяет подпись, `iss`, `aud`, `exp` → `UserRepository::find()` → проверка роли → `ItemData::from()` → репозиторий (подготовленные запросы) → `Json::response()`. Ошибки отдаются как `application/problem+json` с `X-Request-Id`; API не открывает сессию и не ставит cookie.

## Что сделано

| Код | Файл | Реализация |
|---|---|---|
| `P06-ROUTES` | `p06-routes.php` | `POST /api/login`, `GET`/`POST /api/items`, `GET`/`PUT`/`DELETE /api/items/{id}`; HEAD обслуживается GET-обработчиком маршрутизатора |
| `P06-INPUT` | `src/Api/Json.php` | основной тип `Content-Type` без учёта регистра и параметров должен быть `application/json`, иначе 415; `json_decode(…, false, 32, JSON_THROW_ON_ERROR)`; ошибка разбора — 400 `invalid_json`; не объект (массив, строка, `null`) — 400 `object_required` |
| `P06-OUTPUT` | там же | `json_encode` с `JSON_UNESCAPED_UNICODE`, `JSON_UNESCAPED_SLASHES`, `JSON_THROW_ON_ERROR`; `Content-Type: application/json; charset=UTF-8` |
| `P06-LOGIN` | `src/Api/Login.php` | только поля `email` и `password`, обе строки (email до 254 байтов, пароль до 72), иначе 422; email в нижнем регистре; `byEmail()` + `password_verify()`, при ошибке 401 `invalid_credentials`; ответ `access_token`, `token_type: Bearer`, `expires_in: 900`, `user` (id, имя, роль); без сессии |
| `P06-IDENTITY` | `src/Api/Identity.php` | нет или неверный формат `Bearer` — 401 с `WWW-Authenticate: Bearer`; неверная подпись или истёкший срок — 401 из `Tokens`; удалённый пользователь — 401 `invalid_token`; при `admin = true` роль не `admin` — 403 |
| `P06-LIST` | `src/Api/Catalog.php` | `queryText('q')`; пусто — `all()`, иначе `matching()`; `{"items": [...]}` |
| `P06-SHOW` | там же | `{"item": {...}}`, нет записи — 404 |
| `P06-CREATE` | там же | сначала JWT, затем JSON и `ItemData::from()` (целые только как числа JSON, `ownerId` от клиента — 422, категория другого варианта — 422); владелец — ID из `users`; 201 и `Location: /api/items/{id}` |
| `P06-UPDATE` | там же | администратор, запись существует, новые данные через `AdminItems::update()`; 200 со значениями, повторно прочитанными из БД |
| `P06-DELETE` | там же | администратор, запись существует, `AdminItems::delete()`; 204 без тела |

Дополнительно:

- `apache-course.conf` — `CGIPassAuth On`; `public/.htaccess` — правило `/api`.
- `router.php` (встроенный сервер PHP для автопроверки) направляет `/api` в `public/api.php`.
- `.gitignore` исключает `vendor/`, `var/` (ключ JWT и сессии), `config.local.php`, `login.json`.
- `stage.php` возвращает 6.

## Результаты проверок

- `php checks/check-json.php .` — `RESULT PASS 7 checks; step=json`.
- `php checks/check.php http://localhost:8080 all user@example.test admin@example.test` — `RESULT PASS 92 checks; step=all`.
- Полный вывод и запросы curl (без паролей и JWT): [check-result.md](check-result.md).

| Случай | Результат |
|---|---|
| Список, поиск, существующий ID | 200, JSON с данными |
| Нет JWT при добавлении | 401, `WWW-Authenticate: Bearer` |
| Изменена средняя часть JWT | 401, операция не выполняется |
| Истёк срок JWT | 401, нужен новый вход |
| Обычный пользователь изменяет или удаляет | 403, БД не изменяется |
| Неверный JSON / не объект | 400 |
| Не JSON `Content-Type` | 415 после успешной проверки доступа |
| Неверные поля, чужая категория, `ownerId` | 422 |
| Создание пользователем | 201 и `Location`, владелец — ID из `users` |
| Изменение администратором | 200, новые значения видны в MySQL и при повторном GET |
| Удаление администратором | 204 без тела, затем 404 |
| HEAD списка | 200 без тела |
| `/var/jwt.key` по HTTP | 404 (ключ вне веб-корня) |

Дополнительное задание «Каталог на Laravel» выполнено отдельным проектом: [PHP-practic-6-dop](https://github.com/Rauka-pvl/PHP-practic-6-dop).
