<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\DateHelper;
use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use Illuminate\Http\Request;

class AccessLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AccessLog::query()
            ->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))
            ->when($request->ip, fn($q, $v) => $q->where('ip_address', 'like', "%{$v}%"))
            ->when($request->device, fn($q, $v) => $q->where('device_type', $v))
            ->when($request->device_source, fn($q, $v) => $q->where('device_source', $v))
            ->when($request->browser, fn($q, $v) => $q->where('browser', $v))
            ->when($request->os, fn($q, $v) => $q->where('os', $v))
            ->when($request->device_id, fn($q, $v) => $q->where('device_id', 'like', "%{$v}%"))
            ->when($request->guest_id, fn($q, $v) => $q->where('guest_id', 'like', "%{$v}%"))
            ->when($request->login_status === 'logged_in', fn($q) => $q->where('is_logged_in', true))
            ->when($request->login_status === 'guest', fn($q) => $q->where('is_logged_in', false))
            ->when($request->date_from, fn($q, $v) => $q->where('created_at', '>=', DateHelper::kstToUtc($v)))
            ->when($request->date_to, fn($q, $v) => $q->where('created_at', '<=', DateHelper::kstToUtc($v, true)))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.access-logs.index', compact('logs'));
    }

    public function show(AccessLog $accessLog)
    {
        $accessLog->load('user');
        return view('admin.access-logs.show', ['log' => $accessLog]);
    }
}
