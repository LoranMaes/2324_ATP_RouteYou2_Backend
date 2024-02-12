<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\RouteController;
use Illuminate\Http\Request;
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

Route::controller(AuthenticationController::class)->group(function () {
    Route::post('/login', 'login')->middleware('is.not.authenticated');
    Route::post('/register', 'register')->middleware('is.not.authenticated');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
    Route::get('/user', 'getUser')->middleware('auth:sanctum');
    Route::delete('/user', 'deleteUser')->middleware('auth:sanctum');
});

Route::controller(OrganisationController::class)->group(function () {
    Route::get('/organisations', 'getOrganisations');
    Route::get('/organisations/{organisation}', 'getOrganisation');
    Route::post('/organisations', 'postOrganisation')->middleware(['auth:sanctum', 'role:USER']);
    Route::post('/organisations/{organisation}/follow', 'followOrganisation')->middleware(['auth:sanctum', 'role:USER', 'can:follow-organisation,organisation']);
    Route::delete('/organisations/{organisation}/follow', 'unfollowOrganisation')->middleware(['auth:sanctum', 'role:USER', 'can:unfollow-organisation,organisation']);
    Route::delete('/organisations/{organisation}', 'deleteOrganisation')->middleware(['auth:sanctum', 'role:ORGANISER', 'can:delete-organisation,organisation']);
});

Route::controller(EventController::class)->group(function () {
    Route::get('/user/events', 'getUserEvents')->middleware('auth:sanctum');
    Route::get('/events', 'getEvents');
    Route::get('/events/{event}', 'getEvent');
    Route::get('/events/{event}/achievements/{qrcode}', 'completeAchievement')->whereUuid('qrcode')->middleware(['auth:sanctum', 'role:USER', 'can:complete-achievement,event']);
    Route::get('/events/{event}/participate/{qrcode}', 'setParticipantPresent')->whereUuid('qrcode')->middleware(['auth:sanctum', 'role:ORGANISER', 'can:set-participant-present,event']);
    Route::post('/events', 'postEvent')->middleware(['auth:sanctum', 'role:ORGANISER']);
    Route::post('/events/{event}/participate', 'participate')->middleware(['auth:sanctum', 'role:USER', 'can:participate,event']);
    Route::put('/events/{event}/participate', 'updateParticipation')->middleware(['auth:sanctum', 'role:USER', 'can:update-participation,event']);
    Route::put('/events/{event}', 'updateEvent')->middleware(['auth:sanctum', 'role:ORGANISER', 'can:update-event,event']);
    Route::delete('/events/{event}', 'deleteEvent')->middleware(['auth:sanctum', 'role:ORGANISER', 'can:delete-event,event']);
});

Route::controller(RouteController::class)->group(function () {
    Route::post('/routes/{route}/checkpoints', 'postCheckpoint')->middleware(['auth:sanctum', 'role:ORGANISER', 'can:post-checkpoint,route']);
    Route::delete('/routes/{route}/checkpoints/{checkpoint}', 'deleteCheckpoint')->middleware(['auth:sanctum', 'role:ORGANISER', 'can:delete-checkpoint,checkpoint,route']);
});

Route::controller(MailController::class)->group(function () {
    Route::post('/events/{event}/invite', 'inviteUsers')->middleware(['auth:sanctum', 'role:ORGANISER', 'can:invite-users,event']);
});
