<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RadioServerController;
use App\Http\Controllers\RcControlStationController;
use App\Http\Controllers\RcControlClientController;
use App\Http\Controllers\RcControlStationAppController;
use App\Http\Controllers\HostingStationController;
use App\Http\Controllers\HostingServerController;
use App\Http\Controllers\VideoServerController;
use App\Http\Controllers\VideoStreamingController;
use App\Http\Controllers\RadioStreamingController;
use App\Http\Controllers\StationController;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\TicketController;

use App\Http\Controllers\UserNotificationController;


use App\Http\Controllers\NotificationController;

use App\Http\Controllers\MailController;
use App\Http\Controllers\MailMessageController;
use App\Http\Controllers\MailFilterController;
use App\Http\Controllers\MailFolderController;
use App\Http\Controllers\MailLabelController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\RdomiStationController;
use App\Http\Controllers\RdomiServiceStatsController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\StationMailController;
use App\Http\Controllers\WHMCSClientController;
use App\Http\Controllers\WHMCSSyncController;
use App\Http\Controllers\WHMCSStationController;





















Route::middleware('auth:sanctum')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
});








Route::post('rccontrol/mailmessages/store', [MailMessageController::class, 'store']);
Route::post('rccontrol/mailmessages/index/{mail_id}', [MailMessageController::class, 'index']);

Route::post('rccontrol/mailfilters/index', [MailFilterController::class, 'index']);
Route::post('rccontrol/mailfilters/store', [MailFilterController::class, 'store']);
Route::post('rccontrol/mailfilters/show/{id}', [MailFilterController::class, 'show']);
Route::post('rccontrol/mailfilters/update/{id}', [MailFilterController::class, 'update']);
Route::post('rccontrol/mailfilters/destroy/{id}', [MailFilterController::class, 'destroy']);

Route::post('rccontrol/mailfolders/index', [MailFolderController::class, 'index']);
Route::post('rccontrol/mailfolders/store', [MailFolderController::class, 'store']);
Route::post('rccontrol/mailfolders/show/{id}', [MailFolderController::class, 'show']);
Route::post('rccontrol/mailfolders/update/{id}', [MailFolderController::class, 'update']);
Route::post('rccontrol/mailfolders/destroy/{id}', [MailFolderController::class, 'destroy']);

Route::post('rccontrol/maillabels/index', [MailLabelController::class, 'index']);
Route::post('rccontrol/maillabels/store', [MailLabelController::class, 'store']);
Route::post('rccontrol/maillabels/show/{id}', [MailLabelController::class, 'show']);
Route::post('rccontrol/maillabels/update/{id}', [MailLabelController::class, 'update']);
Route::post('rccontrol/maillabels/destroy/{id}', [MailLabelController::class, 'destroy']);

Route::post('rccontrol/tags/index', [TagController::class, 'index']);
Route::post('rccontrol/tags/store', [TagController::class, 'store']);
Route::post('rccontrol/tags/show/{id}', [TagController::class, 'show']);
Route::post('rccontrol/tags/update/{id}', [TagController::class, 'update']);
Route::post('rccontrol/tags/destroy/{id}', [TagController::class, 'destroy']);
Route::post('rccontrol/tags/sync', [TagController::class, 'syncTags']);






Route::post('rccontrol/mails/rcsend', [MailController::class, 'rcSend']);
Route::get('rccontrol/mail/rcsend', [MailController::class, 'rcSend']);
Route::post('rccontrol/mails/index', [MailController::class, 'index']);
Route::post('rccontrol/mails/store', [MailController::class, 'store']);
Route::post('rccontrol/mails/show/{id}', [MailController::class, 'show']);
Route::post('rccontrol/mails/update/{id}', [MailController::class, 'update']);
Route::post('rccontrol/mails/destroy/{id}', [MailController::class, 'destroy']);
Route::post('rccontrol/mails/getMailCounts', [MailController::class, 'getMailCounts']);

Route::post('rccontrol/mails/send-test-mail', [MailController::class, 'sendTestMail']); // Nueva ruta para enviar un correo de prueba





// http://rcdomintapi/api/rccontrol/notif/usr/destroy/all


Route::post('rccontrol/notif/usr/clean', [UserNotificationController::class, 'destroyAll']); 



Route::get('rccontrol/notif/send ', [ActivityController::class, 'createNotification']); 



