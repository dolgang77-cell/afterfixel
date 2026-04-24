@extends('layouts.app')
@section('title', '이용약관')
@section('content')
<div class="px-4 py-6 max-w-2xl mx-auto">
    <h1 class="text-[20px] font-extrabold text-white mb-6">이용약관</h1>
    <div class="card p-5 text-[13px] text-gray-400 leading-[1.8] space-y-6">
        <!-- 내부 운영 초안: 실제 서비스 오픈 전 법률 검토가 필요합니다 -->

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제1조 (목적)</h2>
            <p>이 약관은 VYBE(이하 "서비스")가 제공하는 클럽/파티 정보 안내, 커뮤니티, 예약 문의 등 관련 서비스의 이용 조건과 절차, 회원과 서비스 간의 권리, 의무 및 기타 필요한 사항을 규정함을 목적으로 합니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제2조 (용어 정의)</h2>
            <p>1. "회원"이란 본 약관에 동의하고 회원가입을 완료한 자를 말합니다.<br>
            2. "닉네임"이란 회원이 가입 시 설정하는 고유 표시명을 말하며, 가입 후 변경이 불가합니다.<br>
            3. "MD"란 클럽/파티 현장 담당자로서 서비스 내 회원 문의에 응대하는 역할을 합니다.<br>
            4. "게시물"이란 회원이 작성한 커뮤니티 글, 후기, 이미지 등 일체의 콘텐츠를 말합니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제3조 (서비스 내용)</h2>
            <p>서비스는 다음을 포함합니다:<br>
            - 클럽/파티 정보 조회 및 검색<br>
            - AI 기반 클럽투어 추천<br>
            - 커뮤니티 (후기, 팁, 실시간 제보)<br>
            - 예약 문의 및 MD 상담<br>
            - 알림 및 개인화 추천<br>
            - 위치기반 추천 (Near Me)</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제4조 (회원가입 및 계정)</h2>
            <p>1. 회원가입 시 닉네임, 이메일, 비밀번호를 필수로 입력합니다.<br>
            2. 닉네임은 가입 후 변경할 수 없으며, 중복 사용이 불가합니다.<br>
            3. 허위 정보로 가입한 경우 서비스 이용이 제한될 수 있습니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제5조 (금지행위)</h2>
            <p>회원은 다음 행위를 해서는 안 됩니다:<br>
            - 욕설, 비방, 명예훼손<br>
            - 음란/선정적 콘텐츠 게시<br>
            - 광고/홍보 목적의 도배<br>
            - 허위 정보 유포<br>
            - 타인의 개인정보 노출<br>
            - 서비스 운영 방해 행위<br>
            - 금칙어 우회 사용</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제6조 (게시물 관리 및 제재)</h2>
            <p>1. 신고가 일정 기준 이상 누적된 게시물은 자동으로 숨김 처리될 수 있습니다.<br>
            2. 금칙어가 포함된 콘텐츠는 등록이 차단되거나 검토 후 비노출 처리될 수 있습니다.<br>
            3. 운영자는 약관을 위반한 게시물을 사전 고지 없이 삭제하거나 비노출 처리할 수 있습니다.<br>
            4. 위반 행위에 따라 경고, 글쓰기 제한, 일시 정지, 영구 차단 등 단계별 제재가 적용될 수 있습니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제7조 (이미지 업로드)</h2>
            <p>1. 업로드된 이미지는 자동 최적화(리사이즈, 압축)되어 저장됩니다.<br>
            2. 관리자가 업로드한 이미지를 제외한 모든 이미지는 관리자 승인 전까지 서비스에 노출되지 않습니다.<br>
            3. 부적절한 이미지는 반려 또는 삭제될 수 있습니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제8조 (예약 문의)</h2>
            <p>서비스를 통한 예약 문의는 정보 안내 목적이며, 예약 확정을 보장하지 않습니다. 최종 예약은 클럽/파티 측의 확인을 거쳐 확정됩니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제9조 (면책)</h2>
            <p>서비스는 회원 간 또는 회원과 클럽/파티 간 발생한 분쟁에 대해 직접적인 책임을 지지 않습니다. 서비스에 게시된 정보의 정확성을 보장하지 않습니다.</p>
        </section>

        <section>
            <h2 class="text-[15px] font-bold text-gray-200 mb-2">제10조 (문의)</h2>
            <p>서비스 관련 문의: support@nightlife.ive.co.kr</p>
        </section>

        <p class="text-[11px] text-gray-600 pt-4 border-t border-white/[0.06]">시행일: 2026년 4월 15일</p>
    </div>
</div>
@endsection
