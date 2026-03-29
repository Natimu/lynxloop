<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;


require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Listing.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ListingsController.php';
require_once __DIR__ . '/../app/core/Auth.php';

if (session_status() !== PHP_SESSION_ACTIVE){
    session_start();
}

$router = new Router();

$router->get('', 'HomeController@index');

$router->get('login', 'AuthController@loginForm');
$router->post('login', 'AuthController@login');

$router->get('register', 'AuthController@registerForm');
$router->post('register', 'AuthController@register');

$router->get('dashboard', 'DashboardController@dashboard');

$router->get('listings/create', 'ListingsController@create');
$router->post('listings', 'ListingsController@store');

$router->post('logout', 'AuthController@logout');

$url = $_GET['url'] ?? '';
$router->dispatch($_SERVER['REQUEST_METHOD'], $url);
