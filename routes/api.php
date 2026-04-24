<?php

use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Api\ClubApiController;
use App\Http\Controllers\Api\ConversationApiController;
use App\Http\Controllers\Api\MdApiController;
use App\Http\Controllers\Api\NearbyUserApiController;
use App\Http\Controllers\Api\PartyApiController;
use App\Http\Controllers\Api\TourApiController;
use App\Http\Controllers\Api\CommunityApiController;
use App\Http\Controllers\MediaUploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (prefix: /api, throttle: 60/min)
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/home', [HomeApiController::class, 'index']);

    Route::get('/clubs', [ClubApiController::class, 'index']);
    Route::get('/clubs/{club}', [ClubApiController::class, 'show']);

    Route::get('/parties', [PartyApiController::class, 'index']);
    Route::get('/parties/{party}', [PartyApiController::class, 'show']);

    Route::get('/community/posts', [CommunityApiController::class, 'index']);
});

// 더 엄격한 제한: 쓰기/연산 API
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/tour/recommend', [TourApiController::class, 'recommend']);
    Route::post('/community/posts', [CommunityApiController::class, 'store']);
});

Route::middleware(['web', 'md', 'throttle:30,1'])->group(function () {
    Route::get('/md/me', [MdApiController::class, 'me']);
    Route::patch('/md/me', [MdApiController::class, 'updateMe']);
    Route::get('/md/me/clubs', [MdApiController::class, 'clubs']);
    Route::get('/md/me/parties', [MdApiController::class, 'parties']);
    Route::get('/md/me/inquiries', [MdApiController::class, 'inquiries']);
    Route::post('/md/me/inquiries/{inquiry}/reply', [MdApiController::class, 'replyInquiry']);
    Route::patch('/md/me/inquiries/{inquiry}/status', [MdApiController::class, 'updateInquiryStatus']);
    Route::get('/md/me/reviews', [MdApiController::class, 'reviews']);

    Route::patch('/clubs/{club}/content', [MdApiController::class, 'updateClubContent']);
    Route::patch('/parties/{party}/content', [MdApiController::class, 'updatePartyContent']);

    Route::post('/upload/md-images', [MediaUploadController::class, 'uploadMdImage']);
    Route::delete('/media/{media}', [MediaUploadController::class, 'destroy']);
    Route::patch('/media/{media}/order', [MediaUploadController::class, 'updateOrder']);
});

if (config('nearby-messaging.enabled')) {
    Route::middleware(['web', 'throttle:30,1'])->group(function () {
        Route::get('/nearby-users/status', [NearbyUserApiController::class, 'status']);
        Route::get('/nearby-users', [NearbyUserApiController::class, 'index']);
        Route::patch('/nearby-users/settings', [NearbyUserApiController::class, 'updateSettings']);
        Route::post('/nearby-users/location', [NearbyUserApiController::class, 'updateLocation']);
        Route::post('/nearby-users/checkins', [NearbyUserApiController::class, 'checkin']);

        Route::get('/conversations', [ConversationApiController::class, 'index']);
        Route::post('/conversations', [ConversationApiController::class, 'store'])->middleware('throttle:10,1');
        Route::get('/conversations/{conversation}', [ConversationApiController::class, 'show']);
        Route::get('/conversations/{conversation}/messages', [ConversationApiController::class, 'messages']);
        Route::post('/conversations/{conversation}/messages', [ConversationApiController::class, 'send'])->middleware('throttle:20,1');
        Route::post('/conversations/{conversation}/read', [ConversationApiController::class, 'read']);
        Route::post('/conversations/{conversation}/leave', [ConversationApiController::class, 'leave']);

        Route::post('/messages/{message}/report', [ConversationApiController::class, 'report'])->middleware('throttle:10,1');
        Route::post('/users/{user}/block', [ConversationApiController::class, 'block'])->middleware('throttle:10,1');
        Route::delete('/users/{user}/block', [ConversationApiController::class, 'unblock'])->middleware('throttle:10,1');
    });
}
