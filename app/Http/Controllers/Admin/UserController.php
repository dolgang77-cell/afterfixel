<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Favorite;
use App\Models\RecentView;
use App\Models\User;
use App\Models\AccessLog;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn($q, $v) => $q->where(fn($sub) =>
                $sub->where('name', 'like', "%{$v}%")
                    ->orWhere('email', 'like', "%{$v}%")
                    ->orWhere('phone', 'like', "%{$v}%")
            ))
            ->when($request->role, fn($q, $v) => $q->where('role', $v))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $favCount = Favorite::where('user_id', $user->id)->count();
        $recentViewCount = RecentView::where('user_id', $user->id)->count();
        $recentLogs = AccessLog::where('user_id', $user->id)->orderByDesc('created_at')->limit(10)->get();

        return view('admin.users.show', compact('user', 'favCount', 'recentViewCount', 'recentLogs'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:user,md,admin,super_admin']);

        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', '최고 관리자의 권한은 변경할 수 없습니다.');
        }

        $old = $user->role;
        $user->update(['role' => $request->role]);

        AdminLog::record('update_role', 'user', $user->id, ['from' => $old, 'to' => $request->role]);

        return back()->with('success', "'{$user->name}' 권한이 '{$request->role}'(으)로 변경되었습니다.");
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate(['status' => 'required|in:active,suspended,withdrawn']);

        if ($user->id === auth()->id()) {
            return back()->with('error', '자신의 상태는 변경할 수 없습니다.');
        }

        $old = $user->status;
        $user->update(['status' => $request->status]);

        AdminLog::record('update_status', 'user', $user->id, ['from' => $old, 'to' => $request->status]);

        $labels = ['active' => '활성', 'suspended' => '정지', 'withdrawn' => '탈퇴'];
        return back()->with('success', "'{$user->name}' 상태가 '{$labels[$request->status]}'(으)로 변경되었습니다.");
    }
}
