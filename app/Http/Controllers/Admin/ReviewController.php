<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with('user')
            ->when($request->target_type, fn($q, $v) => $q->where('target_type', $v))
            ->when($request->filter === 'hidden', fn($q) => $q->where('is_hidden', true))
            ->when($request->filter === 'reported', fn($q) => $q->where('report_count', '>', 0)->where('is_hidden', false))
            ->when($request->search, fn($q, $v) => $q->where('content', 'like', "%{$v}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggleHidden(Review $review)
    {
        $review->update(['is_hidden' => !$review->is_hidden]);
        $action = $review->is_hidden ? 'hide' : 'unhide';
        AdminLog::record($action, 'review', $review->id);
        return back()->with('success', $review->is_hidden ? '후기가 숨김 처리되었습니다.' : '후기 숨김이 해제되었습니다.');
    }
}
