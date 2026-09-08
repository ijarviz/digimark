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

// TikTok link management + Instagram publish — content_manager + admin only.
$routes->group('', ['filter' => 'role:admin,content_manager'], static function ($routes) {
    $routes->get('tiktok/links/new', 'TikTok\LinkController::new');
    $routes->post('tiktok/links', 'TikTok\LinkController::create');
    $routes->post('tiktok/links/(:num)/delete', 'TikTok\LinkController::delete/$1');
    $routes->post('tiktok/links/(:num)/refresh-one', 'TikTok\LinkController::refreshOne/$1');

    $routes->get('publish', 'Instagram\PublishController::index');
    $routes->get('publish/new', 'Instagram\PublishController::new');
    $routes->get('publish/calendar', 'Instagram\PublishController::calendar');
    $routes->get('publish/performance', 'Instagram\ContentPerformanceController::index');
    $routes->post('publish', 'Instagram\PublishController::create');
    $routes->get('publish/(:num)/edit', 'Instagram\PublishController::edit/$1');
    $routes->post('publish/(:num)/update', 'Instagram\PublishController::update/$1');
    $routes->post('publish/(:num)/cancel', 'Instagram\PublishController::cancel/$1');
    $routes->post('publish/(:num)/retry', 'Instagram\PublishController::retry/$1');
});

// Influencer Discovery — admin-only (restricted from its earlier all-roles view).
$routes->group('', ['filter' => 'role:admin'], static function ($routes) {
    $routes->get('discovery/influencers', 'Discovery\InfluencerDiscoveryController::index');
    $routes->post('discovery/influencers/save', 'Discovery\InfluencerDiscoveryController::save');
});

// Admin-only area.
$routes->group('admin', ['filter' => 'role:admin'], static function ($routes) {
    $routes->get('users', 'Admin\UserController::index');
    $routes->get('users/new', 'Admin\UserController::new');
    $routes->post('users', 'Admin\UserController::create');
    $routes->get('users/(:num)/edit', 'Admin\UserController::edit/$1');
    $routes->post('users/(:num)/update', 'Admin\UserController::update/$1');

    $routes->get('api-settings', 'Admin\MetaAppConfigController::edit');
    $routes->post('api-settings', 'Admin\MetaAppConfigController::update');

    $routes->get('ig-account', 'Instagram\OAuthController::status');
    $routes->get('ig-account/connect', 'Instagram\OAuthController::connect');
    $routes->get('ig-account/callback', 'Instagram\OAuthController::callback');

    $routes->get('ads-account/select', 'Admin\AdsAccountController::select');
    $routes->post('ads-account/choose', 'Admin\AdsAccountController::choose');

    $routes->get('job-logs', 'Admin\JobLogController::index');
    $routes->post('job-logs/toggle-monitoring', 'Admin\JobLogController::toggleMonitoring');

    // Jarvis Power — "Improve Me". Admin-only prompt box that opens a
    // GitHub issue bridging into a claude.ai cloud routine (works on a
    // branch, opens a PR). Role is re-checked in the controller too.
    $routes->get('improve-me', 'Admin\Improve\ImproveMeController::index');
    $routes->post('improve-me/submit', 'Admin\Improve\ImproveMeController::submit');
});
