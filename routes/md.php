<?php

use App\Http\Controllers\MdDashboardController;
use App\Http\Controllers\MediaUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware('md')->group(function () {
    Route::get('/', [MdDashboardController::class, 'index'])->name('md-dashboard.index');

    // 프로필
    Route::get('/profile', [MdDashboardController::class, 'profile'])->name('md-dashboard.profile');
    Route::patch('/profile', [MdDashboardController::class, 'updateProfile'])->name('md-dashboard.profile.update');

    // 담당 클럽
    Route::get('/clubs', [MdDashboardController::class, 'clubs'])->name('md-dashboard.clubs');
    Route::get('/clubs/{club}/content', [MdDashboardController::class, 'editClubContent'])->name('md-dashboard.clubs.content');
    Route::patch('/clubs/{club}/content', [MdDashboardController::class, 'updateClubContent'])->name('md-dashboard.clubs.content.update');

    // 담당 파티
    Route::get('/parties', [MdDashboardController::class, 'parties'])->name('md-dashboard.parties');
    Route::get('/parties/{party}/content', [MdDashboardController::class, 'editPartyContent'])->name('md-dashboard.parties.content');
    Route::patch('/parties/{party}/content', [MdDashboardController::class, 'updatePartyContent'])->name('md-dashboard.parties.content.update');

    // 후기
    Route::get('/reviews', [MdDashboardController::class, 'reviews'])->name('md-dashboard.reviews');

    // 문의
    Route::get('/inquiries', [MdDashboardController::class, 'inquiries'])->name('md-dashboard.inquiries');
    Route::get('/inquiries/{inquiry}', [MdDashboardController::class, 'showInquiry'])->name('md-dashboard.inquiries.show');
    Route::post('/inquiries/{inquiry}/reply', [MdDashboardController::class, 'replyInquiry'])->name('md-dashboard.inquiries.reply');
    Route::patch('/inquiries/{inquiry}/status', [MdDashboardController::class, 'updateInquiryStatus'])->name('md-dashboard.inquiries.status');

    // 미디어
    Route::get('/media', [MdDashboardController::class, 'media'])->name('md-dashboard.media');

    // 업로드
    Route::post('/upload', [MediaUploadController::class, 'upload'])->name('md-dashboard.upload');
    Route::delete('/media/{media}', [MediaUploadController::class, 'destroy'])->name('md-dashboard.media.destroy');
    Route::patch('/media/{media}/order', [MediaUploadController::class, 'updateOrder'])->name('md-dashboard.media.order');
});
