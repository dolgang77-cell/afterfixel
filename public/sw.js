// VYBE Service Worker
const CACHE_NAME = 'vybe-v2';

// 오프라인에서도 반드시 제공할 핵심 셸
const PRECACHE_URLS = [
  '/offline',
  '/manifest.json',
  '/app-icons/icon-192x192.png',
  '/app-icons/icon-512x512.png',
];

// ── Install: 핵심 셸 프리캐시 ──
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(PRECACHE_URLS))
      .then(() => self.skipWaiting())
  );
});

// ── Activate: 이전 캐시 정리 ──
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      ))
      .then(() => self.clients.claim())
  );
});

// ── Fetch: Network-first, 실패 시 캐시 폴백 ──
self.addEventListener('fetch', (event) => {
  const { request } = event;

  // POST 등 비-GET 요청은 패스
  if (request.method !== 'GET') return;

  // admin 영역, API 요청은 캐시하지 않음
  const url = new URL(request.url);
  if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api/')) return;

  const isDocumentRequest =
    request.mode === 'navigate'
    || request.destination === 'document'
    || request.headers.get('Accept')?.includes('text/html');

  // HTML 문서는 항상 네트워크 우선, 캐시 저장 금지
  if (isDocumentRequest) {
    event.respondWith(
      fetch(request).catch(() => caches.match('/offline'))
    );
    return;
  }

  // 외부 CDN 리소스는 Cache-first
  if (url.origin !== location.origin) {
    event.respondWith(
      caches.match(request).then((cached) => {
        if (cached) return cached;
        return fetch(request).then((response) => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // 내부 페이지: Network-first, 오프라인 시 캐시 → 오프라인 페이지
  event.respondWith(
    fetch(request)
      .then((response) => {
        // 정상 응답이면 캐시에 저장
        if (response.ok && response.type === 'basic') {
          const clone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
        }
        return response;
      })
      .catch(() => {
        return caches.match(request).then((cached) => {
          if (cached) return cached;
          // HTML 요청이면 오프라인 페이지
          if (request.headers.get('Accept')?.includes('text/html')) {
            return caches.match('/offline');
          }
        });
      })
  );
});
