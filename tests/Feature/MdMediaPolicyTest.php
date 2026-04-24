<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Media;
use App\Models\MdProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MdMediaPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_md_can_upload_to_assigned_club_with_auto_approval(): void
    {
        Storage::fake('public');

        [$user, $profile] = $this->createMdUser();
        $club = $this->createClub('Assigned Club', 'assigned-club');

        DB::table('md_club')->insert([
            'md_profile_id' => $profile->id,
            'club_id' => $club->id,
            'visible' => true,
            'priority' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/api/upload/md-images', [
            'image' => UploadedFile::fake()->image('club.jpg', 1200, 800),
            'owner_type' => 'club',
            'owner_id' => $club->id,
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('approval_status', 'approved')
            ->assertJsonPath('is_visible', true);

        $this->assertDatabaseHas('media', [
            'owner_type' => 'club',
            'owner_id' => $club->id,
            'uploaded_by' => $user->id,
            'uploaded_by_role' => 'md',
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'is_visible' => true,
        ]);
    }

    public function test_md_cannot_upload_to_unassigned_club(): void
    {
        Storage::fake('public');

        [$user] = $this->createMdUser();
        $club = $this->createClub('Other Club', 'other-club');

        $response = $this->actingAs($user)->post('/api/upload/md-images', [
            'image' => UploadedFile::fake()->image('club.jpg', 1200, 800),
            'owner_type' => 'club',
            'owner_id' => $club->id,
        ], ['Accept' => 'application/json']);

        $response->assertForbidden();

        $this->assertDatabaseMissing('media', [
            'owner_type' => 'club',
            'owner_id' => $club->id,
            'uploaded_by' => $user->id,
        ]);
    }

    public function test_md_general_user_surface_stays_pending(): void
    {
        $approval = Media::approvalAttributesFor('md', 55, 'community');

        $this->assertSame('pending', $approval['approval_status']);
        $this->assertNull($approval['approved_by']);
        $this->assertNull($approval['approved_at']);
        $this->assertTrue($approval['is_visible']);
    }

    private function createMdUser(): array
    {
        $user = User::factory()->create([
            'role' => 'md',
            'status' => 'active',
        ]);

        $profile = MdProfile::create([
            'user_id' => $user->id,
            'display_name' => '테스트 MD',
            'status' => 'active',
            'visible' => true,
        ]);

        return [$user, $profile];
    }

    private function createClub(string $name, string $slug): Club
    {
        return Club::create([
            'name' => $name,
            'slug' => $slug,
            'area' => '강남',
            'genre' => 'EDM',
            'entry_fee_min' => 10000,
            'entry_fee_max' => 20000,
            'foreigner_allowed' => true,
            'is_active' => true,
        ]);
    }
}
