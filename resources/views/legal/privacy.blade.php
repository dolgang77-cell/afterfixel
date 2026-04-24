@extends('layouts.app')
@section('title', '개인정보처리방침')
@section('content')
<div class="px-4 py-6 max-w-2xl mx-auto">
    <h1 class="text-[20px] font-extrabold text-white mb-6">개인정보처리방침</h1>
    <div class="card p-5 text-[13px] text-gray-400 leading-[1.8] space-y-6">
        <!-- 내부 운영 초안: 실제 서비스 오픈 전 법률 검토가 필요합니다 -->

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">1. 수집하는 개인정보</h2>
            <p><strong>필수 수집:</strong> 닉네임, 이메일, 비밀번호(해시 저장)<br>
            <strong>선택 수집:</strong> 전화번호<br>
            <strong>자동 수집:</strong> IP 주소, 접속 기기정보(기기유형, OS, 브라우저, 기기모델), 접속 시간, 접속 경로, 언어 설정, 앱 버전(앱 사용 시)<br>
            <strong>기기 식별자:</strong> 웹 환경에서는 서비스 내 생성된 익명 식별자(웹 ID), 앱 환경에서는 앱 범위 기기 식별자(Android ID 기반)가 수집됩니다. 이는 하드웨어 고유번호가 아닌 앱/브라우저 범위의 식별값입니다.<br>
            <strong>위치 정보:</strong> Near Me(내 근처 추천) 기능 사용 시 사용자 동의 하에 현재 위치 좌표가 일시적으로 사용됩니다.<br>
            <strong>푸쉬 토큰:</strong> 알림 수신 동의 시 기기 푸쉬 토큰이 저장됩니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">2. 수집 방법</h2>
            <p>- 회원가입 폼을 통한 직접 입력<br>
            - 서비스 이용 중 자동 수집 (접속 로그, 기기 정보)<br>
            - 앱 설치 및 실행 시 자동 수집 (앱 기기 식별자, 앱 버전)</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">3. 이용 목적</h2>
            <p>- 회원 식별 및 서비스 제공<br>
            - 클럽/파티 정보 추천 및 개인화<br>
            - 커뮤니티 운영 및 부정이용 방지<br>
            - 예약 문의 처리 및 MD 연결<br>
            - 알림 발송 (파티 리마인더, 추천, 공지)<br>
            - 서비스 개선을 위한 접속 통계 분석<br>
            - 민원 처리 및 운영 문의 대응</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">4. 보유 및 이용기간</h2>
            <p>- 회원 정보: 탈퇴 시까지 (탈퇴 후 즉시 파기 또는 법령에 따라 일정 기간 보관)<br>
            - 접속 로그: 최대 1년간 보관 후 파기<br>
            - 문의 내역: 상담 종료 후 1년간 보관<br>
            - 커뮤니티 게시물: 삭제 요청 시 즉시 처리</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">5. 제3자 제공</h2>
            <p>원칙적으로 이용자의 개인정보를 제3자에게 제공하지 않습니다. 단, 법령에 의한 요청이 있는 경우 예외로 합니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">6. 이용자 권리</h2>
            <p>- 개인정보 열람, 수정, 삭제 요청 가능<br>
            - 마이페이지에서 일부 정보 직접 수정 가능<br>
            - 알림 수신 동의 철회 가능<br>
            - 회원 탈퇴를 통한 개인정보 파기 요청 가능</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">7. 파기 절차</h2>
            <p>- 수집 목적이 달성된 개인정보는 즉시 파기합니다.<br>
            - 전자적 파일: 복구 불가능한 방법으로 삭제<br>
            - 종이 문서: 분쇄 또는 소각</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">8. 안전성 확보 조치</h2>
            <p>- 비밀번호 해시 저장 (bcrypt)<br>
            - HTTPS 암호화 통신<br>
            - 관리자 권한 분리 (일반/MD/관리자)<br>
            - 접속 로그 기록 및 모니터링<br>
            - 이미지 업로드 형식/용량 제한</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">9. 문의처</h2>
            <p>개인정보 관련 문의: support@nightlife.ive.co.kr</p>
        </section>

        <p class="text-[11px] text-gray-600 pt-4 border-t border-white/[0.06]">시행일: 2026년 4월 15일</p>
    </div>
</div>
@endsection
