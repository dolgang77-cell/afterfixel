<?php

/**
 * 다국어 설정 - 단일 소스 (Single Source of Truth)
 *
 * 새 언어 추가 시:
 * 1. 이 파일의 'available' 배열에 항목 추가
 * 2. lang/{code}.json 번역 파일 생성 (lang/en.json 복사 후 번역)
 * 끝. header 드롭다운, 미들웨어, 지원 목록 전부 자동 반영됨.
 */

return [

    // 기본 언어
    'default' => 'ko',

    // 사용 가능한 언어 목록
    // enabled: true인 것만 사용자에게 노출
    'available' => [
        'ko' => [
            'label'  => 'KOR',
            'native' => '한국어',
            'enabled' => true,
            'order'   => 1,
        ],
        'en' => [
            'label'  => 'ENG',
            'native' => 'English',
            'enabled' => true,
            'order'   => 2,
        ],
        'ja' => [
            'label'  => 'JPN',
            'native' => '日本語',
            'enabled' => true,
            'order'   => 3,
        ],
        'zh' => [
            'label'  => 'CHN',
            'native' => '中文',
            'enabled' => true,
            'order'   => 4,
        ],
    ],

];
