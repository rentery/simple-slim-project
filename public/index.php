<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

use function App\Users\setUser;
use function App\Users\getUsers;
use App\Validator;

session_start();

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['name' => '', 'email' => '', 'id' => ''],
        'errors' => [],
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});//->setName('newUser');

$app->post('/users', function ($request, $response) use ($router) {
    $user = $request->getParsedBodyParam('user');
    $validator = new Validator();
    $errors = $validator->validate($user);
    if (count($errors) == 0) {
        $user['id'] = uniqid();
        $users = json_decode($request->getCookieParam('users', json_encode([])), true);
        $users[] = $user;
        $encodedUsers = json_encode($users);
        $this->get('flash')->addMessage('succes', 'User added');
        $url = $router->urlFor('users');
        return $response->withHeader('Set-cookie', "users={$encodedUsers}")
                        ->withRedirect($url, 302);
    }
    $params = [
        'user' => $user,
        'errors' => $errors,
    ];
    return $this->get('renderer')->render($response->withStatus(422), 'users/new.phtml', $params);
});

$app->get('/users', function ($request, $response) {
    $users = json_decode($request->getCookieParam('users', json_encode([])), true);
    $flash = $this->get('flash')->getMessages();
    $params = [
        'users' => $users,
        'flash' => $flash
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

$app->get('/users/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $users = json_decode($request->getCookieParam('users', json_decode([])), true);
    $user = collect($users)->firstWhere('id', $id);
    if (!$user) {
        return $response->withStatus(404)->write('Page not found');
    }
    $params = [
        'user' => $user,
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
    
})->setName('user');

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->run();