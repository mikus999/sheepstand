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

    Route::get('/user', 'Auth\UserController@current');
    Route::get('/users', 'Auth\UserController@getAllUsers'); // GLOBAL Roles Only
    Route::get('/users/{role}', 'Auth\UserController@getUsersByRole'); // GLOBAL Roles Only

    // SECURITY routes
    Route::get('roles', 'SecurityController@getRolesWithPermissions');
    Route::post('user/roles/get', 'SecurityController@getRoles');
    Route::post('user/roles/set', 'SecurityController@setRoles');
    //Route::post('user/permissions', 'Auth\UserController@setPermissions');

    // ACCOUNT routes
    Route::patch('account/profile', 'Settings\ProfileController@update');
    Route::patch('account/password', 'Settings\PasswordController@update');
    Route::get('account/availability', 'UserAvailabilityController@getAvailability');
    Route::post('account/availability', 'UserAvailabilityController@setAvailability');
    Route::post('account/availability/default', 'UserAvailabilityController@setDefaultAvailability');

    // TEAM routes
    Route::post('teams/jointeam', 'TeamController@addUserToTeam');
    Route::post('teams/leaveteam', 'TeamController@removeUserFromTeam');
    Route::get('teams/resetcode/{id}', 'TeamController@changeTeamCode');
    Route::get('teams/findteam/{code}', 'TeamController@findTeamByCode');
    Route::get('teams/users/{id}', 'TeamController@getTeamUsers');
    Route::post('teams/settings/update', 'TeamController@updateSetting');
    Route::post('teams/default/update', 'TeamController@setDefault');
    Route::get('teams/{id}/notificationsettings', 'NotificationController@notificationSettings');
    Route::post('teams/{id}/notificationsettings', 'NotificationController@updateTelegram');
    Route::post('teams/{id}/grouplink', 'NotificationController@updateTelegramGroupLink');
    Route::get('teams/{id}/grouplink', 'NotificationController@getTelegramGroupLink');

    // SHIFT routes
    Route::post('schedules/joinshift', 'ShiftController@addUserToShift');
    Route::post('schedules/leaveshift', 'ShiftController@removeUserFromShift');
    Route::post('schedules/shiftuserstatus', 'ShiftController@changeUserShiftStatus');
    Route::get('schedules/shiftusers/{id}', 'ShiftController@getShiftUsers');
    Route::get('teams/{id}/stats', 'ShiftController@showStatistics');
    Route::get('teams/{id}/trades', 'ShiftController@getTradeRequests');
    Route::post('teams/{id}/trades', 'ShiftController@makeTrade');
    Route::get('user/shifts', 'ShiftController@userAllShifts');

    // SCHEDULE routes
    Route::get('schedules/{teamid}', ['as' => 'schedules.index', 'uses' => 'ScheduleController@index']);
    Route::get('schedules/show/{id}', ['as' => 'schedules.show', 'uses' => 'ScheduleController@show']);
    Route::get('schedules/{id}/counts/{date}/{dayOfWeek}', 'ScheduleController@getShiftCounts');
    Route::post('schedules/{id}/status', 'ScheduleController@updateStatus');
    Route::get('schedules/templates/{teamid}', 'ScheduleController@getTemplates');
    Route::post('schedules/templates', 'ScheduleController@newTemplate');
    Route::post('schedules/templates/{id}/copy', 'ScheduleController@makeFromTemplate');

    // LOCATION routes
    Route::post('teams/{teamid}/locations/{locid}/makedefault', 'LocationController@setDefault');

    // TRANSLATION routes
    Route::post('translation/update', 'TranslationController@updateString');
    Route::get('translation/strings/{lang}', 'TranslationController@getStrings');
    Route::get('translation/permissions', 'TranslationController@getUserLanguages');
    Route::post('translation/permissions', 'TranslationController@setUserLanguages');
    Route::post('translation/languages/edit', 'TranslationController@setSiteLanguage');


    // MESSAGE routes
    Route::get('messages/{id}/markread', 'MessageController@markAsRead');
    Route::get('messages/count', 'MessageController@getMessageCount');


    // TASKS routs
    Route::get('tasks/scheduled', 'TaskController@scheduledTasks');


    // API routes (must be listed after all other routes are declared)
    Route::apiResource('teams', 'TeamController');
    Route::apiResource('schedules', 'ScheduleController', ['except' => ['index','show']]);
    Route::apiResource('schedules.shifts', 'ShiftController');
    Route::apiResource('teams.locations', 'LocationController');
    Route::apiResource('messages', 'MessageController');

});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');

    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::post('email/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'Auth\VerificationController@resend');

    Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
    Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');
    Route::post('oauth/{driver}/mobile', 'Auth\OAuthController@getTokenFromMobile');

});




// ROUTES THAT ARE ACCESSIBLE WITH AND WITHOUT AUTHENTICATION
Route::get('translation/languages/{subset}', 'TranslationController@getLanguages');
