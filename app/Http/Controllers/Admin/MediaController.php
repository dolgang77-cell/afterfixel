<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Media;
use App\Models\MdProfile;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::with('uploader')
            ->when($request->status, fn($q, $v) => $q->where('approval_status', $v))
            ->when($request->owner_type, fn($q, $v) => $q->where('owner_type', $v))
            ->when($request->uploaded_by_role, fn($q, $v) => $q->where('uploaded_by_role', $v))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $pendingCount = Media::pending()->count();
        $mdAutoApprovedCount = Media::where('uploaded_by_role', 'md')->where('approval_status', 'approved')->count();

        return view('admin.media.index', compact('media', 'pendingCount', 'mdAutoApprovedCount'));
    }

    public function diagnostics(Request $request)
    {
        $scope = $request->input('scope', 'issues');
        $issueType = $request->input('issue_type', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $rows = $this->buildDiagnosticRows($request);

        $issueRows = $rows->filter(fn (array $row) => !empty($row['issues']))->values();
        $displayRows = $scope === 'all' ? $rows->values() : $issueRows;
        $pagedRows = new LengthAwarePaginator(
            $displayRows->forPage($page, $perPage)->values(),
            $displayRows->count(),
            $perPage,
            $page,
            ['path' => route('admin.media.diagnostics'), 'query' => $request->query()]
        );

        $summary = [
            'total' => $rows->count(),
            'issueCount' => $issueRows->count(),
            'missingOriginalCount' => $rows->where('missing_original', true)->count(),
            'missingThumbnailCount' => $rows->where('missing_thumbnail', true)->count(),
            'missingVariantCount' => $rows->filter(fn (array $row) => !empty($row['missing_variant_widths']))->count(),
            'mdProfileMismatchCount' => $rows->where('md_profile_mismatch', true)->count(),
        ];

        return view('admin.media.diagnostics', [
            'rows' => $pagedRows,
            'summary' => $summary,
            'scope' => $scope,
            'issueType' => $issueType,
        ]);
    }

    public function approve(Media $media)
    {
        $media->approve(auth()->id());
        AdminLog::record('approve', 'media', $media->id);
        return back()->with('success', '이미지가 승인되었습니다.');
    }

    public function reject(Request $request, Media $media)
    {
        $media->reject(auth()->id(), $request->input('reason'));
        AdminLog::record('reject', 'media', $media->id, ['reason' => $request->input('reason')]);
        return back()->with('success', '이미지가 반려되었습니다.');
    }

    public function hide(Media $media)
    {
        $media->update(['approval_status' => 'hidden']);
        AdminLog::record('hide', 'media', $media->id);
        return back()->with('success', '이미지가 숨김 처리되었습니다.');
    }

    public function destroy(Media $media)
    {
        AdminLog::record('delete', 'media', $media->id);
        $media->delete();
        return back()->with('success', '이미지가 삭제되었습니다.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        Media::whereIn('id', $request->ids)->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        AdminLog::record('bulk_approve', 'media', null, ['count' => count($request->ids)]);
        return back()->with('success', count($request->ids) . '개 이미지가 승인되었습니다.');
    }

    public function regenerateVariants(Media $media)
    {
        $this->regenerateMedia($media);

        return back()->with('success', "미디어 #{$media->id} 파생 이미지가 재생성되었습니다.");
    }

    public function bulkRegenerateVariants(Request $request)
    {
        $rows = $this->buildDiagnosticRows($request);
        $scope = $request->input('scope', 'issues');
        $targetRows = $scope === 'all'
            ? $rows
            : $rows->filter(fn (array $row) => !empty($row['issues']))->values();

        if ($targetRows->isEmpty()) {
            return back()->with('success', '재생성할 미디어가 없습니다.');
        }

        $processed = 0;

        foreach ($targetRows as $row) {
            $this->regenerateMedia($row['media']);
            $processed++;
        }

        AdminLog::record('bulk_regenerate_variants', 'media', null, [
            'count' => $processed,
            'scope' => $scope,
            'owner_type' => $request->input('owner_type'),
            'approval_status' => $request->input('approval_status'),
            'uploaded_by_role' => $request->input('uploaded_by_role'),
            'issue_type' => $request->input('issue_type'),
        ]);

        return back()->with('success', "{$processed}개 미디어의 파생 이미지가 일괄 재생성되었습니다.");
    }

    private function buildDiagnosticRows(Request $request): Collection
    {
        $disk = Storage::disk('public');
        $issueType = $request->input('issue_type', '');

        $rows = $this->diagnosticMediaQuery($request)
            ->get()
            ->map(function (Media $media) use ($disk) {
                $missingOriginal = !$media->file_path || !$disk->exists($media->file_path);
                $missingThumbnail = !$media->thumbnail_path || !$disk->exists($media->thumbnail_path);
                $missingVariantWidths = collect($media->missingVariantWidths())
                    ->filter(fn (int $width) => !$disk->exists((string) data_get($media->variant_paths, (string) $width)))
                    ->values()
                    ->all();
                $mdProfileMismatch = false;

                if ($media->owner_type === 'md_profile') {
                    $mdProfileImage = MdProfile::whereKey($media->owner_id)->value('profile_image');
                    $currentMedia = Media::forOwner('md_profile', (int) $media->owner_id)
                        ->public()
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->first();

                    $mdProfileMismatch = $media->approval_status === 'approved'
                        && $media->is_visible
                        && $currentMedia?->id === $media->id
                        && $mdProfileImage !== $media->file_url;
                }

                $issues = collect([
                    $missingOriginal ? '원본 누락' : null,
                    $missingThumbnail ? '썸네일 누락' : null,
                    !empty($missingVariantWidths) ? 'variant 누락: ' . implode(', ', $missingVariantWidths) : null,
                    $mdProfileMismatch ? 'MD 대표 이미지 미동기화' : null,
                ])->filter()->values()->all();

                return [
                    'media' => $media,
                    'missing_original' => $missingOriginal,
                    'missing_thumbnail' => $missingThumbnail,
                    'missing_variant_widths' => $missingVariantWidths,
                    'md_profile_mismatch' => $mdProfileMismatch,
                    'issues' => $issues,
                ];
            });

        if ($issueType === '') {
            return $rows->values();
        }

        return $rows->filter(function (array $row) use ($issueType) {
            return match ($issueType) {
                'original' => $row['missing_original'],
                'thumbnail' => $row['missing_thumbnail'],
                'variant' => !empty($row['missing_variant_widths']),
                'md_profile_sync' => $row['md_profile_mismatch'],
                'healthy' => empty($row['issues']),
                default => true,
            };
        })->values();
    }

    private function diagnosticMediaQuery(Request $request)
    {
        return Media::with('uploader')
            ->when($request->filled('owner_type'), fn ($query) => $query->where('owner_type', $request->string('owner_type')))
            ->when($request->filled('approval_status'), fn ($query) => $query->where('approval_status', $request->string('approval_status')))
            ->when($request->filled('uploaded_by_role'), fn ($query) => $query->where('uploaded_by_role', $request->string('uploaded_by_role')))
            ->orderByDesc('created_at');
    }

    private function regenerateMedia(Media $media): void
    {
        $derivedAssets = ImageOptimizer::regenerateDerivedAssetsForStoredMedia(
            $media->file_path,
            $media->mime_type,
            true,
        );

        $media->forceFill([
            'thumbnail_path' => $derivedAssets['thumbnail_path'],
            'variant_paths' => $derivedAssets['variant_paths'],
        ])->save();

        if ($media->owner_type === 'md_profile' && $media->approval_status === 'approved' && $media->is_visible) {
            $current = Media::forOwner('md_profile', (int) $media->owner_id)
                ->public()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            if ($current?->id === $media->id) {
                MdProfile::whereKey($media->owner_id)->update(['profile_image' => $media->file_url]);
            }
        }

        AdminLog::record('regenerate_variants', 'media', $media->id, [
            'variant_count' => count($derivedAssets['variant_paths']),
        ]);
    }
}
