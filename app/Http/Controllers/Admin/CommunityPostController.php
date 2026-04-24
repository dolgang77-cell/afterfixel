<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\CommunityPost;
use App\Models\AdminLog;
use Illuminate\Http\Request;

class CommunityPostController extends Controller
{
    public function index(Request $request)
    {
        $posts = CommunityPost::with('club')
            ->when($request->filter === 'reported', fn($q) => $q->where('report_count', '>', 0)->where('is_hidden', false))
            ->when($request->filter === 'hidden', fn($q) => $q->where('is_hidden', true))
            ->when($request->type, fn($q, $v) => $q->ofType($v))
            ->when($request->search, fn($q, $v) => $q->where(fn($sub) =>
                $sub->where('title', 'like', "%{$v}%")->orWhere('content', 'like', "%{$v}%")->orWhere('nickname', 'like', "%{$v}%")
            ))
            ->orderByDesc('report_count')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $clubs = Club::active()->orderBy('name')->pluck('name', 'id');
        return view('admin.posts.form', ['post' => new CommunityPost(), 'clubs' => $clubs]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);
        $data['images'] = $this->parseImages($request->input('images_text'));
        $data['nickname'] = $data['nickname'] ?: '관리자';

        $post = CommunityPost::create($data);

        AdminLog::record('create', 'community_post', $post->id, ['title' => $post->title]);

        return redirect()->route('admin.posts.index')->with('success', "게시글 '{$post->title}'이(가) 등록되었습니다.");
    }

    public function show(CommunityPost $post)
    {
        $post->load('club');
        return view('admin.posts.show', compact('post'));
    }

    public function edit(CommunityPost $post)
    {
        $clubs = Club::active()->orderBy('name')->pluck('name', 'id');
        return view('admin.posts.form', compact('post', 'clubs'));
    }

    public function update(Request $request, CommunityPost $post)
    {
        $data = $this->validatePost($request);
        $data['images'] = $this->parseImages($request->input('images_text'));

        $post->update($data);

        AdminLog::record('update', 'community_post', $post->id, ['title' => $post->title]);

        return redirect()->route('admin.posts.index')->with('success', "게시글 '{$post->title}'이(가) 수정되었습니다.");
    }

    public function toggleHidden(CommunityPost $post)
    {
        $post->update(['is_hidden' => !$post->is_hidden]);

        $action = $post->is_hidden ? 'hide' : 'unhide';
        AdminLog::record($action, 'community_post', $post->id, ['title' => $post->title]);

        $msg = $post->is_hidden ? '게시글이 숨김 처리되었습니다.' : '게시글 숨김이 해제되었습니다.';
        return back()->with('success', $msg);
    }

    public function resetReports(CommunityPost $post)
    {
        $post->update(['report_count' => 0]);

        AdminLog::record('reset_reports', 'community_post', $post->id, ['title' => $post->title]);

        return back()->with('success', '신고 횟수가 초기화되었습니다.');
    }

    public function destroy(CommunityPost $post)
    {
        $title = $post->title;
        $post->delete();

        AdminLog::record('delete', 'community_post', $post->id, ['title' => $title]);

        return redirect()->route('admin.posts.index')->with('success', "게시글 '{$title}'이(가) 삭제되었습니다.");
    }

    /**
     * 신고 게시글 일괄 숨김
     */
    public function bulkHide(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $count = CommunityPost::whereIn('id', $request->ids)
            ->where('is_hidden', false)
            ->update(['is_hidden' => true]);

        AdminLog::record('hide', 'community_post', null, [
            'action' => 'bulk_hide',
            'count'  => $count,
            'ids'    => $request->ids,
        ]);

        return back()->with('success', "{$count}개 게시글이 숨김 처리되었습니다.");
    }

    private function validatePost(Request $request): array
    {
        return $request->validate([
            'title'    => 'required|string|max:200',
            'content'  => 'required|string|max:5000',
            'nickname' => 'nullable|string|max:50',
            'club_id'  => 'nullable|exists:clubs,id',
            'type'     => 'required|in:' . implode(',', array_keys(CommunityPost::$types)),
        ]);
    }

    private function parseImages(?string $text): array
    {
        if (!$text) return [];
        return array_values(array_filter(array_map('trim', explode("\n", $text))));
    }
}
