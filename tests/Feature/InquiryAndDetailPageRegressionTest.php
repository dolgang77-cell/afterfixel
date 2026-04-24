<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Inquiry;
use App\Models\Party;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryAndDetailPageRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_party_detail_page_renders_successfully(): void
    {
        $party = $this->createParty();

        $response = $this->get("/parties/{$party->id}");

        $response->assertOk();
        $response->assertSee($party->name);
        $response->assertSee('예약 전 확인');
    }

    public function test_club_detail_page_renders_successfully(): void
    {
        $club = $this->createClub();

        $response = $this->get("/clubs/{$club->id}");

        $response->assertOk();
        $response->assertSee($club->name);
        $response->assertSee('예약 전 확인');
    }

    public function test_authenticated_user_can_create_party_inquiry(): void
    {
        $user = User::factory()->create();
        $party = $this->createParty();

        $response = $this->actingAs($user)
            ->from("/parties/{$party->id}")
            ->post(route('inquiries.store', ['type' => 'party', 'id' => $party->id]), [
                'intent_type' => 'question',
                'message' => '오늘 4인 방문 가능 여부 확인 부탁드립니다.',
                'preferred_contact' => 'kakao:test-user',
                'visit_date' => now()->toDateString(),
                'party_size' => 4,
                'budget_min' => 200000,
                'budget_max' => 400000,
                'visit_time_slot' => '22_24',
                'gender_mix' => '남2 여2',
                'special_request' => '테이블 우선 안내 요청',
            ]);

        $response->assertRedirect("/parties/{$party->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'user_id' => $user->id,
            'target_type' => 'party',
            'target_id' => $party->id,
            'intent_type' => 'question',
            'subject' => '파티 ' . $party->name . ' - 문의',
            'party_size' => 4,
            'budget_min' => 200000,
            'budget_max' => 400000,
            'visit_time_slot' => '22_24',
            'gender_mix' => '남2 여2',
        ]);
    }

    public function test_inquiry_store_returns_404_for_missing_target(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('inquiries.store', ['type' => 'party', 'id' => 999999]), [
                'intent_type' => 'question',
                'subject' => '문의',
                'message' => '존재하지 않는 대상 테스트',
            ]);

        $response->assertNotFound();
        $this->assertSame(0, Inquiry::count());
    }

    public function test_inquiry_store_rejects_invalid_budget_range(): void
    {
        $user = User::factory()->create();
        $club = $this->createClub();

        $response = $this->actingAs($user)
            ->from("/clubs/{$club->id}")
            ->post(route('inquiries.store', ['type' => 'club', 'id' => $club->id]), [
                'intent_type' => 'quote_request',
                'subject' => '예산 문의',
                'message' => '예산 범위 검증 테스트',
                'budget_min' => 500000,
                'budget_max' => 100000,
            ]);

        $response->assertRedirect("/clubs/{$club->id}");
        $response->assertSessionHasErrors('budget_min');
        $this->assertSame(0, Inquiry::count());
    }

    private function createClub(array $overrides = []): Club
    {
        return Club::query()->create(array_merge([
            'name' => 'M2',
            'slug' => 'm2-hongdae',
            'area' => '홍대',
            'genre' => 'EDM',
            'subgenre' => 'House',
            'vibe' => 'Energetic',
            'open_time' => '22:00',
            'close_time' => '05:00',
            'entry_fee_min' => 20000,
            'entry_fee_max' => 40000,
            'foreigner_allowed' => true,
            'dress_code' => 'casual',
            'rating_avg' => 4.5,
            'rating_count' => 12,
            'description' => '테스트용 클럽 설명',
            'short_description' => '테스트용 요약',
            'intro_title' => '소개',
            'guide_text' => '입장 전 가이드',
            'is_active' => true,
        ], $overrides));
    }

    private function createParty(array $overrides = []): Party
    {
        $club = $overrides['club'] ?? $this->createClub();
        unset($overrides['club']);

        return Party::query()->create(array_merge([
            'club_id' => $club->id,
            'name' => 'M2 ELECTRONIC NIGHT',
            'slug' => 'm2-electronic-night',
            'event_date' => today()->toDateString(),
            'start_time' => '22:00',
            'end_time' => '05:00',
            'genre' => 'EDM',
            'ticket_price_min' => 30000,
            'ticket_price_max' => 50000,
            'dress_code' => 'casual',
            'description' => '테스트용 파티 설명',
            'short_description' => '테스트용 파티 요약',
            'intro_title' => '소개',
            'guide_text' => '파티 가이드',
            'status' => 'upcoming',
        ], $overrides));
    }
}
