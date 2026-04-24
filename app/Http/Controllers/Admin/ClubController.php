<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\AdminLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClubController extends Controller
{
    public function index(Request $request)
    {
        $clubs = Club::query()
            ->when($request->area, fn($q, $v) => $q->where('area', $v))
            ->when($request->genre, fn($q, $v) => $q->where('genre', $v))
            ->when($request->search, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.clubs.index', compact('clubs'));
    }

    public function create()
    {
        return view('admin.clubs.form', ['club' => new Club()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateClub($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        $data['tags'] = $this->parseTags($request->input('tags_text'));
        $data['images'] = $this->parseImages($request->input('images_text'));

        $club = Club::create($data);

        AdminLog::record('create', 'club', $club->id, ['name' => $club->name]);
        app(NotificationService::class)->sendNewClubAlerts($club);

        return redirect()->route('admin.clubs.index')->with('success', "클럽 '{$club->name}'이(가) 등록되었습니다.");
    }

    public function edit(Club $club)
    {
        return view('admin.clubs.form', compact('club'));
    }

    public function update(Request $request, Club $club)
    {
        $data = $this->validateClub($request);
        $data['tags'] = $this->parseTags($request->input('tags_text'));
        $data['images'] = $this->parseImages($request->input('images_text'));

        $club->update($data);

        AdminLog::record('update', 'club', $club->id, ['name' => $club->name]);

        return redirect()->route('admin.clubs.index')->with('success', "클럽 '{$club->name}'이(가) 수정되었습니다.");
    }

    public function destroy(Club $club)
    {
        $name = $club->name;
        $club->delete();

        AdminLog::record('delete', 'club', $club->id, ['name' => $name]);

        return redirect()->route('admin.clubs.index')->with('success', "클럽 '{$name}'이(가) 삭제되었습니다.");
    }

    public function updatePriority(Request $request, Club $club)
    {
        $request->validate(['sort_order' => 'required|integer|min:0']);
        $club->update(['sort_order' => $request->sort_order]);

        AdminLog::record('update_priority', 'club', $club->id, ['sort_order' => $request->sort_order]);

        return back()->with('success', '노출 순서가 변경되었습니다.');
    }

    private function validateClub(Request $request): array
    {
        return $request->validate([
            'name'              => 'required|string|max:100',
            'area'              => 'required|in:' . implode(',', Club::$areas),
            'genre'             => 'required|in:' . implode(',', Club::$genres),
            'subgenre'          => 'nullable|string|max:50',
            'vibe'              => 'nullable|string|max:100',
            'open_time'         => 'nullable|date_format:H:i',
            'close_time'        => 'nullable|date_format:H:i',
            'entry_fee_min'     => 'nullable|integer|min:0',
            'entry_fee_max'     => 'nullable|integer|min:0',
            'foreigner_allowed' => 'boolean',
            'dress_code'        => 'nullable|string|max:50',
            'dress_code_detail' => 'nullable|string|max:200',
            'address'           => 'nullable|string|max:200',
            'phone'             => 'nullable|string|max:20',
            'instagram'         => 'nullable|string|max:100',
            'lat'               => 'nullable|numeric',
            'lng'               => 'nullable|numeric',
            'description'       => 'nullable|string|max:2000',
            'guide_text'        => 'nullable|string|max:2000',
            'thumbnail'         => 'nullable|string|max:500',
            'is_active'         => 'boolean',
            'rating_avg'        => 'nullable|numeric|min:0|max:5',
            'rating_count'      => 'nullable|integer|min:0',
        ]);
    }

    private function parseTags(?string $text): array
    {
        if (!$text) return [];
        return array_values(array_filter(array_map('trim', explode(',', $text))));
    }

    private function parseImages(?string $text): array
    {
        if (!$text) return [];
        return array_values(array_filter(array_map('trim', explode("\n", $text))));
    }
}
