<?php
declare(strict_types=1);

use Course\Http\Request;
use Course\Http\Response;
use Course\Http\Router;
use Course\Controller\AccountController;
use Course\Controller\AccountCatalogController;

return static function (Router $router, AccountController $accounts, AccountCatalogController $catalog): void {
    $router->add('GET', '/', static fn(Request $r): Response => $catalog->listing($r));
    $router->add('GET', '/items', static fn(Request $r): Response => $catalog->listing($r));
    $router->add('GET', '/items/new', static fn(Request $r): Response => $catalog->newForm());
    $router->add('GET', '/items/{id}', static fn(Request $r, int $id): Response => $catalog->show($id));
    $router->add('POST', '/items', static fn(Request $r): Response => $catalog->save($r));
    $router->add('GET', '/items/{id}/edit', static fn(Request $r, int $id): Response => $catalog->editForm($id));
    $router->add('POST', '/items/{id}/edit', static fn(Request $r, int $id): Response => $catalog->save($r, $id));
    $router->add('POST', '/items/{id}/delete', static fn(Request $r, int $id): Response => $catalog->delete($r, $id));
    $router->add('GET', '/register', static fn(Request $r): Response => $accounts->form(true));
    $router->add('GET', '/login', static fn(Request $r): Response => $accounts->form(false));
    $router->add('POST', '/register', static fn(Request $r): Response => $accounts->register($r));
    $router->add('POST', '/login', static fn(Request $r): Response => $accounts->login($r));
    $router->add('POST', '/logout', static fn(Request $r): Response => $accounts->logout($r));
};
