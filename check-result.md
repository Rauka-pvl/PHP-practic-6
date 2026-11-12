# Результаты проверки — практическая работа № 6

Пароли тестовых учётных записей передавались через переменные окружения `P06_USER_PASSWORD` и `P06_ADMIN_PASSWORD` и в вывод не попадают. Полные JWT не сохранялись.

## php checks/check-json.php <папка проекта>

```
[OK] JSON: {}; application/json; 200
[OK] JSON: []; application/json; 400
[OK] JSON: {; application/json; 400
[OK] JSON: null; application/json; 400
[OK] JSON: {}; text/plain; 415
[OK] JSON: {}; application/json; charset=UTF-8; 200
[OK] JSON response
RESULT PASS 7 checks; step=json
```

## php checks/check.php http://localhost:8080 all user@example.test admin@example.test

```
[OK] Неподдерживаемый метод HTTP 405 (получен 405)
[OK] Неподдерживаемый метод: X-Request-Id
[OK] Неподдерживаемый метод: ошибка JSON
[OK] Allow содержит GET и POST
[OK] Неизвестный адрес HTTP 404 (получен 404)
[OK] Неизвестный адрес: X-Request-Id
[OK] Неизвестный адрес: ошибка JSON
[OK] ID с ведущим нулём HTTP 404 (получен 404)
[OK] ID с ведущим нулём: X-Request-Id
[OK] ID с ведущим нулём: ошибка JSON
[OK] Повреждённый JSON HTTP 400 (получен 400)
[OK] Повреждённый JSON: X-Request-Id
[OK] Повреждённый JSON: ошибка JSON
[OK] Массив вместо объекта HTTP 400 (получен 400)
[OK] Массив вместо объекта: X-Request-Id
[OK] Массив вместо объекта: ошибка JSON
[OK] Нет Content-Type JSON HTTP 415 (получен 415)
[OK] Нет Content-Type JSON: X-Request-Id
[OK] Нет Content-Type JSON: ошибка JSON
[OK] Email не строка HTTP 422 (получен 422)
[OK] Email не строка: X-Request-Id
[OK] Email не строка: ошибка JSON
[OK] Неверный пароль HTTP 401 (получен 401)
[OK] Неверный пароль: X-Request-Id
[OK] Неверный пароль: ошибка JSON
[OK] Вход пользователя HTTP 200 (получен 200)
[OK] Вход пользователя: X-Request-Id
[OK] Получен JWT из трёх частей
[OK] Тип и срок токена
[OK] API не устанавливает сессионную cookie
[OK] Вход администратора HTTP 200 (получен 200)
[OK] Вход администратора: X-Request-Id
[OK] Нет токена HTTP 401 (получен 401)
[OK] Нет токена: X-Request-Id
[OK] Нет токена: ошибка JSON
[OK] Запрос Bearer аутентификации
[OK] Изменён payload JWT HTTP 401 (получен 401)
[OK] Изменён payload JWT: X-Request-Id
[OK] Изменён payload JWT: ошибка JSON
[OK] Пользователь не администратор HTTP 403 (получен 403)
[OK] Пользователь не администратор: X-Request-Id
[OK] Пользователь не администратор: ошибка JSON
[OK] Публичный список HTTP 200 (получен 200)
[OK] Публичный список: X-Request-Id
[OK] Список содержит записи
[OK] HEAD HTTP 200 (получен 200)
[OK] HEAD: X-Request-Id
[OK] HEAD без тела
[OK] Нет записи HTTP 404 (получен 404)
[OK] Нет записи: X-Request-Id
[OK] Нет записи: ошибка JSON
[OK] Поиск не строка HTTP 400 (получен 400)
[OK] Поиск не строка: X-Request-Id
[OK] Поиск не строка: ошибка JSON
[OK] Поиск с SQL-текстом HTTP 200 (получен 200)
[OK] Поиск с SQL-текстом: X-Request-Id
[OK] SQL-текст не раскрывает весь каталог
[OK] Клиент не назначает владельца HTTP 422 (получен 422)
[OK] Клиент не назначает владельца: X-Request-Id
[OK] Клиент не назначает владельца: ошибка JSON
[OK] Строка вместо целого JSON HTTP 422 (получен 422)
[OK] Строка вместо целого JSON: X-Request-Id
[OK] Строка вместо целого JSON: ошибка JSON
[OK] Категория другого варианта HTTP 422 (получен 422)
[OK] Категория другого варианта: X-Request-Id
[OK] Категория другого варианта: ошибка JSON
[OK] Создание HTTP 201 (получен 201)
[OK] Создание: X-Request-Id
[OK] ID новой записи
[OK] Location созданной записи
[OK] HTML-текст сохранён без изменения
[OK] Чтение созданной записи HTTP 200 (получен 200)
[OK] Чтение созданной записи: X-Request-Id
[OK] Изменение запрещено пользователю HTTP 403 (получен 403)
[OK] Изменение запрещено пользователю: X-Request-Id
[OK] Изменение запрещено пользователю: ошибка JSON
[OK] Изменение администратором HTTP 200 (получен 200)
[OK] Изменение администратором: X-Request-Id
[OK] Изменения видны в ответе
[OK] Повторное сохранение тех же значений HTTP 200 (получен 200)
[OK] Повторное сохранение тех же значений: X-Request-Id
[OK] Изменение сохранилось между запросами
[OK] Удаление HTTP 204 (получен 204)
[OK] Удаление: X-Request-Id
[OK] 204 без тела
[OK] Удалённая запись отсутствует HTTP 404 (получен 404)
[OK] Удалённая запись отсутствует: X-Request-Id
[OK] Удалённая запись отсутствует: ошибка JSON
[OK] Повторное удаление HTTP 404 (получен 404)
[OK] Повторное удаление: X-Request-Id
[OK] Повторное удаление: ошибка JSON
[OK] Ключ вне веб-корня: HTTP 404
RESULT PASS 92 checks; step=all
```

