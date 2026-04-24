<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\MdProfile;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (!$q) {
            // 인기 검색어
            $popular = DB::table('search_logs')
                ->select('keyword', DB::raw('COUNT(*) as cnt'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('keyword')
                ->orderByDesc('cnt')
                ->limit(10)
                ->pluck('keyword');

            return view('search.index', ['q' => '', 'popular' => $popular, 'clubs' => collect(), 'parties' => collect(), 'mds' => collect()]);
        }

        // 검색 로그 저장
        DB::table('search_logs')->insert([
            'keyword'    => mb_substr($q, 0, 200),
            'user_id'    => auth()->id(),
            'session_id' => $request->session()->getId(),
            'created_at' => now(),
        ]);

        // 클럽 검색 (active만, 이름/지역/장르/설명)
        $clubs = Club::active()
            ->where(fn($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('area', 'like', "%{$q}%")
                ->orWhere('genre', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhere('short_description', 'like', "%{$q}%")
                ->orWhere('vibe', 'like', "%{$q}%")
            )
            ->limit(10)
            ->get();

        // 파티 검색 (upcoming만)
        $parties = Party::with('club')
            ->upcoming()
            ->where(fn($query) => $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('genre', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhere('lineup', 'like', "%{$q}%")
                ->orWhere('short_description', 'like', "%{$q}%")
            )
            ->orderByDesc('event_date')
            ->limit(10)
            ->get();

        // MD 검색 (공개+활동중만)
        $mds = MdProfile::public()
            ->with('profileMedia')
            ->where(fn($query) => $query
                ->where('display_name', 'like', "%{$q}%")
                ->orWhere('intro', 'like', "%{$q}%")
                ->orWhere('affiliation', 'like', "%{$q}%")
            )
            ->limit(10)
            ->get();

        // result_count 업데이트
        $total = $clubs->count() + $parties->count() + $mds->count();
        DB::table('search_logs')
            ->where('keyword', mb_substr($q, 0, 200))
            ->where('created_at', '>=', now()->subSeconds(5))
            ->orderByDesc('created_at')
            ->limit(1)
            ->update(['result_count' => $total]);

        if ($request->expectsJson()) {
            return response()->json([
                'clubs'   => $clubs->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'area' => $c->area,
                    'genre' => $c->genre,
                    'thumbnail' => $c->thumbnail_url,
                    'thumbnailSrcset' => $c->thumbnail_srcset,
                ]),
                'parties' => $parties->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'date' => $p->event_date?->format('n/j'),
                    'club' => $p->club?->name,
                    'eventCardLabel' => $p->event_card_label,
                    'eventCardNotice' => $p->event_card_notice,
                    'thumbnail' => $p->thumbnail_url,
                    'thumbnailSrcset' => $p->thumbnail_srcset,
                ]),
                'mds'     => $mds->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->display_name,
                    'image' => $m->profile_image,
                    'imageSrcset' => $m->profile_image_srcset,
                ]),
            ]);
        }

        return view('search.index', compact('q', 'clubs', 'parties', 'mds') + ['popular' => collect()]);
    }
}
