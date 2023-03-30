<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2023-03-30 22:20:01
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

// My code: API routes
$routes->group('api', function ($routes) {
    $routes->post('auth/login', 'Auth::login');
    $routes->post('auth/logout', 'Auth::logout');
    $routes->post('auth/sms', 'Auth::sms');
    $routes->post('auth/reset-password', 'Auth::resetPassword');
    //
    $routes->get('account', 'Account::queryEntry');
    $routes->put('account/password', 'Account::updatePassword');
    $routes->put('account/phone', 'Account::updatePhone');
    $routes->put('account/sms', 'Account::sms');
    $routes->put('account/email', 'Account::updateEmail');
    $routes->put('account', 'Account::updateBasicSetting');
    $routes->post('account/avatar', 'Account::updateAvatar');
    //
    $routes->get('role', 'Admin::getRole');
    $routes->post('role', 'Admin::newRole');
    $routes->put('role', 'Admin::updateRole');
    $routes->delete('role', 'Admin::delRole');
    //
    $routes->get('menu', 'Admin::getMenu');
    $routes->post('menu', 'Admin::newMenu');
    $routes->put('menu', 'Admin::updateMenu');
    $routes->delete('menu', 'Admin::delMenu');
    //
    $routes->get('api', 'Admin::getApi');
    $routes->post('api', 'Admin::newApi');
    $routes->put('api', 'Admin::updateApi');
    $routes->delete('api', 'Admin::delApi');
    //
    $routes->get('workflow', 'Admin::getWorkflow');
    $routes->post('workflow', 'Admin::newWorkflow');
    $routes->put('workflow', 'Admin::updateWorkflow');
    $routes->delete('workflow', 'Admin::delWorkflow');
    //
    $routes->get('role_menu', 'Admin::getRoleMenu');
    $routes->post('role_menu', 'Admin::saveRoleMenu');
    $routes->get('role_api', 'Admin::getRoleApi');
    $routes->post('role_api', 'Admin::saveRoleApi');
    $routes->get('role_dept', 'Admin::getRoleDept');
    $routes->post('role_dept', 'Admin::saveRoleDept');
    $routes->get('role_workflow', 'Admin::getRoleWorkflow');
    $routes->post('role_workflow', 'Admin::saveRoleWorkflow');
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
    $routes->get('generator/event', 'GeneratorEvent::queryEntry');
    $routes->post('generator/event', 'GeneratorEvent::newRecord');
    $routes->put('generator/event', 'GeneratorEvent::updateRecord');
    $routes->delete('generator/event', 'GeneratorEvent::delRecord');
    // 2022-5-1
    $routes->get('meter', 'Meter::queryEntry');
    $routes->post('meter', 'Meter::newRecord');
    $routes->put('meter', 'Meter::updateRecord');
    $routes->delete('meter', 'Meter::delRecord');
    $routes->put('meter/plan_deal', 'Meter::updatePlanAndDealRecord');
    // 2022-5-1
    $routes->post('meters', 'Meters::newRecord');
    $routes->get('meters', 'Meters::getRecord');
    $routes->get('meters/record/detail', 'Meters::getRecordDetail');
    $routes->put('meters', 'Meters::updateRecord');
    $routes->delete('meters', 'Meters::delRecord');
    $routes->get('meters/report/daily', 'Meters::getReportDaily');
    //
    $routes->get('meters/plan_deal', 'Meters::getPlanAndDealList');
    $routes->put('meters/plan_deal', 'Meters::updatePlanAndDealRecord');
    // $routes->get('meters/statistic/chart/data', 'Meters::getStatisticChartData');
    // $routes->get('meters/statistic/overall', 'Meters::getStatisticOverall');
    //
    $routes->get('dts/attachment', 'Dts::downloadAttachment');
    $routes->post('dts/attachment', 'Dts::uploadAttachment');
    $routes->delete('dts/attachment', 'Dts::delAttachment');

    $routes->get('dts', 'Dts::queryEntry');
    $routes->post('dts', 'Dts::createOne');
    $routes->delete('dts', 'Dts::deleteOne');
    $routes->put('dts', 'Dts::updateEntry');

    // 2023-2-21
    $routes->get('drawing', 'Drawing::queryEntry');
    $routes->post('drawing', 'Drawing::createOne');
    $routes->put('drawing', 'Drawing::updateOne');
    $routes->delete('drawing', 'Drawing::deleteOne');
    //
    $routes->post('drawing/file', 'Drawing::uploadFile');
    $routes->delete('drawing/file', 'Drawing::deleteFile');
    $routes->get('drawing/file', 'Drawing::downloadFile');
    // 2023-2-26
    $routes->get('party_branch', 'PartyBranch::queryEntry');
    $routes->post('party_branch', 'PartyBranch::createOne');
    //
    $routes->get('party_branch/file', 'PartyBranch::downloadFile');
    $routes->delete('party_branch/file', 'PartyBranch::deleteFile');
    $routes->post('party_branch/file', 'PartyBranch::uploadFile');
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