## Запросы curl

`access_token` сокращён. После изменения администратором показана строка из MySQL.

```
$ curl -X POST /api/login  (user@example.test, пароль из login.json)
{"access_token":"eyJ0eXAiOi…(сокращено)","token_type":"Bearer","expires_in":900,"user":{"id":1,"name":"Студент","role":"user"}}

$ curl -i "/api/items?q=адап"
HTTP/1.1 200 OK
Cache-Control: no-store
X-Request-Id: 97a93dd8f24b3681
Content-Type: application/json; charset=UTF-8

{"items":[{"id":3,"title":"Адаптер","category":"device","quantity":0,"priceMinor":5000,"ownerId":null}]}

$ curl -i -X POST /api/items  (без JWT)
HTTP/1.1 401 Unauthorized
Cache-Control: no-store
X-Request-Id: 123299680939fcdf
Content-Type: application/problem+json; charset=UTF-8
WWW-Authenticate: Bearer

{"type":"about:blank","title":"Нужен Bearer JWT","status":401,"code":"token_required","errors":{},"requestId":"123299680939fcdf"}

$ curl -i -X POST /api/items  (Bearer пользователя)
HTTP/1.1 201 Created
Cache-Control: no-store
X-Request-Id: 98425577c90d93a6
Content-Type: application/json; charset=UTF-8
Location: /api/items/7

{"item":{"id":7,"title":"Запись API","category":"device","quantity":2,"priceMinor":150,"ownerId":1}}

$ curl -i -X PUT /api/items/1  (Bearer пользователя)
HTTP/1.1 403 Forbidden
Cache-Control: no-store
X-Request-Id: 28047a72450c8451
Content-Type: application/problem+json; charset=UTF-8

{"type":"about:blank","title":"Операция доступна только администратору","status":403,"code":"forbidden","errors":{},"requestId":"28047a72450c8451"}

$ curl -i -X POST /api/items  (ownerId и quantity строкой)
HTTP/1.1 422 Unknown Status Code
Cache-Control: no-store
X-Request-Id: ee6f9956371b7793
Content-Type: application/problem+json; charset=UTF-8

{"type":"about:blank","title":"Проверьте поля","status":422,"code":"validation_error","errors":{"fields":"Неизвестные поля","quantity":"Ожидается целое 0–99"},"requestId":"ee6f9956371b7793"}

$ curl -i -X PUT /api/items/7  (Bearer администратора)
HTTP/1.1 200 OK
Cache-Control: no-store
X-Request-Id: 0d68921d0b745427
Content-Type: application/json; charset=UTF-8

{"item":{"id":7,"title":"Запись API (изменена)","category":"device","quantity":5,"priceMinor":990,"ownerId":1}}

+----+-------------------------------------+----------+-------------+----------+
| id | title                               | quantity | price_minor | owner_id |
+----+-------------------------------------+----------+-------------+----------+
|  7 | Запись API (изменена)               |        5 |         990 |        1 |
+----+-------------------------------------+----------+-------------+----------+

$ curl -i -X DELETE /api/items/7  (Bearer администратора)
HTTP/1.1 204 No Content
Cache-Control: no-store
X-Request-Id: a40207ae5c7ae879
Content-type: text/html; charset=UTF-8


$ curl -i /api/items/7  (после удаления)
HTTP/1.1 404 Not Found
Cache-Control: no-store
X-Request-Id: d706df3bd75ae063
Content-Type: application/problem+json; charset=UTF-8

{"type":"about:blank","title":"Запись не найдена","status":404,"code":"not_found","errors":{},"requestId":"d706df3bd75ae063"}

```
