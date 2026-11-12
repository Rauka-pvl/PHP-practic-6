<?php
declare(strict_types=1);
use Course\Api\{Catalog, Login};
use Course\Http\{Router, Request, Response};
return static function(Router $router, Catalog $catalog, Login $login): void {
    $router->add('POST', '/api/login', $login);
    $router->add('GET', '/api/items', static fn(Request $r): Response => $catalog->listing($r));
    $router->add('GET', '/api/items/{id}', static fn(Request $r, int $id): Response => $catalog->show($id));
    $router->add('POST', '/api/items', static fn(Request $r): Response => $catalog->create($r));
    $router->add('PUT', '/api/items/{id}', static fn(Request $r, int $id): Response => $catalog->update($r, $id));
    $router->add('DELETE', '/api/items/{id}', static fn(Request $r, int $id): Response => $catalog->delete($r, $id));
};
