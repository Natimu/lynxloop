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
require_once __DIR__ . '/../app/models/Tables.php';
require_once __DIR__ . '/../app/models/Favorite.php';
require_once __DIR__ . '/../app/models/Message.php';
require_once __DIR__ . '/../app/models/PriceHistory.php';
require_once __DIR__ . '/../app/models/SavedSearch.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ListingsController.php';
require_once __DIR__ . '/../app/controllers/MessagesController.php';
require_once __DIR__ . '/../app/controllers/ProfileController.php';
require_once __DIR__ . '/../app/controllers/PagesController.php';
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

// Listing detail
$router->get('listings/show', 'ListingsController@show');

// Search
$router->get('listings/search', 'ListingsController@search');

// Bump
$router->post('listings/bump', 'ListingsController@bump');

// Favorite toggle (AJAX)
$router->post('listings/toggle-favorite', 'ListingsController@toggleFavorite');

// Quick message / "Still available?"
$router->post('listings/quick-message', 'ListingsController@quickMessage');

// Saved searches
$router->post('listings/save-search', 'ListingsController@saveSearch');
$router->post('listings/delete-saved-search', 'ListingsController@deleteSavedSearch');

// Messages
$router->get('messages', 'MessagesController@inbox');
$router->get('messages/show', 'MessagesController@show');
$router->get('messages/poll', 'MessagesController@poll');
$router->post('messages/reply', 'MessagesController@reply');
$router->post('messages/send', 'MessagesController@sendAjax');

// Profile
$router->get('profile', 'ProfileController@index');

// Static pages
$router->get('about', 'PagesController@about');
$router->get('support', 'PagesController@support');

$router->post('logout', 'AuthController@logout');

$url = $_GET['url'] ?? '';
$router->dispatch($_SERVER['REQUEST_METHOD'], $url);
