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

    // SECURITY routes
    Route::get('roles', 'SecurityController@getRolesWithPermissions');
    Route::post('user/roles/get', 'SecurityController@getRoles');
    Route::post('user/roles/set', 'SecurityController@setRoles');
    //Route::post('user/permissions', 'Auth\UserController@setPermissions');

    // ACCOUNT routes
    Route::patch('account/profile', 'Settings\ProfileController@update');
    Route::patch('account/password', 'Settings\PasswordController@update');

    // TEAM routes
    Route::post('teams/jointeam', 'TeamController@addUserToTeam');
    Route::post('teams/leaveteam', 'TeamController@removeUserFromTeam');
    Route::get('teams/resetcode/{id}', 'TeamController@changeTeamCode');
    Route::get('teams/findteam/{code}', 'TeamController@findTeamByCode');
    Route::get('teams/users/{id}', 'TeamController@getTeamUsers');
    Route::post('teams/settings/update', 'TeamController@updateSetting');
    Route::post('teams/default/update', 'TeamController@setDefault');

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

    // LOCATION routes
    Route::post('teams/{teamid}/locations/{locid}/makedefault', 'LocationController@setDefault');

    // TRANSLATION routes
    Route::post('translation/update', 'TranslationController@updateString');
    Route::get('translation/strings/{lang}', 'TranslationController@getStrings');
    Route::get('translation/permissions', 'TranslationController@getLanguages');

    // API Resource Routes
    Route::apiResource('teams', 'TeamController');
    Route::apiResource('teams.locations', 'LocationController');
    Route::apiResource('schedules', 'ScheduleController', ['except' => ['index','show']]);
    Route::apiResource('schedules.shifts', 'ShiftController');

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
});
