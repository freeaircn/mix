<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2021-10-02 22:04:57
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
    $routes->post('auth/login', 'Auth::login');
    $routes->post('auth/logout', 'Auth::logout');
    $routes->post('auth/sms', 'Auth::sms');
    $routes->post('auth/reset-password', 'Auth::resetPassword');
    //
    $routes->get('account/info', 'Account::getUserInfo');
    $routes->get('account/menus', 'Account::getUserMenus');
    $routes->put('account', 'Account::updateUserInfo');
    $routes->put('account/password', 'Account::updatePassword');
    $routes->put('account/phone', 'Account::updatePhone');
    $routes->put('account/sms', 'Account::sms');
    $routes->put('account/email', 'Account::updateEmail');
    $routes->post('account/avatar', 'Account::updateAvatar');
    //
    $routes->get('role', 'Admin::getRole');
    $routes->post('role', 'Admin::newRole');
    $routes->put('role', 'Admin::updateRole');
    $routes->delete('role', 'Admin::delRole');
    //
    $routes->get('menu', 'Admin::getMenu');
    //
    $routes->get('role_menu', 'Admin::getRoleMenu');
    $routes->post('role_menu', 'Admin::saveRoleMenu');
    //
    $routes->get('workflow/handler', 'Admin::getWorkflowHandler');
    //
    $routes->get('role_workflow_handler', 'Admin::getRoleWorkflowHandler');
    $routes->post('role_workflow_handler', 'Admin::saveRoleWorkflowHandler');
    //
    $routes->get('dept', 'Admin::getDept');
    $routes->post('dept', 'Admin::newDept');
    $routes->put('dept', 'Admin::UpdateDept');
    $routes->delete('dept', 'Admin::delDept');
    //
    $routes->get('job', 'Admin::getJob');
    $routes->post('job', 'Admin::newJob');
    $routes->put('job', 'Admin::updateJob');
    $routes->delete('job', 'Admin::delJob');
    //
    $routes->get('title', 'Admin::getTitle');
    $routes->post('title', 'Admin::newTitle');
    $routes->put('title', 'Admin::updateTitle');
    $routes->delete('title', 'Admin::delTitle');
    //
    $routes->get('politic', 'Admin::getPolitic');
    $routes->post('politic', 'Admin::newPolitic');
    $routes->put('politic', 'Admin::updatePolitic');
    $routes->delete('politic', 'Admin::delPolitic');
    //
    $routes->get('user', 'Admin::getUser');
    $routes->post('user', 'Admin::newUser');
    $routes->put('user', 'Admin::updateUser');
    $routes->delete('user', 'Admin::delUser');
    //
    $routes->get('user_role', 'Admin::getUserRole');
    //
    $routes->get('equipment_unit', 'Admin::getEquipmentUnit');
    $routes->post('equipment_unit', 'Admin::newEquipmentUnit');
    $routes->put('equipment_unit', 'Admin::updateEquipmentUnit');
    $routes->delete('equipment_unit', 'Admin::deleteEquipmentUnit');
    //
    $routes->get('generator/event', 'GeneratorEvent::getGeneratorEvent');
    $routes->post('generator/event', 'GeneratorEvent::newGeneratorEvent');
    $routes->put('generator/event', 'GeneratorEvent::updateGeneratorEvent');
    $routes->get('generator/event/statistic', 'GeneratorEvent::getGeneratorEventStatistic');
    $routes->get('generator/event/export', 'GeneratorEvent::getExportGeneratorEvent');
    $routes->delete('generator/event', 'GeneratorEvent::delGeneratorEvent');
    //
    $routes->post('meters', 'Meters::newMeterLogs');
    $routes->get('meters', 'Meters::getMeterLogs');
    $routes->get('meters/log_detail', 'Meters::getMetersLogDetail');
    $routes->delete('meters', 'Meters::delMeterLogs');
    $routes->get('meters/daily_report', 'Meters::getDailyReport');
    $routes->get('meters/basic_statistic', 'Meters::getBasicStatistic');
    $routes->get('meters/overall_statistic', 'Meters::getOverallStatistic');
    //
    $routes->get('meters/planning_kWh', 'Meters::getPlanningKWh');
    $routes->put('meters/planning_kWh', 'Meters::updatePlanningKWhRecord');
    //
    $routes->get('dts/progress/template', 'Dts::getProgressTemplate');
    $routes->get('dts/handler', 'Dts::getHandler');
    $routes->post('dts/draft', 'Dts::postDraft');
    $routes->get('dts/list', 'Dts::getForList');
    $routes->get('dts/ticket/details', 'Dts::getTicketDetails');
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
