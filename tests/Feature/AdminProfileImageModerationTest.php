<?php

namespace Tests\Feature;

use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProfileImageModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_profile_image(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'nickname' => 'admin01',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
            'nickname' => 'member01',
        ]);

        $profileImage = ProfileImage::create([
            'user_id' => $user->id,
            'upload_uuid' => '11111111-1111-1111-1111-111111111111',
            'disk' => 'public',
            'image_path' => 'uploads/profile/1/test/optimized.webp',
            'thumb_path' => 'uploads/profile/1/test/thumb.webp',
            'mime_type' => 'image/webp',
            'optimized_size' => 1024,
            'width' => 512,
            'height' => 512,
            'moderation_provider' => 'mock',
            'moderation_verdict' => 'suspicious',
            'moderation_score' => 4,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post('/admin/approve', [
            'profile_image_id' => $profileImage->id,
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $profileImage->refresh();
        $this->assertSame('approved', $profileImage->status);
        $this->assertTrue($profileImage->is_current);
        $this->assertSame($admin->id, $profileImage->approved_by);
    }

    public function test_admin_can_reject_pending_profile_image_with_reason(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'nickname' => 'admin02',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
            'nickname' => 'member02',
        ]);

        $profileImage = ProfileImage::create([
            'user_id' => $user->id,
            'upload_uuid' => '22222222-2222-2222-2222-222222222222',
            'disk' => 'public',
            'image_path' => 'uploads/profile/2/test/optimized.webp',
            'thumb_path' => 'uploads/profile/2/test/thumb.webp',
            'mime_type' => 'image/webp',
            'optimized_size' => 1024,
            'width' => 512,
            'height' => 512,
            'moderation_provider' => 'mock',
            'moderation_verdict' => 'suspicious',
            'moderation_score' => 4,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post('/admin/reject', [
            'profile_image_id' => $profileImage->id,
            'reason' => '노출 기준에 맞지 않습니다.',
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $profileImage->refresh();
        $this->assertSame('rejected', $profileImage->status);
        $this->assertSame('노출 기준에 맞지 않습니다.', $profileImage->rejection_reason);
        $this->assertSame($admin->id, $profileImage->approved_by);
    }
}
