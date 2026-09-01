<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', static fn () => redirect()->to('/dashboard/instagram'));

// Auth (unauthenticated)
$routes->get('login', 'Auth\LoginController::new');
$routes->post('login', 'Auth\LoginController::attempt');
$routes->get('logout', 'Auth\LoginController::logout');

// Dashboards + TikTok link list — all three roles can view.
$routes->group('', ['filter' => 'role:admin,content_manager,viewer'], static function ($routes) {
    $routes->get('dashboard/instagram', 'Instagram\DashboardController::index');
    $routes->get('dashboard/instagram/chart-data', 'Instagram\DashboardController::chartData');

    $routes->get('dashboard/tiktok', 'TikTok\DashboardController::index');

    $routes->get('tiktok/links', 'TikTok\LinkController::index');
});

// TikTok link management — content_manager + admin only.
$routes->group('', ['filter' => 'role:admin,content_manager'], static function ($routes) {
    $routes->get('tiktok/links/new', 'TikTok\LinkController::new');
    $routes->post('tiktok/links', 'TikTok\LinkController::create');
});

// Admin-only area.
$routes->group('admin', ['filter' => 'role:admin'], static function ($routes) {
    $routes->get('users', 'Admin\UserController::index');
    $routes->get('users/new', 'Admin\UserController::new');
    $routes->post('users', 'Admin\UserController::create');

    $routes->get('ig-account', 'Instagram\OAuthController::status');
    $routes->get('ig-account/connect', 'Instagram\OAuthController::connect');
    $routes->get('ig-account/callback', 'Instagram\OAuthController::callback');

    $routes->get('job-logs', 'Admin\JobLogController::index');
});
