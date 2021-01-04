<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');

    // SECURITY routes
    Route::get('/user', 'Auth\UserController@current');
    Route::get('/users', 'Auth\UserController@getAllUsers'); // GLOBAL Roles Only
    Route::get('/users/{role}', 'Auth\UserController@getUsersByRole'); // GLOBAL Roles Only
    Route::get('user/{id}/roles', 'SecurityController@getRoles');
    Route::post('user/{id}/roles', 'SecurityController@setRoles');
    Route::get('roles', 'SecurityController@getRolesWithPermissions');
    //Route::post('user/permissions', 'Auth\UserController@setPermissions');

    // ACCOUNT routes
    Route::patch('account/profile', 'Settings\ProfileController@update');
    Route::patch('account/password', 'Settings\PasswordController@update');
    Route::get('account/availability', 'UserAvailabilityController@getAvailability');
    Route::post('account/availability', 'UserAvailabilityController@setAvailability');
    Route::post('account/availability/default', 'UserAvailabilityController@setDefaultAvailability');
    Route::get('account/vacation', 'UserAvailabilityController@getVacation');
    Route::post('account/vacation', 'UserAvailabilityController@setVacation');
    Route::delete('account/vacation/{id}', 'UserAvailabilityController@deleteVacation');
    Route::post('account/fts', 'Settings\ProfileController@updateFTSStatus');
    Route::post('account/marriage', 'Settings\ProfileController@updateMarriageMate');
    Route::post('account/driver', 'Settings\ProfileController@updateDriverStatus');

    // TEAM routes
    Route::post('teams/jointeam', 'TeamController@addUserToTeam');
    Route::post('teams/leaveteam', 'TeamController@removeUserFromTeam');
    Route::get('teams/{id}/resetcode', 'TeamController@changeTeamCode');
    Route::get('teams/{code}/findteam', 'TeamController@findTeamByCode');
    Route::get('teams/{id}/users/', 'TeamController@getTeamUsers');
    Route::post('teams/settings/update', 'TeamController@updateSetting');
    Route::post('teams/default/update', 'TeamController@setDefault');
    Route::get('teams/{id}/notificationsettings', 'NotificationController@notificationSettings');
    Route::post('teams/{id}/notificationsettings', 'NotificationController@updateTelegram');
    Route::post('teams/{id}/grouplink', 'NotificationController@updateTelegramGroupLink');
    Route::get('teams/{id}/grouplink', 'NotificationController@getTelegramGroupLink');
    Route::get('teams/{id}/availability', 'UserAvailabilityController@getAllAvailability');
    Route::get('teams/{id}/stats', 'ShiftController@showStatistics');

    // SHIFT routes
    Route::post('schedules/joinshift', 'ShiftController@addUserToShift');
    Route::post('schedules/leaveshift', 'ShiftController@removeUserFromShift');
    Route::post('schedules/shiftuserstatus', 'ShiftController@changeUserShiftStatus');
    Route::get('schedules/{id}/approveall/{status}', 'ShiftController@approveAllRequests');
    Route::get('schedules/shifts/{id}/users', 'ShiftController@getShiftUsers');
    Route::get('schedules/trades', 'ShiftController@getTradeRequests'); // TODO OpenAPI
    Route::post('schedules/trades', 'ShiftController@makeTrade'); // TODO OpenAPI
    Route::get('user/shifts', 'ShiftController@userAllShifts'); // TODO OpenAPI
    Route::post('user/shifts', 'ShiftController@userTeamShifts'); // TODO OpenAPI


    // SCHEDULE routes
    Route::get('schedules/{teamid}', ['as' => 'schedules.index', 'uses' => 'ScheduleController@index']); // TODO OpenAPI
    Route::get('schedules/show/{id}', ['as' => 'schedules.show', 'uses' => 'ScheduleController@show']); // TODO OpenAPI
    Route::get('schedules/{id}/counts/{date}/{dayOfWeek}', 'ScheduleController@getShiftCounts'); // TODO OpenAPI
    Route::post('schedules/{id}/status', 'ScheduleController@updateStatus'); // TODO OpenAPI
    Route::get('schedules/templates/{teamid}', 'ScheduleController@getTemplates'); // TODO OpenAPI
    Route::post('schedules/templates', 'ScheduleController@newTemplate'); // TODO OpenAPI
    Route::post('schedules/templates/{id}/copy', 'ScheduleController@makeFromTemplate'); // TODO OpenAPI
    Route::post('schedules/{id}/templates/make', 'ScheduleController@saveAsTemplate'); // TODO OpenAPI

    // LOCATION routes
    Route::post('teams/{teamid}/locations/{locid}/makedefault', 'LocationController@setDefault'); // TODO OpenAPI

    // TRANSLATION routes
    Route::post('translation/update', 'TranslationController@updateString'); // TODO OpenAPI
    Route::get('translation/strings/{lang}', 'TranslationController@getStrings'); // TODO OpenAPI
    Route::get('translation/permissions', 'TranslationController@getUserLanguages'); // TODO OpenAPI
    Route::post('translation/permissions', 'TranslationController@setUserLanguages'); // TODO OpenAPI
    Route::post('translation/languages/edit', 'TranslationController@setSiteLanguage'); // TODO OpenAPI


    // MESSAGE routes
    Route::get('messages/{id}/markread', 'MessageController@markAsRead'); // TODO OpenAPI
    Route::get('messages/{id}/markunread', 'MessageController@markAsUnread'); // TODO OpenAPI
    Route::get('messages/{id}/hide', 'MessageController@hideMessage'); // TODO OpenAPI
    Route::get('messages/count', 'MessageController@getMessageCount'); // TODO OpenAPI
    Route::get('messages/banners', 'MessageController@getActiveBanners'); // TODO OpenAPI


    // TASKS routs
    Route::get('tasks/scheduled', 'TaskController@scheduledTasks'); // TODO OpenAPI


    // API routes (must be listed after all other routes are declared)
    Route::apiResource('teams', 'TeamController'); // TODO OpenAPI
    Route::apiResource('schedules', 'ScheduleController', ['except' => ['index','show']]); // TODO OpenAPI
    Route::apiResource('schedules.shifts', 'ShiftController'); // TODO OpenAPI
    Route::apiResource('teams.locations', 'LocationController'); // TODO OpenAPI
    Route::apiResource('messages', 'MessageController'); // TODO OpenAPI

});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register'); // TODO OpenAPI

    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail'); // TODO OpenAPI
    Route::post('password/reset', 'Auth\ResetPasswordController@reset'); // TODO OpenAPI

    Route::post('email/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify'); // TODO OpenAPI
    Route::post('email/resend', 'Auth\VerificationController@resend'); // TODO OpenAPI

    Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider'); // TODO OpenAPI
    Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback'); // TODO OpenAPI
    Route::post('oauth/{driver}/mobile', 'Auth\OAuthController@getTokenFromMobile'); // TODO OpenAPI

});




// ROUTES THAT ARE ACCESSIBLE WITH AND WITHOUT AUTHENTICATION
Route::get('translation/languages/{subset}', 'TranslationController@getLanguages');
