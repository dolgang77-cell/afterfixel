<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UxEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UxEventTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_store_ux_event_with_array_metadata(): void
    {
        $response = $this->post(route('ux-events.store'), [
            'event_name' => 'home_primary_cta_click',
            'page_type' => 'home',
            'target_type' => 'club',
            'target_id' => 43,
            'context' => 'hero_primary',
            'metadata' => [
                'label' => '오늘 갈 곳 찾기',
                'href' => '/tonight',
            ],
        ]);

        $response->assertStatus(202)
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('ux_events', [
            'event_name' => 'home_primary_cta_click',
            'page_type' => 'home',
            'target_type' => 'club',
            'target_id' => 43,
            'context' => 'hero_primary',
        ]);

        $event = UxEvent::query()->firstOrFail();

        $this->assertSame('오늘 갈 곳 찾기', $event->metadata['label']);
        $this->assertSame('/tonight', $event->metadata['href']);
        $this->assertNotEmpty($event->session_id);
    }

    public function test_authenticated_user_event_accepts_json_string_metadata(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('ux-events.store'), [
                'event_name' => 'inquiry_submit',
                'page_type' => 'club_detail',
                'target_type' => 'club',
                'target_id' => 7,
                'context' => 'club-inquiry',
                'metadata' => json_encode([
                    'form_id' => 'club-inquiry',
                    'method' => 'POST',
                ], JSON_UNESCAPED_UNICODE),
            ]);

        $response->assertStatus(202)
            ->assertJson(['ok' => true]);

        $event = UxEvent::query()->firstOrFail();

        $this->assertSame($user->id, $event->user_id);
        $this->assertSame('club-inquiry', $event->metadata['form_id']);
        $this->assertSame('POST', $event->metadata['method']);
    }
}
