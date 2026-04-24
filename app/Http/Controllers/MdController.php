<?php

namespace App\Http\Controllers;

use App\Models\MdProfile;

class MdController extends Controller
{
    public function show(MdProfile $md)
    {
        if (!$md->visible || $md->status !== 'active') {
            abort(404);
        }

        $md->load([
            'clubs' => fn($q) => $q->where('is_active', true)->wherePivot('visible', true)->orderBy('md_club.priority'),
            'parties' => fn($q) => $q->where('event_date', '>=', today())->wherePivot('visible', true)->orderBy('md_party.priority'),
        ]);

        return view('md.show', compact('md'));
    }
}
