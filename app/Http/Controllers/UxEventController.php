<?php

namespace App\Http\Controllers;

use App\Models\UxEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UxEventController extends Controller
{
    public function store(Request $request)
    {
        if (!Schema::hasTable('ux_events')) {
            return response()->json(['ok' => true, 'skipped' => 'table_missing'], 202);
        }

        $validated = $request->validate([
            'event_name' => 'required|string|max:100',
            'page_type' => 'nullable|string|max:50',
            'target_type' => 'nullable|string|max:50',
            'target_id' => 'nullable|integer|min:1',
            'context' => 'nullable|string|max:100',
            'metadata' => 'nullable',
        ]);

        $metadata = $request->input('metadata');

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            $metadata = json_last_error() === JSON_ERROR_NONE && is_array($decoded)
                ? $decoded
                : ['raw' => Str::limit($metadata, 1000, '')];
        }

        if (!is_array($metadata)) {
            $metadata = null;
        }

        UxEvent::query()->create([
            'event_name' => $validated['event_name'],
            'page_type' => $validated['page_type'] ?? null,
            'target_type' => $validated['target_type'] ?? null,
            'target_id' => $validated['target_id'] ?? null,
            'context' => $validated['context'] ?? null,
            'metadata' => $metadata,
            'path' => $request->path(),
            'referrer' => Str::limit((string) $request->headers->get('referer', ''), 2000, ''),
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'occurred_at' => now(),
        ]);

        return response()->json(['ok' => true], 202);
    }
}
