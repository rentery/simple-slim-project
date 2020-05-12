<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$users = ['mike', 'mishel','adel', 'keks', 'kamila'];

//$app->post('/users', function ($request, $response) {
//    return $response->withStatus(302);
//});

/*$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParam('term');
    if (isset($term)) {
        $userTerm = [];
        foreach ($users as $user) {
            if (strpos($user, $term) !== false) {
                $userTerm[] = $user;
            }
        $users = $userTerm;
        }    
    }
    $params = ['users' => $users];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});*/

$app->get('/', function ($request, $response, $args) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['name' => '', 'email' => '', 'id' => ''],
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->post('/users', function ($request, $response) {
    $user = $request->getParsedBodyParam('user');
    $user['id'] = uniqid();
    $users_json = file_get_contents('./files/users.txt');
    $users = json_decode($users_json, true);
    $user_json = json_encode($user);
    print_r($user_json);
    $save = file_put_contents('./files/users.txt', $user_json, FILE_APPEND);
    return $response->withRedirect('/users', 302);
});

$app->get('/users', function ($request, $response) {
    $users_json = file_get_contents('./files/users.txt');
    print_r($users_json);
    $users = json_decode($users_json, true);
    print_r(json_last_error_msg());
    $params = ['users' => $users];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

/*$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => htmlspecialchars($args['id']), 'nickname' => htmlspecialchars('user-' . $args['id'])];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});*/

/*$app->get('/', function ($request, $response, $args) {
    return $response->write('open something like (you can change id): /companies/5');
});*/

$app->run();