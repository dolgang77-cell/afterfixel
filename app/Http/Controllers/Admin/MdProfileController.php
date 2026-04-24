<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Club;
use App\Models\MdProfile;
use App\Models\User;
use Illuminate\Http\Request;

class MdProfileController extends Controller
{
    public function index(Request $request)
    {
        $mds = MdProfile::query()
            ->with('profileMedia')
            ->when($request->search, fn($q, $v) => $q->where('display_name', 'like', "%{$v}%"))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->orderBy('priority')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.md.index', compact('mds'));
    }

    public function create()
    {
        $users = User::where('role', 'md')->orderBy('name')->pluck('name', 'id');
        return view('admin.md.form', ['md' => new MdProfile(), 'users' => $users]);
    }

    public function store(Request $request)
    {
        $data = $this->validateMd($request);
        $data['areas'] = $this->parseList($request->input('areas_text'));
        $data['genres'] = $this->parseList($request->input('genres_text'));

        $md = MdProfile::create($data);
        AdminLog::record('create', 'md_profile', $md->id, ['name' => $md->display_name]);

        return redirect()->route('admin.md.index')->with('success', "MD '{$md->display_name}'이(가) 등록되었습니다.");
    }

    public function show(MdProfile $md)
    {
        $md->load('clubs', 'parties', 'user', 'profileMedia');
        return view('admin.md.show', compact('md'));
    }

    public function edit(MdProfile $md)
    {
        $users = User::where('role', 'md')->orderBy('name')->pluck('name', 'id');
        return view('admin.md.form', compact('md', 'users'));
    }

    public function update(Request $request, MdProfile $md)
    {
        $data = $this->validateMd($request);
        $data['areas'] = $this->parseList($request->input('areas_text'));
        $data['genres'] = $this->parseList($request->input('genres_text'));

        $md->update($data);
        AdminLog::record('update', 'md_profile', $md->id, ['name' => $md->display_name]);

        return redirect()->route('admin.md.index')->with('success', "MD '{$md->display_name}'이(가) 수정되었습니다.");
    }

    public function destroy(MdProfile $md)
    {
        $name = $md->display_name;
        $md->delete();
        AdminLog::record('delete', 'md_profile', $md->id, ['name' => $name]);

        return redirect()->route('admin.md.index')->with('success', "MD '{$name}'이(가) 삭제되었습니다.");
    }

    private function validateMd(Request $request): array
    {
        return $request->validate([
            'user_id'       => 'nullable|exists:users,id',
            'display_name'  => 'required|string|max:50',
            'profile_image' => 'nullable|string|max:500',
            'intro'         => 'nullable|string|max:2000',
            'contact_info'  => 'nullable|string|max:200',
            'external_link' => 'nullable|string|max:500',
            'affiliation'   => 'nullable|string|max:100',
            'admin_memo'    => 'nullable|string|max:2000',
            'status'        => 'required|in:active,inactive,hidden',
            'visible'       => 'boolean',
            'priority'      => 'integer|min:0',
        ]);
    }

    private function parseList(?string $text): array
    {
        if (!$text) return [];
        return array_values(array_filter(array_map('trim', explode(',', $text))));
    }
}
