<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MdController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\SavedFilterController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\MediaUploadController;
use App\Http\Controllers\MyPageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TonightController;
use App\Http\Controllers\NearbyController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\UxEventController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/auth/check-nickname', [AuthController::class, 'checkNickname'])->name('auth.check-nickname');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Offline (PWA fallback)
Route::get('/offline', fn() => view('offline'))->name('offline');

// Legal
Route::get('/terms', fn() => view('legal.terms'))->name('terms');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');

// Public docs
Route::get('/docs/{path?}', [DocsController::class, 'show'])
    ->where('path', '.*')
    ->name('docs.show');

// Reports
Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:10,1')->name('reports.store');

// Clubs
Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
Route::get('/clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');

// Parties
Route::get('/parties', [PartyController::class, 'index'])->name('parties.index');
Route::get('/parties/{party}', [PartyController::class, 'show'])->name('parties.show');

// Tour
Route::get('/tour', [TourController::class, 'index'])->name('tour.index');
Route::post('/tour/recommend', [TourController::class, 'recommend'])->name('tour.recommend');

// Tonight
Route::get('/tonight', [TonightController::class, 'index'])->name('tonight.index');
Route::match(['get', 'post'], '/tonight/quick', [TonightController::class, 'quickRecommend'])->name('tonight.quick');
Route::get('/tonight/status', [TonightController::class, 'statusSummary'])->name('tonight.status');

// Nearby (Near Me)
Route::get('/nearby', [NearbyController::class, 'index'])->name('nearby.index');
Route::match(['get', 'post'], '/nearby/recommend', [NearbyController::class, 'recommend'])->name('nearby.recommend');
Route::get('/nearby/summary', [NearbyController::class, 'summary'])->name('nearby.summary');
Route::post('/me/location', [NearbyController::class, 'saveLocation'])->name('me.location');

// MD
Route::get('/md/{md}', [MdController::class, 'show'])->name('md.show');

// Community
Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
Route::get('/community/create', [CommunityController::class, 'create'])->name('community.create');
Route::post('/community', [CommunityController::class, 'store'])->middleware('throttle:5,1')->name('community.store');
Route::post('/community/{post}/report', [CommunityController::class, 'report'])->middleware('throttle:10,1')->name('community.report');

// Reviews
Route::post('/reviews/{type}/{id}', [ReviewController::class, 'store'])->middleware('throttle:10,1')->name('reviews.store');
Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

// Inquiries
Route::post('/inquiries/{type}/{id}', [InquiryController::class, 'store'])->middleware('throttle:5,1')->name('inquiries.store');

// Media Upload (authenticated)
Route::post('/media/upload', [MediaUploadController::class, 'upload'])->name('media.upload');
Route::delete('/media/{media}', [MediaUploadController::class, 'destroy'])->name('media.destroy');
Route::post('/upload/profile-image', [ProfileImageController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('profile-image.store');
Route::get('/profile-image/{user}', [ProfileImageController::class, 'show'])->name('profile-image.show');
Route::get('/profile-image-file/{profileImage}/{variant?}', [ProfileImageController::class, 'file'])
    ->whereIn('variant', ['image', 'thumb', 'original'])
    ->name('profile-image.file');

// Favorites (generic)
Route::post('/favorites/{type}/{id}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

// Compare
Route::get('/compare/{type}', [CompareController::class, 'index'])
    ->whereIn('type', ['club', 'party'])
    ->name('compare.index');
Route::post('/compare/{type}/clear', [CompareController::class, 'clear'])
    ->whereIn('type', ['club', 'party'])
    ->name('compare.clear');
Route::post('/compare/{type}/{id}', [CompareController::class, 'toggle'])
    ->whereIn('type', ['club', 'party'])
    ->whereNumber('id')
    ->name('compare.toggle');

// Saved Filters
Route::post('/saved-filters/{type}', [SavedFilterController::class, 'store'])
    ->whereIn('type', ['club', 'party'])
    ->name('saved-filters.store');
Route::delete('/saved-filters/{savedFilter}', [SavedFilterController::class, 'destroy'])
    ->name('saved-filters.destroy');

// My Page
Route::get('/my', [MyPageController::class, 'index'])->name('my.index');
Route::get('/my/recent', [MyPageController::class, 'recentViews'])->name('my.recent');
Route::get('/my/preferences', [MyPageController::class, 'preferences'])->name('my.preferences');
Route::post('/my/preferences', [MyPageController::class, 'updatePreferences'])->name('my.preferences.update');
Route::get('/my/inquiries', [InquiryController::class, 'myInquiries'])->name('my.inquiries');
Route::get('/my/inquiries/{inquiry}', [InquiryController::class, 'showMyInquiry'])->name('my.inquiries.show');
Route::post('/my/inquiries/{inquiry}/message', [InquiryController::class, 'addMessage'])->name('my.inquiries.message');
Route::post('/my/inquiries/{inquiry}/reminder', [InquiryController::class, 'sendReminder'])->middleware('throttle:3,60')->name('my.inquiries.reminder');

// Notifications
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

// UX Event Tracking
Route::post('/events/ux', [UxEventController::class, 'store'])
    ->middleware('throttle:120,1')
    ->name('ux-events.store');

// Device Token & Push Tracking
Route::post('/device-tokens', function (\Illuminate\Http\Request $request) {
    if (!auth()->check()) return response()->json(['error' => 'Unauthorized'], 401);
    $request->validate(['platform' => 'required|in:android,ios,web', 'token' => 'required|string']);
    \App\Models\DeviceToken::register(auth()->id(), $request->platform, $request->token);
    return response()->json(['success' => true]);
})->name('device-tokens.store');

Route::post('/push/track-click', function (\Illuminate\Http\Request $request) {
    $request->validate(['campaign_id' => 'required|integer']);
    \App\Models\PushDeliveryLog::where('campaign_id', $request->campaign_id)
        ->where('user_id', auth()->id())
        ->whereNull('clicked_at')
        ->update(['clicked_at' => now()]);
    \App\Models\PushCampaign::where('id', $request->campaign_id)->increment('clicked_count');
    return response()->json(['success' => true]);
})->name('push.track-click');

Route::post('/push/track-inflow', function (\Illuminate\Http\Request $request) {
    $request->validate(['campaign_id' => 'required|integer']);
    \App\Models\PushInflowLog::create([
        'campaign_id' => $request->campaign_id,
        'user_id'     => auth()->id(),
        'path'        => $request->input('path'),
    ]);
    \App\Models\PushCampaign::where('id', $request->campaign_id)->increment('inflow_count');
    return response()->json(['success' => true]);
})->name('push.track-inflow');

// Notification Settings
Route::get('/settings/notifications', [NotificationSettingController::class, 'edit'])->name('notification-settings.edit');
Route::post('/settings/notifications', [NotificationSettingController::class, 'update'])->name('notification-settings.update');
