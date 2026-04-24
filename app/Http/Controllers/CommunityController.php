<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunityPostRequest;
use App\Models\CommunityPost;
use App\Models\Club;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');

        return view('community.index', [
            'posts' => CommunityPost::with('club')->visible()->ofType($type)->recent()->paginate(20),
            'type'  => $type,
            'clubs' => Club::active()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('community.create', [
            'clubs' => Club::active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCommunityPostRequest $request)
    {
        // 제재 상태 체크
        if (!auth()->user()->canWrite()) {
            return back()->with('error', '글쓰기가 제한된 상태입니다.');
        }

        // 도배 체크
        if (!\App\Services\ModerationService::checkSpamLimit(auth()->id())) {
            return back()->with('error', '잠시 후 다시 시도해주세요.');
        }

        // 금칙어 체크
        $textToCheck = ($request->input('title') ?? '') . ' ' . ($request->input('content') ?? '');
        $filterResult = \App\Services\ForbiddenWordFilter::check($textToCheck);
        if (!$filterResult['passed'] && $filterResult['action'] === 'block') {
            return back()->with('error', '부적절한 표현이 포함되어 등록할 수 없습니다.')->withInput();
        }

        $post = CommunityPost::create(
            $request->validated() + [
                'user_id'    => auth()->id(),
                'nickname'   => auth()->user()->nickname,
                'session_id' => session()->getId(),
            ]
        );

        // 업로드된 이미지 연결
        if ($request->filled('uploaded_image_ids')) {
            $ids = array_filter(explode(',', $request->input('uploaded_image_ids')));
            \App\Models\Media::whereIn('id', $ids)
                ->where('uploaded_by', auth()->id())
                ->update(['owner_id' => $post->id]);
        }

        return redirect()->route('community.index')
            ->with('success', '게시글이 등록되었습니다.');
    }

    public function report(CommunityPost $post)
    {
        $post->report();

        return back()->with('success', '신고가 접수되었습니다.');
    }
}