Route::post('rccontrol/station/private/one ', [RcControlStationAppController::class, 'oneDomintControl']); 
Route::post('rccontrol/station/private/all ', [RcControlStationAppController::class, 'allDomintControl']); 


//notification user
Route::post('rccontrol/notif/usr/get ', [UserNotificationController::class, 'getUserNotifications']); 
Route::post('rccontrol/notif/usr/send/test ', [UserNotificationController::class, 'sendTestNotification']); 
Route::post('rccontrol/notif/usr/send/admin/test ', [UserNotificationController::class, 'sendAdminTestNotification']); 
//notification_id send
Route::post('rccontrol/notif/usr/read', [UserNotificationController::class, 'markAsRead']);
Route::post('rccontrol/notif/usr/destroy', [UserNotificationController::class, 'destroy']);




//notification


Route::post('rccontrol/notif/send/test ', [NotificationController::class, 'sendTestNotification']); 
Route::post('rccontrol/notif/index ', [NotificationController::class, 'getNotifications']); 
Route::post('rccontrol/notif/user/get ', [NotificationController::class, 'getUserNotifications']); 
Route::post('rccontrol/notif/admin/get ', [NotificationController::class, 'getAdminNotifications']); 
Route::post('rccontrol/notif/read/{id} ', [NotificationController::class, 'markAsRead']); 
Route::post('rccontrol/notif/read/all ', [NotificationController::class, 'markAllAsRead']); 
Route::post('rccontrol/notif/destroy ', [NotificationController::class, 'destroy']); 


Route::post('rccontrol/station/all', [RcControlStationController::class, 'all']);
Route::post('rccontrol/station/upd', [RcControlStationController::class, 'upd']);
Route::post('rccontrol/station/add', [RcControlStationController::class, 'add']);

//clients
Route::post('rccontrol/client/all', [RcControlClientController::class, 'RcAllControl']);
Route::post('rccontrol/client/get', [RcControlClientController::class, 'rcControlGet']);
Route::post('rccontrol/client/upd', [RcControlClientController::class, 'upd']);
Route::post('rccontrol/client/add', [RcControlClientController::class, 'add']);


Route::post('rccontrol/ticket/store', [TicketController::class, 'store']);
Route::post('rccontrol/ticket/index', [TicketController::class, 'index']);
Route::delete('rccontrol/ticket/{id}', [TicketController::class, 'destroy']);
Route::post('rccontrol/ticket/bulk-delete', [TicketController::class, 'bulkDestroy']);
Route::post('rccontrol/ticket/bulk-update-status', [TicketController::class, 'bulkUpdateStatus']);
Route::post('rccontrol/ticket/{id}/restore', [TicketController::class, 'restore']);
Route::post('rccontrol/ticket/bulk-permanent-delete', [TicketController::class, 'bulkPermanentDelete']);
Route::post('rccontrol/ticket/migrate-legacy-deleted', [TicketController::class, 'migrateLegacyDeleted']);
Route::post('rccontrol/ticket/delete-legacy-tickets', [TicketController::class, 'deleteLegacyTickets']);
Route::post('rccontrol/activities/delete-legacy-activities', [ActivityController::class, 'deleteLegacyActivities']);
Route::post('rccontrol/activities/delete-specific-legacy', [ActivityController::class, 'deleteSpecificLegacyActivities']);
Route::post('rccontrol/activities/delete-activity-701', [ActivityController::class, 'deleteActivity701']);
Route::post('rccontrol/activities/delete-individual-activity', [ActivityController::class, 'deleteIndividualActivity']);
Route::post('rccontrol/activities/force-delete-trash-activities', [ActivityController::class, 'forceDeleteTrashActivities']);
Route::get('rccontrol/activities/analyze-trash-activities', [ActivityController::class, 'analyzeTrashActivities']);
Route::get('rccontrol/activities/preview-ghost-activities', [ActivityController::class, 'previewGhostActivities']);
Route::post('rccontrol/activities/delete-ghost-activities', [ActivityController::class, 'deleteGhostActivities']);
Route::post('rccontrol/activities/delete-all-report-activities', [ActivityController::class, 'deleteAllReportActivities']);
Route::get('rccontrol/activities/test-legacy-activities', [ActivityController::class, 'testLegacyActivities']);
Route::get('rccontrol/activities/find-specific-legacy', [ActivityController::class, 'findSpecificLegacyActivities']);




Route::post('rccontrol/client/addimg', [RcControlClientController::class, 'addImg']);
Route::post('rccontrol/client/delimg', [RcControlClientController::class, 'delImg']);


