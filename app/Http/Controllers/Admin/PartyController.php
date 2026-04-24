<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Club;
use App\Models\AdminLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PartyController extends Controller
{
    public function index(Request $request)
    {
        $parties = Party::with('club')
            ->when($request->search, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->club_id, fn($q, $v) => $q->where('club_id', $v))
            ->orderByDesc('event_date')
            ->paginate(20)
            ->withQueryString();

        $clubs = Club::orderBy('name')->pluck('name', 'id');

        return view('admin.parties.index', compact('parties', 'clubs'));
    }

    public function create()
    {
        $clubs = Club::active()->orderBy('name')->pluck('name', 'id');
        return view('admin.parties.form', ['party' => new Party(), 'clubs' => $clubs]);
    }

    public function store(Request $request)
    {
        $data = $this->validateParty($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        $data['tags'] = $this->parseTags($request->input('tags_text'));
        $data['images'] = $this->parseImages($request->input('images_text'));

        $party = Party::create($data);

        AdminLog::record('create', 'party', $party->id, ['name' => $party->name]);

        // 관심사 매칭 알림 발송
        app(NotificationService::class)->sendNewPartyAlerts($party->load('club'));

        return redirect()->route('admin.parties.index')->with('success', "파티 '{$party->name}'이(가) 등록되었습니다.");
    }

    public function edit(Party $party)
    {
        $clubs = Club::active()->orderBy('name')->pluck('name', 'id');
        return view('admin.parties.form', compact('party', 'clubs'));
    }

    public function update(Request $request, Party $party)
    {
        $data = $this->validateParty($request);
        $data['tags'] = $this->parseTags($request->input('tags_text'));
        $data['images'] = $this->parseImages($request->input('images_text'));

        $party->update($data);

        AdminLog::record('update', 'party', $party->id, ['name' => $party->name]);

        return redirect()->route('admin.parties.index')->with('success', "파티 '{$party->name}'이(가) 수정되었습니다.");
    }

    public function destroy(Party $party)
    {
        $name = $party->name;
        $party->delete();

        AdminLog::record('delete', 'party', $party->id, ['name' => $name]);

        return redirect()->route('admin.parties.index')->with('success', "파티 '{$name}'이(가) 삭제되었습니다.");
    }

    /**
     * 파티 일괄 상태 변경
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer',
            'status' => 'required|in:upcoming,ongoing,ended,cancelled',
        ]);

        $count = Party::whereIn('id', $request->ids)->update(['status' => $request->status]);

        AdminLog::record('update', 'party', null, [
            'action' => 'bulk_status',
            'status' => $request->status,
            'count'  => $count,
        ]);

        $statusLabels = ['upcoming' => '예정', 'ongoing' => '진행중', 'ended' => '종료', 'cancelled' => '취소'];
        return back()->with('success', "{$count}개 파티가 '{$statusLabels[$request->status]}'(으)로 변경되었습니다.");
    }

    private function validateParty(Request $request): array
    {
        return $request->validate([
            'club_id'          => 'required|exists:clubs,id',
            'name'             => 'required|string|max:100',
            'event_date'       => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
            'lineup'           => 'nullable|string|max:500',
            'genre'            => 'nullable|in:' . implode(',', Club::$genres),
            'ticket_price_min' => 'nullable|integer|min:0',
            'ticket_price_max' => 'nullable|integer|min:0',
            'booking_link'     => 'nullable|url|max:500',
            'dress_code'       => 'nullable|string|max:100',
            'entry_condition'  => 'nullable|string|max:200',
            'description'      => 'nullable|string|max:2000',
            'guide_text'       => 'nullable|string|max:2000',
            'thumbnail'        => 'nullable|string|max:500',
            'status'           => 'required|in:upcoming,ongoing,ended,cancelled',
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
