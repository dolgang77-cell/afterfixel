<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\PartyController;
use App\Http\Controllers\Admin\CommunityPostController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ExposureController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\PushCampaignController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MdProfileController;
use App\Http\Controllers\Admin\MdMappingController;
use App\Http\Controllers\Admin\ProfileImageModerationController;
use App\Http\Controllers\Admin\AccessLogController;
use Illuminate\Support\Facades\Route;

// 관리자 인증
Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

// 관리자 보호 영역
Route::middleware('admin')->group(function () {
    // 대시보드
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // 클럽 관리
    Route::resource('clubs', ClubController::class)->names('admin.clubs')->except('show');
    Route::patch('/clubs/{club}/priority', [ClubController::class, 'updatePriority'])->name('admin.clubs.priority');

    // 파티 관리
    Route::resource('parties', PartyController::class)->names('admin.parties')->except('show');
    Route::post('/parties/bulk-status', [PartyController::class, 'bulkStatus'])->name('admin.parties.bulk-status');

    // 게시글 관리
    Route::get('/posts', [CommunityPostController::class, 'index'])->name('admin.posts.index');
    Route::get('/posts/create', [CommunityPostController::class, 'create'])->name('admin.posts.create');
    Route::post('/posts', [CommunityPostController::class, 'store'])->name('admin.posts.store');
    Route::get('/posts/{post}', [CommunityPostController::class, 'show'])->name('admin.posts.show');
    Route::get('/posts/{post}/edit', [CommunityPostController::class, 'edit'])->name('admin.posts.edit');
    Route::put('/posts/{post}', [CommunityPostController::class, 'update'])->name('admin.posts.update');
    Route::patch('/posts/{post}/toggle-hidden', [CommunityPostController::class, 'toggleHidden'])->name('admin.posts.toggle-hidden');
    Route::patch('/posts/{post}/reset-reports', [CommunityPostController::class, 'resetReports'])->name('admin.posts.reset-reports');
    Route::delete('/posts/{post}', [CommunityPostController::class, 'destroy'])->name('admin.posts.destroy');
    Route::post('/posts/bulk-hide', [CommunityPostController::class, 'bulkHide'])->name('admin.posts.bulk-hide');

    // 배너/노출 관리
    Route::resource('banners', BannerController::class)->names('admin.banners')->except('show');

    // 홈 노출 우선순위 관리
    Route::get('/exposure', [ExposureController::class, 'index'])->name('admin.exposure.index');
    Route::post('/exposure/club-order', [ExposureController::class, 'updateClubOrder'])->name('admin.exposure.club-order');
    Route::patch('/exposure/party-pin/{party}', [ExposureController::class, 'togglePartyPin'])->name('admin.exposure.party-pin');

    // 회원 관리
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.role');
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('admin.users.status');

    // MD 관리
    Route::resource('md', MdProfileController::class)->names('admin.md')->except('show');
    Route::get('/md/{md}', [MdProfileController::class, 'show'])->name('admin.md.show');

    // MD 매칭
    Route::get('/md-mappings', [MdMappingController::class, 'index'])->name('admin.md-mappings.index');
    Route::post('/md-mappings', [MdMappingController::class, 'store'])->name('admin.md-mappings.store');
    Route::delete('/md-mappings/{type}/{id}', [MdMappingController::class, 'destroy'])->name('admin.md-mappings.destroy');

    // 미디어 승인 관리
    Route::get('/media', [MediaController::class, 'index'])->name('admin.media.index');
    Route::get('/media/diagnostics', [MediaController::class, 'diagnostics'])->name('admin.media.diagnostics');
    Route::post('/media/diagnostics/bulk-regenerate-variants', [MediaController::class, 'bulkRegenerateVariants'])->name('admin.media.bulk-regenerate-variants');
    Route::post('/media/{media}/regenerate-variants', [MediaController::class, 'regenerateVariants'])->name('admin.media.regenerate-variants');
    Route::patch('/media/{media}/approve', [MediaController::class, 'approve'])->name('admin.media.approve');
    Route::patch('/media/{media}/reject', [MediaController::class, 'reject'])->name('admin.media.reject');
    Route::patch('/media/{media}/hide', [MediaController::class, 'hide'])->name('admin.media.hide');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('admin.media.destroy');
    Route::post('/media/bulk-approve', [MediaController::class, 'bulkApprove'])->name('admin.media.bulk-approve');

    // 프로필 이미지 승인 관리
    Route::get('/profile-images', [ProfileImageModerationController::class, 'index'])->name('admin.profile-images.index');
    Route::get('/pending-images', [ProfileImageModerationController::class, 'pending'])->name('admin.profile-images.pending-api');
    Route::post('/approve', [ProfileImageModerationController::class, 'approve'])->name('admin.profile-images.approve-api');
    Route::post('/reject', [ProfileImageModerationController::class, 'reject'])->name('admin.profile-images.reject-api');

    // 후기 관리
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('admin.reviews.index');
    Route::patch('/reviews/{review}/toggle-hidden', [AdminReviewController::class, 'toggleHidden'])->name('admin.reviews.toggle-hidden');

    // 문의 관리
    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('admin.inquiries.index');
    Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('admin.inquiries.show');
    Route::patch('/inquiries/{inquiry}/status', [AdminInquiryController::class, 'updateStatus'])->name('admin.inquiries.status');
    Route::patch('/inquiries/{inquiry}/assign-md', [AdminInquiryController::class, 'assignMd'])->name('admin.inquiries.assign-md');
    Route::post('/inquiries/{inquiry}/reply', [AdminInquiryController::class, 'reply'])->name('admin.inquiries.reply');

    // 운영정책/신고/금칙어/제재
    Route::get('/moderation/reports', [ModerationController::class, 'reports'])->name('admin.moderation.reports');
    Route::patch('/moderation/reports/{report}', [ModerationController::class, 'reviewReport'])->name('admin.moderation.reports.review');
    Route::get('/moderation/message-reports', [ModerationController::class, 'messageReports'])->name('admin.moderation.message-reports');
    Route::patch('/moderation/message-reports/{messageReport}', [ModerationController::class, 'reviewMessageReport'])->name('admin.moderation.message-reports.review');
    Route::get('/moderation/banned-users', [ModerationController::class, 'bannedUsers'])->name('admin.moderation.banned-users');
    Route::post('/moderation/actions', [ModerationController::class, 'createAction'])->name('admin.moderation.actions.store');
    Route::patch('/moderation/actions/{action}', [ModerationController::class, 'deactivateAction'])->name('admin.moderation.actions.deactivate');
    Route::get('/moderation/forbidden-words', [ModerationController::class, 'forbiddenWords'])->name('admin.moderation.forbidden-words');
    Route::post('/moderation/forbidden-words', [ModerationController::class, 'storeForbiddenWord'])->name('admin.moderation.forbidden-words.store');
    Route::delete('/moderation/forbidden-words/{word}', [ModerationController::class, 'destroyForbiddenWord'])->name('admin.moderation.forbidden-words.destroy');
    Route::patch('/moderation/forbidden-words/{word}/toggle', [ModerationController::class, 'toggleForbiddenWord'])->name('admin.moderation.forbidden-words.toggle');
    Route::get('/moderation/policies', [ModerationController::class, 'policies'])->name('admin.moderation.policies');
    Route::patch('/moderation/policies', [ModerationController::class, 'updatePolicies'])->name('admin.moderation.policies.update');

    // 푸쉬 캠페인
    Route::get('/push', [PushCampaignController::class, 'index'])->name('admin.push.index');
    Route::get('/push/create', [PushCampaignController::class, 'create'])->name('admin.push.create');
    Route::post('/push', [PushCampaignController::class, 'store'])->name('admin.push.store');
    Route::get('/push/{campaign}', [PushCampaignController::class, 'show'])->name('admin.push.show');
    Route::get('/push/{campaign}/edit', [PushCampaignController::class, 'edit'])->name('admin.push.edit');
    Route::put('/push/{campaign}', [PushCampaignController::class, 'update'])->name('admin.push.update');
    Route::patch('/push/{campaign}/cancel', [PushCampaignController::class, 'cancel'])->name('admin.push.cancel');
    Route::post('/push/{campaign}/send-now', [PushCampaignController::class, 'sendNow'])->name('admin.push.send-now');

    // 접속 로그
    Route::get('/access-logs', [AccessLogController::class, 'index'])->name('admin.access-logs.index');
    Route::get('/access-logs/{accessLog}', [AccessLogController::class, 'show'])->name('admin.access-logs.show');

    // 이미지 업로드
    Route::post('/upload-image', [ImageUploadController::class, 'upload'])->name('admin.upload-image');

    // 로그/통계
    Route::get('/logs', [LogController::class, 'index'])->name('admin.logs.index');
    Route::get('/logs/clicks', [LogController::class, 'clicks'])->name('admin.logs.clicks');
    Route::get('/logs/recommendations', [LogController::class, 'recommendations'])->name('admin.logs.recommendations');
    Route::get('/logs/notifications', [LogController::class, 'notifications'])->name('admin.logs.notifications');
});