Route::post('rccontrol/activities/index', [ActivityController::class, 'index']);
Route::post('activities/{id}', [ActivityController::class, 'show']);
Route::post('activities', [ActivityController::class, 'store']);
Route::put('activities/{id}', [ActivityController::class, 'update']);
Route::delete('activities/{id}', [ActivityController::class, 'destroy']);
Route::post('rccontrol/activities/client/{clientId}', [ActivityController::class, 'getActivitiesByClientId']);
Route::post('rccontrol/activities/station/{stationId}', [ActivityController::class, 'getActivitiesByStationId']);




Route::post('hosting-stations/index', [HostingStationController::class, 'index']);
Route::post('hosting-stations/store', [HostingStationController::class, 'store']);
Route::post('hosting-stations/show', [HostingStationController::class, 'show']);
Route::post('hosting-stations/update', [HostingStationController::class, 'update']);
Route::post('hosting-stations/destroy', [HostingStationController::class, 'destroy']);

Route::post('hosting-servers/index', [HostingServerController::class, 'index']);
Route::post('hosting-servers/store', [HostingServerController::class, 'store']);
Route::post('hosting-servers/show', [HostingServerController::class, 'show']);
Route::post('hosting-servers/update', [HostingServerController::class, 'update']);
Route::post('hosting-servers/destroy', [HostingServerController::class, 'destroy']);

Route::post('video-servers/index', [VideoServerController::class, 'index']);
Route::post('video-servers/store', [VideoServerController::class, 'store']);
Route::post('video-servers/show', [VideoServerController::class, 'show']);
Route::post('video-servers/update', [VideoServerController::class, 'update']);
Route::post('video-servers/destroy', [VideoServerController::class, 'destroy']);



Route::post('video-streaming/index', [VideoStreamingController::class, 'index']);
Route::post('video-streaming/store', [VideoStreamingController::class, 'store']);
Route::post('video-streaming/show', [VideoStreamingController::class, 'show']);
Route::post('video-streaming/update', [VideoStreamingController::class, 'update']);
Route::post('video-streaming/destroy', [VideoStreamingController::class, 'destroy']);





Route::post('radio-streaming/index', [RadioStreamingController::class, 'index']);
Route::post('radio-streaming/store', [RadioStreamingController::class, 'store']);
Route::post('radio-streaming/show', [RadioStreamingController::class, 'show']);
Route::post('radio-streaming/update', [RadioStreamingController::class, 'update']);
Route::post('radio-streaming/destroy', [RadioStreamingController::class, 'destroy']);



Route::post('radio-servers/index', [RadioServerController::class, 'index']); 
Route::post('radio-servers/store', [RadioServerController::class, 'store']); 
Route::post('radio-servers/update', [RadioServerController::class, 'update']);
Route::post('radio-servers/destroy', [RadioServerController::class, 'destroy']); 
Route::post('radio-servers/destroy', [RadioServerController::class, 'destroy']); 


Route::post('radio-servers/destroy', [RadioServerController::class, 'destroy']); 

Route::post('station/get', [StationController::class, 'get']);
Route::post('station/add/img', [StationController::class, 'addImg']);
Route::get('station/get', [StationController::class, 'get']);

Route::get('station/all', [RcControlStationController::class, 'all']);           // Interno (sin filtro)
Route::get('station/all-public', [RcControlStationController::class, 'allPublic']); // ✅ Nuevo
Route::get('station/all-radio', [RcControlStationController::class, 'allRadio']);
Route::get('station/all-tv', [RcControlStationController::class, 'allTv']);


Route::get('station/rdomi/all', [RdomiStationController::class, 'RdomiAll']);
Route::get('station/rdomi/radio', [RdomiStationController::class, 'RdomiRadio']);
Route::get('station/rdomi/tv', [RdomiStationController::class, 'RdomiTv']);
Route::get('station/rdomi/{id}', [RdomiStationController::class, 'RdomiById']);
Route::get('station/rdomi/client/{client_id}', [RdomiStationController::class, 'RdomiByClient']);

// Service Analytics Routes - ORIGINALES (MANTENER PARA COMPATIBILIDAD)
Route::post('rdomi/sts/service/ping', [RdomiServiceStatsController::class, 'incrementView']);
Route::post('rdomi/sts/service/status', [RdomiServiceStatsController::class, 'getStats']);
Route::post('rdomi/sts/service/health-check', [RdomiServiceStatsController::class, 'getMultipleStats']);

