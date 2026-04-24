<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\AdminLog;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('position')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => new Banner()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBanner($request);
        $banner = Banner::create($data);

        AdminLog::record('create', 'banner', $banner->id, ['title' => $banner->title]);

        return redirect()->route('admin.banners.index')->with('success', "배너 '{$banner->title}'이(가) 등록되었습니다.");
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validateBanner($request);
        $banner->update($data);

        AdminLog::record('update', 'banner', $banner->id, ['title' => $banner->title]);

        return redirect()->route('admin.banners.index')->with('success', "배너 '{$banner->title}'이(가) 수정되었습니다.");
    }

    public function destroy(Banner $banner)
    {
        $title = $banner->title;
        $banner->delete();

        AdminLog::record('delete', 'banner', $banner->id, ['title' => $title]);

        return redirect()->route('admin.banners.index')->with('success', "배너 '{$title}'이(가) 삭제되었습니다.");
    }

    private function validateBanner(Request $request): array
    {
        return $request->validate([
            'title'      => 'required|string|max:100',
            'image_url'  => 'required|string|max:500',
            'link_url'   => 'nullable|url|max:500',
            'position'   => 'required|in:home_top,home_middle,party_top,club_top',
            'sort_order' => 'integer|min:0',
            'is_active'  => 'boolean',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);
    }
}
