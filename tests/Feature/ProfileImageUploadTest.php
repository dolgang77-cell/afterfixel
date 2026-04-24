<?php

namespace Tests\Feature;

use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_profile_image_and_get_auto_approved_when_safe(): void
    {
        Storage::fake('local');
        config()->set('profile-images.disk', 'local');
        config()->set('profile-images.moderation.provider', 'mock');
        config()->set('profile-images.moderation.mock_verdict', 'safe');

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
            'nickname' => 'tester',
        ]);

        $response = $this->actingAs($user)->post('/upload/profile-image', [
            'image' => UploadedFile::fake()->image('avatar.png', 1600, 1200),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('status', 'approved')
            ->assertJsonPath('moderation_provider', 'mock');

        $image = ProfileImage::firstOrFail();

        $this->assertSame('approved', $image->status);
        $this->assertTrue($image->is_current);
        Storage::disk('local')->assertExists($image->image_path);
        Storage::disk('local')->assertExists($image->thumb_path);
    }

    public function test_pending_upload_keeps_existing_current_image(): void
    {
        Storage::fake('local');
        config()->set('profile-images.disk', 'local');
        config()->set('profile-images.moderation.provider', 'mock');

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
            'nickname' => 'tester2',
        ]);

        config()->set('profile-images.moderation.mock_verdict', 'safe');
        $approvedResponse = $this->actingAs($user)->post('/upload/profile-image', [
            'image' => UploadedFile::fake()->image('approved.jpg', 800, 800),
        ], ['Accept' => 'application/json']);
        $approvedResponse->assertCreated();

        $approvedImage = ProfileImage::where('status', 'approved')->firstOrFail();

        config()->set('profile-images.moderation.mock_verdict', 'suspicious');
        $pendingResponse = $this->actingAs($user)->post('/upload/profile-image', [
            'image' => UploadedFile::fake()->image('pending.jpg', 2000, 1500),
        ], ['Accept' => 'application/json']);

        $pendingResponse->assertStatus(202)
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('current_image_url', $approvedImage->image_url);

        $this->assertSame($approvedImage->id, ProfileImage::where('user_id', $user->id)->current()->value('id'));
        $this->assertDatabaseHas('profile_images', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }
}
