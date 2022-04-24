<?php
/*
 * @Description:
 * @Author: freeair
 * @Date: 2021-06-25 11:16:41
 * @LastEditors: freeair
 * @LastEditTime: 2022-04-24 23:27:34
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
    $routes->get('account/basic_setting/form', 'Account::getBasicSettingFormParam');
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
    $routes->get('generator/event', 'GeneratorEvent::getRecord');
    $routes->post('generator/event', 'GeneratorEvent::newRecord');
    $routes->put('generator/event', 'GeneratorEvent::updateRecord');
    $routes->delete('generator/event', 'GeneratorEvent::delRecord');

    $routes->get('generator/event/statistic', 'GeneratorEvent::getStatisticChartData');
    $routes->get('generator/event/export', 'GeneratorEvent::getExportRecordsAsExcel');
    $routes->get('generator/event/sync/kkx', 'GeneratorEvent::getSyncRecordToKKX');
    //
    $routes->post('meters', 'Meters::newRecord');
    $routes->get('meters', 'Meters::getRecord');
    $routes->get('meters/record/detail', 'Meters::getRecordDetail');
    $routes->put('meters', 'Meters::updateRecord');
    $routes->delete('meters', 'Meters::delRecord');
    $routes->get('meters/report/daily', 'Meters::getReportDaily');
    //
    $routes->get('meters/plan_deal', 'Meters::getPlanAndDealList');
    $routes->put('meters/plan_deal', 'Meters::updatePlanAndDealRecord');
    $routes->get('meters/statistic/chart/data', 'Meters::getStatisticChartData');
    $routes->get('meters/statistic/overall', 'Meters::getStatisticOverall');
    //
    $routes->get('dts/query_params', 'Dts::getQueryParams');
    $routes->get('dts/blank_form', 'Dts::getBlankForm');
    $routes->get('dts/device_list', 'Dts::getDeviceList');

    $routes->get('dts/list', 'Dts::getList');
    $routes->get('dts/details', 'Dts::getDetails');
    $routes->put('dts/progress', 'Dts::updateProgress');
    //
    $routes->post('dts/attachment', 'Dts::uploadAttachment');
    $routes->delete('dts/attachment', 'Dts::delAttachment');
    //
    $routes->post('dts', 'Dts::createOne');
    $routes->get('dts', 'Dts::queryEntry');
    //
    $routes->get('dts/ticket/download/attachment', 'Dts::getTicketAttachment');
    $routes->put('dts/ticket/handler', 'Dts::putTicketHandler');
    $routes->post('dts/ticket/toReview', 'Dts::postTicketToReview');
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
