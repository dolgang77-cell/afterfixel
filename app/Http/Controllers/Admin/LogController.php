<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\ClickLog;
use App\Models\NiteNotification;
use App\Models\TourRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AdminLog::with('user')
            ->when($request->action, fn($q, $v) => $q->where('action', $v))
            ->when($request->target_type, fn($q, $v) => $q->where('target_type', $v))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.logs.index', compact('logs'));
    }

    public function clicks(Request $request)
    {
        $clickStats = ClickLog::select('target_type', DB::raw('COUNT(*) as total'), DB::raw('DATE(created_at) as date'))
            ->when($request->target_type, fn($q, $v) => $q->where('target_type', $v))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('target_type', 'date')
            ->orderByDesc('date')
            ->get()
            ->groupBy('target_type');

        $recentClicks = ClickLog::latest()
            ->limit(50)
            ->get();

        return view('admin.logs.clicks', compact('clickStats', 'recentClicks'));
    }

    public function recommendations(Request $request)
    {
        $recommendations = TourRecommendation::latest()
            ->paginate(20)
            ->withQueryString();

        $dailyStats = TourRecommendation::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderByDesc('date')
            ->get();

        return view('admin.logs.recommendations', compact('recommendations', 'dailyStats'));
    }

    public function notifications(Request $request)
    {
        $notifications = NiteNotification::query()
            ->when($request->type, fn($q, $v) => $q->where('type', $v))
            ->when($request->channel, fn($q, $v) => $q->where('channel', $v))
            ->when($request->read === 'read', fn($q) => $q->where('is_read', true))
            ->when($request->read === 'unread', fn($q) => $q->where('is_read', false))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $typeCounts = NiteNotification::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $totalNotifs = NiteNotification::count();
        $readCount = NiteNotification::where('is_read', true)->count();
        $readRate = $totalNotifs > 0 ? round(($readCount / $totalNotifs) * 100) : 0;

        return view('admin.logs.notifications', compact('notifications', 'typeCounts', 'readRate'));
    }
}
