// Admin routes
$router->get('/admin/pending-events', 'AdminController@pendingEvents');
$router->post('/admin/approve-event', 'AdminController@approveEvent');
$router->post('/admin/reject-event', 'AdminController@rejectEvent'); 