// Service Analytics Routes - SISTEMA AVANZADO (NUEVOS)
Route::post('rdomi/sts/service/ping-advanced', [RdomiServiceStatsController::class, 'incrementViewAdvanced']);
Route::get('rdomi/sts/analytics/hourly/{service_id}', [RdomiServiceStatsController::class, 'getHourlyStats']);
Route::get('rdomi/sts/analytics/daily/{service_id}', [RdomiServiceStatsController::class, 'getDailyStats']);
Route::get('rdomi/sts/analytics/dashboard/{service_id}', [RdomiServiceStatsController::class, 'getDashboard']);
// Route::get('rdomi/sts/service/live/{service_id}', [RdomiServiceStatsController::class, 'liveStatsStream']);

// Admin Routes - INCREMENTO MANUAL
Route::post('rdomi/sts/admin/manual-increment', [RdomiServiceStatsController::class, 'manualIncrement']);

// Location Routes - Countries and Cities
Route::post('location/country/all', [CountryController::class, 'index']);
Route::post('location/country/show/{id}', [CountryController::class, 'show']);
Route::post('location/cities/all', [CityController::class, 'index']);
Route::post('location/cities/by/country', [CityController::class, 'getByCountry']);
Route::post('location/city/show/{id}', [CityController::class, 'show']);

// Station Email Routes - Send emails using SendGrid
Route::post('station/send-radio-mail', [StationMailController::class, 'sendRadioMail']);
Route::post('station/send-hosting-mail', [StationMailController::class, 'sendHostingMail']);
Route::post('station/send-tv-mail', [StationMailController::class, 'sendTvMail']);
Route::post('station/preview-radio-mail', [StationMailController::class, 'previewRadioMail']);

// Email Testing Routes
Route::get('mail/test-config', [StationMailController::class, 'testEmailConfig']);
Route::post('mail/send-test', [StationMailController::class, 'sendTestEmail']);
Route::post('station/debug', [StationMailController::class, 'debugStation']);
Route::get('mail/logs', [StationMailController::class, 'getEmailLogs']);


// ============================================
// WHMCS INTEGRATION ROUTES
// ============================================
// Manual, on-demand integration with WHMCS
// No automatic syncing - all operations require explicit action

// WHMCS Clients Management
Route::prefix('whmcs/clients')->group(function () {
    // Push Laravel client to WHMCS (create or update)
    Route::post('push/{client_id}', [WHMCSClientController::class, 'pushToWHMCS']);
    
    // Update existing client in WHMCS
    Route::post('update/{client_id}', [WHMCSClientController::class, 'updateInWHMCS']);
    
    // Pull client from WHMCS to Laravel
    Route::post('pull/{whmcs_id}', [WHMCSClientController::class, 'pullFromWHMCS']);
    
    // List clients from WHMCS
    Route::post('list', [WHMCSClientController::class, 'listWHMCSClients']);
    
    // Get specific client from WHMCS
    Route::post('get/{whmcs_id}', [WHMCSClientController::class, 'getWHMCSClient']);
    
    // Check if Laravel client is synced with WHMCS
    Route::post('check/{client_id}', [WHMCSClientController::class, 'checkSync']);
    
    // Delete client from WHMCS (requires confirmation)
    Route::post('delete/{whmcs_id}', [WHMCSClientController::class, 'deleteFromWHMCS']);
});

// Manual Client Linking (Read-Only WHMCS Integration)
Route::prefix('clients')->group(function () {
    // Link Laravel client to WHMCS client manually
    Route::post('{client_id}/link-whmcs', [WHMCSClientController::class, 'linkToWHMCS']);
    
    // Get WHMCS info for linked client (read-only)
    Route::get('{client_id}/whmcs-info', [WHMCSClientController::class, 'getWHMCSInfo']);
    
    // Get WHMCS products for linked client
    Route::get('{client_id}/whmcs-products', [WHMCSClientController::class, 'getWHMCSProducts']);
    
    // Get WHMCS invoices for linked client
    Route::get('{client_id}/whmcs-invoices', [WHMCSClientController::class, 'getWHMCSInvoices']);
    
    // Get WHMCS domains for linked client
    Route::get('{client_id}/whmcs-domains', [WHMCSClientController::class, 'getWHMCSDomains']);
    
    // Generate SSO token for client (auto-login to WHMCS)
    Route::post('{client_id}/whmcs-sso', [WHMCSClientController::class, 'generateSsoToken']);
    
    // Unlink client from WHMCS (doesn't delete anything)
    Route::delete('{client_id}/unlink-whmcs', [WHMCSClientController::class, 'unlinkFromWHMCS']);
});

