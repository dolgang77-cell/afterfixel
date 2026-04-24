<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Club;
use App\Models\PushCampaign;
use App\Services\PushService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PushCampaignController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = PushCampaign::with('creator')
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->type, fn($q, $v) => $q->where('campaign_type', $v))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'     => PushCampaign::count(),
            'sent'      => PushCampaign::where('status', 'sent')->count(),
            'scheduled' => PushCampaign::where('status', 'scheduled')->count(),
            'totalSent' => PushCampaign::sum('sent_count'),
            'totalClicked' => PushCampaign::sum('clicked_count'),
        ];

        return view('admin.push.index', compact('campaigns', 'stats'));
    }

    public function create()
    {
        $areas = Club::$areas;
        $genres = Club::$genres;
        $campaign = $this->applyRetentionPresetDefaults(new PushCampaign(), request('preset'));

        return view('admin.push.form', [
            'campaign' => $campaign,
            'areas' => $areas,
            'genres' => $genres,
            'retentionPresets' => PushCampaign::$retentionPresets,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCampaign($request);
        $data['created_by'] = auth()->id();
        $data['target_query'] = $this->buildTargetQuery($request);

        if ($data['send_type'] === 'immediate') {
            $data['status'] = 'sending';
        } else {
            $data['status'] = 'scheduled';
        }

        $campaign = PushCampaign::create($data);
        AdminLog::record('create', 'push_campaign', $campaign->id, ['title' => $campaign->title]);

        if ($campaign->status === 'sending') {
            app(PushService::class)->sendCampaign($campaign);
            return redirect()->route('admin.push.show', $campaign)->with('success', '푸쉬가 발송되었습니다.');
        }

        return redirect()->route('admin.push.index')->with('success', '푸쉬가 예약되었습니다.');
    }

    public function show(PushCampaign $campaign)
    {
        $campaign->load('creator');

        return view('admin.push.show', [
            'campaign' => $campaign,
            'targetSummary' => $this->buildTargetSummary($campaign),
        ]);
    }

    public function edit(PushCampaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', '이미 발송된 캠페인은 수정할 수 없습니다.');
        }
        $areas = Club::$areas;
        $genres = Club::$genres;

        return view('admin.push.form', [
            'campaign' => $campaign,
            'areas' => $areas,
            'genres' => $genres,
            'retentionPresets' => PushCampaign::$retentionPresets,
        ]);
    }

    public function update(Request $request, PushCampaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', '이미 발송된 캠페인은 수정할 수 없습니다.');
        }

        $data = $this->validateCampaign($request);
        $data['target_query'] = $this->buildTargetQuery($request);
        $data['status'] = $data['send_type'] === 'scheduled' ? 'scheduled' : 'draft';

        $campaign->update($data);
        AdminLog::record('update', 'push_campaign', $campaign->id);

        return redirect()->route('admin.push.index')->with('success', '캠페인이 수정되었습니다.');
    }

    public function cancel(PushCampaign $campaign)
    {
        if ($campaign->status !== 'scheduled') {
            return back()->with('error', '예약된 캠페인만 취소할 수 있습니다.');
        }
        $campaign->update(['status' => 'cancelled']);
        AdminLog::record('cancel', 'push_campaign', $campaign->id);
        return back()->with('success', '예약이 취소되었습니다.');
    }

    public function sendNow(PushCampaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', '이 캠페인은 발송할 수 없습니다.');
        }
        app(PushService::class)->sendCampaign($campaign);
        return back()->with('success', '푸쉬가 즉시 발송되었습니다.');
    }

    private function validateCampaign(Request $request): array
    {
        return $request->validate([
            'title'         => 'required|string|max:200',
            'body'          => 'required|string|max:2000',
            'image'         => 'nullable|string|max:500',
            'link'          => 'nullable|string|max:500',
            'campaign_type' => 'required|in:' . implode(',', array_keys(PushCampaign::$types)),
            'target_type'   => 'required|in:all,logged_in,area,genre,custom',
            'send_type'     => 'required|in:immediate,scheduled',
            'scheduled_at'  => 'nullable|date|after:now',
            'retention_preset' => 'nullable|required_if:target_type,custom|in:' . implode(',', array_keys(PushCampaign::$retentionPresets)),
            'retention_days' => 'nullable|integer|min:1|max:30',
        ]);
    }

    private function buildTargetQuery(Request $request): array
    {
        return array_filter([
            'areas'         => $request->input('target_areas'),
            'genres'        => $request->input('target_genres'),
            'exclude_staff' => $request->boolean('exclude_staff'),
            'retention_preset' => $request->input('retention_preset'),
            'retention_days' => $request->filled('retention_days') ? (int) $request->input('retention_days') : null,
        ]);
    }

    private function applyRetentionPresetDefaults(PushCampaign $campaign, ?string $preset): PushCampaign
    {
        if (!$preset || !isset(PushCampaign::$retentionPresets[$preset])) {
            return $campaign;
        }

        $defaults = match ($preset) {
            'recent_view_no_inquiry' => [
                'title' => '최근 본 장소를 다시 확인해보세요',
                'body' => '최근 확인한 클럽/파티가 아직 문의 전 상태입니다. 상세로 바로 돌아가 확인해보세요.',
                'campaign_type' => 'marketing',
                'target_type' => 'custom',
                'target_query' => ['retention_preset' => $preset, 'retention_days' => 3, 'exclude_staff' => true],
            ],
            'favorite_no_inquiry' => [
                'title' => '찜한 항목, 지금 문의해보세요',
                'body' => '찜해둔 클럽/파티 중 아직 상담을 시작하지 않은 항목이 있습니다. 상세에서 바로 문의할 수 있습니다.',
                'campaign_type' => 'marketing',
                'target_type' => 'custom',
                'target_query' => ['retention_preset' => $preset, 'retention_days' => 7, 'exclude_staff' => true],
            ],
            'inquiry_reply_unread' => [
                'title' => '문의 답변이 도착했습니다',
                'body' => '담당자의 답변이나 상태 업데이트가 도착했습니다. 문의 상세에서 바로 확인하세요.',
                'campaign_type' => 'system',
                'target_type' => 'custom',
                'target_query' => ['retention_preset' => $preset, 'retention_days' => 3, 'exclude_staff' => true],
            ],
            default => [],
        };

        $campaign->forceFill($defaults);

        return $campaign;
    }

    private function buildTargetSummary(PushCampaign $campaign): array
    {
        $query = $campaign->target_query ?? [];
        $summary = [
            'targetType' => match ($campaign->target_type) {
                'all' => '전체 사용자',
                'logged_in' => '로그인 사용자',
                'area' => '특정 지역 선호',
                'genre' => '특정 장르 선호',
                'custom' => '리텐션 프리셋',
                default => $campaign->target_type,
            },
            'retentionPreset' => Arr::get(PushCampaign::$retentionPresets, data_get($query, 'retention_preset')),
            'retentionDays' => data_get($query, 'retention_days'),
            'areas' => Arr::wrap(data_get($query, 'areas')),
            'genres' => Arr::wrap(data_get($query, 'genres')),
            'excludeStaff' => (bool) data_get($query, 'exclude_staff'),
        ];

        return $summary;
    }
}
