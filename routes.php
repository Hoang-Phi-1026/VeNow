<?php
// Routes cho Admin
$router->get('/admin/pending-events', 'AdminController@pendingEvents');
$router->post('/admin/approve-event', 'AdminController@approveEvent');
$router->post('/admin/reject-event', 'AdminController@rejectEvent');

// Routes cho Organizer
$router->get('/organizer/events', 'OrganizerEventController@index');
$router->get('/organizer/events/edit/{id}', 'OrganizerEventController@edit');
$router->post('/organizer/events/edit/{id}', 'OrganizerEventController@edit');
$router->get('/organizer/events/delete/{id}', 'OrganizerEventController@delete');

// Routes cho Event
$router->get('/events', 'EventController@index');
$router->get('/event/{id}', 'EventController@show');
$router->get('/events/create', 'EventController@create');
$router->post('/events/store', 'EventController@store');

// Routes cho User
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

// Routes cho Search
$router->get('/search', 'SearchController@index');
$router->get('/search/index', 'SearchController@index');

$routes = [
    // Các routes hiện có
    'organizer/events' => ['OrganizerEventController', 'index'],
    'organizer/events/edit/{id}' => ['OrganizerEventController', 'edit'],
    'organizer/events/delete/{id}' => ['OrganizerEventController', 'delete'],
]; 