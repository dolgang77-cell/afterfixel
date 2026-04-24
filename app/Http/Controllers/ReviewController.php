<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, string $type, int $id)
    {
        if (!in_array($type, ['club', 'party'])) abort(404);
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '로그인 후 후기를 작성할 수 있습니다.',
                ], 401);
            }

            return redirect()->route('login')->with('error', '로그인 후 후기를 작성할 수 있습니다.');
        }

        if (!auth()->user()->canWrite()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '글쓰기가 제한된 상태입니다.',
                ], 403);
            }

            return back()->with('error', '글쓰기가 제한된 상태입니다.');
        }

        $data = $request->validate([
            'content' => 'required|string|max:2000',
            'rating'  => 'required|integer|min:1|max:5',
            'tags'    => 'nullable|array',
            'tags.*'  => 'string|max:20',
        ]);

        // 금칙어 체크
        $filterResult = \App\Services\ForbiddenWordFilter::check($data['content']);
        if (!$filterResult['passed'] && $filterResult['action'] === 'block') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '부적절한 표현이 포함되어 등록할 수 없습니다.',
                ], 422);
            }

            return back()->with('error', '부적절한 표현이 포함되어 등록할 수 없습니다.')->withInput();
        }

        $review = Review::create([
            'user_id'     => auth()->id(),
            'target_type' => $type,
            'target_id'   => $id,
            'content'     => $data['content'],
            'rating'      => $data['rating'],
            'tags'        => $data['tags'] ?? null,
            'is_hidden'   => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => '후기가 등록되었습니다.',
                'review_id' => $review->id,
            ]);
        }

        return back()->with('success', '후기가 등록되었습니다.');
    }

    public function update(Request $request, Review $review)
    {
        if ($review->user_id !== auth()->id()) abort(403);

        $data = $request->validate([
            'content' => 'required|string|max:2000',
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        $review->update($data);

        return back()->with('success', '후기가 수정되었습니다.');
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id()) abort(403);
        $review->delete();
        return back()->with('success', '후기가 삭제되었습니다.');
    }
}