// Station WHMCS Integration (Link stations to products/domains)
Route::prefix('stations')->group(function () {
    // Get available products/domains for station's client
    Route::get('{station_id}/whmcs-available-products', [WHMCSStationController::class, 'getAvailableProducts']);
    Route::get('{station_id}/whmcs-available-domains', [WHMCSStationController::class, 'getAvailableDomains']);
    
    // Link station to product/domain
    Route::post('{station_id}/link-whmcs-product', [WHMCSStationController::class, 'linkToProduct']);
    Route::post('{station_id}/link-whmcs-domain', [WHMCSStationController::class, 'linkToDomain']);
    
    // Get linked product/domain info
    Route::get('{station_id}/whmcs-product', [WHMCSStationController::class, 'getProductInfo']);
    Route::get('{station_id}/whmcs-domain', [WHMCSStationController::class, 'getDomainInfo']);
    
    // Unlink station from product/domain
    Route::delete('{station_id}/unlink-whmcs-product', [WHMCSStationController::class, 'unlinkFromProduct']);
    Route::delete('{station_id}/unlink-whmcs-domain', [WHMCSStationController::class, 'unlinkFromDomain']);
});

// WHMCS Sync Management & Utilities
Route::prefix('whmcs/sync')->group(function () {
    // Test WHMCS API connection
    Route::get('test', [WHMCSSyncController::class, 'testConnection']);
    
    // Get sync logs (history of all operations)
    Route::post('logs', [WHMCSSyncController::class, 'getSyncLogs']);
    
    // Get sync mapping (what's connected to WHMCS)
    Route::post('map', [WHMCSSyncController::class, 'getSyncMap']);
    
    // Get sync statistics
    Route::get('stats', [WHMCSSyncController::class, 'getSyncStats']);
    
    // Unlink entity from WHMCS (break sync relationship)
    Route::post('unlink', [WHMCSSyncController::class, 'unlinkEntity']);
    
    // Get WHMCS configuration
    Route::get('config', [WHMCSSyncController::class, 'getConfig']);
    
    // Clear WHMCS cache
    Route::post('clear-cache', [WHMCSSyncController::class, 'clearCache']);
});


// Route::post('rccontrol/mails/index', 'MailController@index');
// Route::post('rccontrol/mails/store', 'MailController@store');
// Route::post('rccontrol/mails/show/{id}', 'MailController@show');
// Route::post('rccontrol/mails/update/{id}', 'MailController@update');
// Route::post('rccontrol/mails/destroy/{id}', 'MailController@destroy');

// Route::post('mailmessages/store', 'MailMessageController@store');
// Route::post('mailmessages/index/{mail_id}', 'MailMessageController@index');

// Route::post('mailfilters/index', 'MailFilterController@index');
// Route::post('mailfilters/store', 'MailFilterController@store');
// Route::post('mailfilters/show/{id}', 'MailFilterController@show');
// Route::post('mailfilters/update/{id}', 'MailFilterController@update');
// Route::post('mailfilters/destroy/{id}', 'MailFilterController@destroy');


// Route::post('mailfolders/index', 'MailFolderController@index');
// Route::post('mailfolders/store', 'MailFolderController@store');
// Route::post('mailfolders/show/{id}', 'MailFolderController@show');
// Route::post('mailfolders/update/{id}', 'MailFolderController@update');
// Route::post('mailfolders/destroy/{id}', 'MailFolderController@destroy');


// Route::post('maillabels/index', 'MailLabelController@index');
// Route::post('maillabels/store', 'MailLabelController@store');
// Route::post('maillabels/show/{id}', 'MailLabelController@show');
// Route::post('maillabels/update/{id}', 'MailLabelController@update');
// Route::post('maillabels/destroy/{id}', 'MailLabelController@destroy');

// Route::post('tags/index', 'TagController@index');
// Route::post('tags/store', 'TagController@store');
// Route::post('tags/show/{id}', 'TagController@show');
// Route::post('tags/update/{id}', 'TagController@update');
// Route::post('tags/destroy/{id}', 'TagController@destroy');
// Route::post('tags/sync', 'TagController@syncTags');
