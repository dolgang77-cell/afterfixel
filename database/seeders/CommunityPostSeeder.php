<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CommunityPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            ['nickname' => '테크노요정', 'club_id' => 1, 'type' => 'review', 'title' => 'FABRIC 다녀왔어요 ㄹㅇ 국내 최고', 'content' => '어제 FABRIC 갔는데 사운드 진짜 미쳤어요. OBJEKT 세트 들으면서 눈물날 뻔. 입장료 25k인데 전혀 아깝지 않음. 드레스코드 체크 꽤 엄격해서 슬리퍼 신고 온 사람 입장 거절당하는 거 봤어요.'],
            ['nickname' => '강남클럽러', 'club_id' => 2, 'type' => 'review', 'title' => '옥타곤 FISHER 파티 후기', 'content' => '2시간 줄 서서 들어갔는데 그 고생이 아깝지 않았어요. 사운드 시스템은 정말 세계 최고급 맞음. 입장료 50k였는데 드링크 2잔 포함이라 나쁘지 않아요.'],
            ['nickname' => '이태원쪽', 'club_id' => 3, 'type' => 'tip', 'title' => '케이크샵 꿀팁 공유', 'content' => '케이크샵은 금요일 11시 이전에 가면 줄 안 서도 됨. 외국인 비율이 60% 이상이라 영어로 대화하기 좋은 환경. 힙합 좋아하면 무조건 추천.'],
            ['nickname' => '클럽투어러', 'club_id' => null, 'type' => 'realtime', 'title' => '오늘 홍대 줄 상황', 'content' => '지금 홍대 FABRIC 입구 줄 한 40명 정도. 20분 대기 예상. MASS는 지금 줄 없음 바로 입장 가능!'],
            ['nickname' => '성수감성', 'club_id' => 8, 'type' => 'review', 'title' => '서클라운지 요즘 분위기 최고', 'content' => '성수에 이런 공간이 있었다니. 하우스/재즈 믹스 플레이리스트가 완벽함. 혼자 와도 전혀 어색하지 않은 분위기. 드링크 가격도 다른 클럽 대비 합리적.'],
            ['nickname' => '압구정여우', 'club_id' => 7, 'type' => 'review', 'title' => 'PEDE 드레스코드 실제 경험담', 'content' => 'PEDE 처음 갔는데 드레스코드 진짜 엄격해요. 남자친구가 청바지 입고 갔다가 입장 거절 당했어요. 반드시 세미 포멀 이상으로 입고 가세요!'],
            ['nickname' => 'EDM마니아', 'club_id' => 4, 'type' => 'tip', 'title' => '아레나 좌석 예약 팁', 'content' => '아레나는 테이블 예약하면 입장 줄 안 서도 되고 드링크 패키지도 있음. 4명 이상이면 테이블 예약이 오히려 저렴할 수 있어요.'],
            ['nickname' => '테크노덕후', 'club_id' => 6, 'type' => 'realtime', 'title' => 'SOAP 오늘 분위기 공유', 'content' => '지금 SOAP 내부 꽉 찼어요. 근데 분위기 진짜 최고. DJ 세트 너무 좋음. 입장 기다리는 사람 많으니까 늦게 가면 입장 못할 수도 있어요.'],
        ];

        foreach ($posts as $post) {
            \App\Models\CommunityPost::create($post);
        }
    }
}
