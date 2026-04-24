<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Club;
use App\Models\MdProfile;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MdMappingController extends Controller
{
    public function index(Request $request)
    {
        $mds = MdProfile::orderBy('display_name')->get();
        $clubs = Club::active()->orderBy('name')->get();
        $parties = Party::with('club')->upcoming()->orderBy('event_date')->get();

        $clubMappings = DB::table('md_club')
            ->join('md_profiles', 'md_club.md_profile_id', '=', 'md_profiles.id')
            ->join('clubs', 'md_club.club_id', '=', 'clubs.id')
            ->select('md_club.*', 'md_profiles.display_name as md_name', 'clubs.name as club_name', 'clubs.area')
            ->orderBy('md_profiles.display_name')
            ->get();

        $partyMappings = DB::table('md_party')
            ->join('md_profiles', 'md_party.md_profile_id', '=', 'md_profiles.id')
            ->join('parties', 'md_party.party_id', '=', 'parties.id')
            ->select('md_party.*', 'md_profiles.display_name as md_name', 'parties.name as party_name', 'parties.event_date')
            ->orderBy('md_profiles.display_name')
            ->get();

        return view('admin.md-mappings.index', compact('mds', 'clubs', 'parties', 'clubMappings', 'partyMappings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'md_profile_id' => 'required|exists:md_profiles,id',
            'type'          => 'required|in:club,party',
            'target_id'     => 'required|integer',
            'visible'       => 'boolean',
            'priority'      => 'integer|min:0',
            'note'          => 'nullable|string|max:200',
        ]);

        $table = $request->type === 'club' ? 'md_club' : 'md_party';
        $fk = $request->type === 'club' ? 'club_id' : 'party_id';

        $exists = DB::table($table)
            ->where('md_profile_id', $request->md_profile_id)
            ->where($fk, $request->target_id)
            ->exists();

        if ($exists) {
            return back()->with('error', '이미 매칭된 조합입니다.');
        }

        DB::table($table)->insert([
            'md_profile_id' => $request->md_profile_id,
            $fk             => $request->target_id,
            'visible'       => $request->boolean('visible', true),
            'priority'      => $request->input('priority', 0),
            'note'          => $request->input('note'),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        AdminLog::record('create', 'md_mapping', null, [
            'md_id' => $request->md_profile_id,
            'type'  => $request->type,
            'target_id' => $request->target_id,
        ]);

        return back()->with('success', 'MD 매칭이 추가되었습니다.');
    }

    public function destroy(string $type, int $id)
    {
        $table = $type === 'club' ? 'md_club' : 'md_party';
        DB::table($table)->where('id', $id)->delete();

        AdminLog::record('delete', 'md_mapping', $id, ['type' => $type]);

        return back()->with('success', 'MD 매칭이 삭제되었습니다.');
    }
}
