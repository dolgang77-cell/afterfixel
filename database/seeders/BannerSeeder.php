<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();
        $banners = [
            // ── home_top 배너 ──
            [
                'title' => '전국 오늘 밤 파티 스캔',
                'image_url' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=1200&q=80',
                'link_url' => '/parties',
                'position' => 'home_top',
                'sort_order' => 1,
                'is_active' => true,
                'start_date' => $today,
                'end_date' => now()->addDays(14)->toDateString(),
            ],
            [
                'title' => '부산 서면 최신 파티 흐름',
                'image_url' => 'https://images.unsplash.com/photo-1545128485-c400e7702796?w=1200&q=80',
                'link_url' => '/clubs?area=부산 서면',
                'position' => 'home_top',
                'sort_order' => 2,
                'is_active' => true,
                'start_date' => $today,
                'end_date' => now()->addDays(30)->toDateString(),
            ],
            [
                'title' => '영종도 대형 공연형 클럽',
                'image_url' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=1200&q=80',
                'link_url' => '/clubs?area=영종도',
                'position' => 'home_top',
                'sort_order' => 3,
                'is_active' => true,
                'start_date' => $today,
                'end_date' => now()->addDays(30)->toDateString(),
            ],
            [
                'title' => '제주 선셋 라운지 & 심야 코스',
                'image_url' => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=1200&q=80',
                'link_url' => '/clubs?area=중문',
                'position' => 'home_top',
                'sort_order' => 4,
                'is_active' => true,
                'start_date' => $today,
                'end_date' => now()->addDays(30)->toDateString(),
            ],

            // ── home_middle 배너 ──
            [
                'title' => '처음이라면? 지역별 입문자 추천',
                'image_url' => 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=1200&q=80',
                'link_url' => '/clubs',
                'position' => 'home_middle',
                'sort_order' => 1,
                'is_active' => true,
                'start_date' => $today,
                'end_date' => now()->addDays(60)->toDateString(),
            ],
            [
                'title' => '전국 Foreigner Friendly',
                'image_url' => 'https://images.unsplash.com/photo-1598653222000-6b7b7a552625?w=1200&q=80',
                'link_url' => '/clubs',
                'position' => 'home_middle',
                'sort_order' => 2,
                'is_active' => true,
                'start_date' => $today,
                'end_date' => now()->addDays(60)->toDateString(),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                [
                    'position' => $banner['position'],
                    'sort_order' => $banner['sort_order'],
                ],
                $banner
            );
        }
    }
}
