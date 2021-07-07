<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-07-05 20:27:30
 */

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
// $routes->get('/', 'Home::index');

// Mix code: API routes
$routes->group('api', function ($routes) {
    $routes->post('auth/login', 'Home::login');
    $routes->post('auth/logout', 'Home::logout');
    $routes->get('user/info', 'Home::getUserInfo');
    //
    $routes->get('role', 'Home::getRole');
    $routes->post('role', 'Home::newRole');
    $routes->put('role', 'Home::updateRole');
    $routes->delete('role', 'Home::delRole');
    //
    $routes->get('menu', 'Home::getMenu');
    //
    $routes->get('role_menu', 'Home::getRoleMenu');
    $routes->post('role_menu', 'Home::saveRoleMenu');
    //
    $routes->get('dept', 'Home::getDept');
    $routes->post('dept', 'Home::newDept');
    $routes->put('dept', 'Home::UpdateDept');
    $routes->delete('dept', 'Home::delDept');
    //
    $routes->get('job', 'Home::getJob');
    $routes->post('job', 'Home::newJob');
    $routes->put('job', 'Home::updateJob');
    $routes->delete('job', 'Home::delJob');
    //
    $routes->get('title', 'Home::getTitle');
    $routes->post('title', 'Home::newTitle');
    $routes->put('title', 'Home::updateTitle');
    $routes->delete('title', 'Home::delTitle');
    //
    $routes->get('politic', 'Home::getPolitic');
    $routes->post('politic', 'Home::newPolitic');
    $routes->put('politic', 'Home::updatePolitic');
    $routes->delete('politic', 'Home::delPolitic');
    //
    $routes->get('user', 'Home::getUser');
    $routes->post('user', 'Home::newUser');
    $routes->put('user', 'Home::updateUser');
    $routes->delete('user', 'Home::delUser');
    //
    $routes->add('(:any)', '404');
});

$routes->add('(:any)', 'Home::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
