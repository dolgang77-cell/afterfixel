/* global window */
// 썸네일이 비어있는 항목들을 위해 두루두루 사용할 Unsplash 풀.
const CP_THUMB_POOL = [
  'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&q=80',
  'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=600&q=80',
  'https://images.unsplash.com/photo-1545128485-c400e7702796?w=1200&q=80',
  'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=1200&q=80',
  'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=1200&q=80',
];
// ClubParty mock data — parties, clubs, DJs, friends, routes
window.CP_DATA = {
  parties: [
    {
      image: CP_THUMB_POOL[0],
      id: 'p1', title: 'REVERB Vol.12', subtitle: 'Peggy Gou b2b Hyenah',
      venue: 'Faust', area: '청담동', city: '서울', country: 'KR',
      distance: 2.5, time: '23:00 — 06:00', date: '오늘 · TONIGHT', dateISO: '2026-04-27',
      genres: ['TECHNO', 'HOUSE'], live: true, friends: 4, glow: 'magenta',
      price: 45000, going: 423, capacity: 600, hot: true,
      lineup: [
        { name: 'Peggy Gou', set: '01:00 — 03:00', resident: false },
        { name: 'Hyenah',    set: '03:00 — 05:00', resident: false },
        { name: 'NET GALA',  set: '23:00 — 01:00', resident: true },
        { name: 'CIFIKA',    set: '05:00 — 06:00', resident: true },
      ],
      tags: ['LATE NIGHT', '4-DECK B2B', 'NO PHOTO ON FLOOR'],
      description: '청담의 격조있는 사운드 시스템 위에 올라선 글로벌 라인업. 밤새 드랍.',
    },
    {
      image: CP_THUMB_POOL[1],
      id: 'p2', title: 'PISTIL — DEEP HOUSE NIGHT', subtitle: 'resident: DJ Soulscape',
      venue: 'Pistil', area: '이태원', city: '서울', country: 'KR',
      distance: 4.1, time: '23:30 — 05:00', date: '오늘 · TONIGHT', dateISO: '2026-04-27',
      genres: ['HOUSE', 'DEEP'], live: false, friends: 2, glow: 'cyan',
      price: 30000, going: 180, capacity: 250,
      lineup: [
        { name: 'DJ Soulscape', set: '00:00 — 03:00', resident: true },
        { name: 'XXX',          set: '03:00 — 05:00', resident: false },
      ],
      tags: ['DEEP HOUSE', 'INTIMATE FLOOR'],
      description: '딥하우스 매니아라면 매주 들르는 곳. 좁고 어두운 플로어.',
    },
    {
      image: CP_THUMB_POOL[2],
      id: 'p3', title: 'VURT. 17th Anniversary', subtitle: 'all-nighter · 4 floors',
      venue: 'vurt.', area: '한남동', city: '서울', country: 'KR',
      distance: 5.6, time: '22:00 — 08:00', date: '내일 · SAT', dateISO: '2026-04-28',
      genres: ['TECHNO', 'INDUSTRIAL'], live: false, friends: 7, glow: 'violet',
      price: 55000, going: 612, capacity: 800,
      lineup: [
        { name: 'Marcel Dettmann', set: '02:00 — 04:00', resident: false },
        { name: 'Anetha',          set: '04:00 — 06:00', resident: false },
        { name: 'Sakuraburst',     set: '06:00 — 08:00', resident: true },
        { name: 'shogo420',        set: '22:00 — 00:00', resident: true },
      ],
      tags: ['10HR ALL-NIGHTER', '4 FLOORS', '18+'],
      description: '17주년 기념 all-nighter. 4개 플로어, 10시간 비트.',
    },
    {
      image: CP_THUMB_POOL[3],
      id: 'p4', title: 'CAKESHOP × Boiler Room', subtitle: 'Honey Dijon — 4hr set',
      venue: 'Cakeshop', area: '이태원', city: '서울', country: 'KR',
      distance: 4.3, time: '00:00 — 07:00', date: 'SAT', dateISO: '2026-04-28',
      genres: ['HOUSE', 'DISCO'], live: false, friends: 11, glow: 'magenta',
      price: 60000, going: 290, capacity: 320, hot: true,
      lineup: [
        { name: 'Honey Dijon', set: '02:00 — 06:00', resident: false },
        { name: 'Closet Yi',   set: '00:00 — 02:00', resident: true },
      ],
      tags: ['BROADCAST LIVE', 'DISCO', '4HR SET'],
      description: 'Boiler Room 송출. Honey Dijon 4시간 set.',
    },
    {
      image: CP_THUMB_POOL[4],
      id: 'p5', title: 'CONTOURS', subtitle: 'minimal · ambient · slow',
      venue: 'Volnost', area: '합정동', city: '서울', country: 'KR',
      distance: 3.2, time: '21:00 — 03:00', date: 'SUN', dateISO: '2026-04-29',
      genres: ['MINIMAL', 'AMBIENT'], live: false, friends: 1, glow: 'violet',
      price: 25000, going: 95, capacity: 400,
      lineup: [
        { name: 'rRoxymore', set: '23:00 — 01:00', resident: false },
        { name: 'Chigwa',    set: '21:00 — 23:00', resident: true },
      ],
      tags: ['SLOW', 'NO PHONES'],
      description: '느리고 깊은 밤. 폰은 잠시 내려놓고.',
    },
  ],
  clubs: [
    { image: CP_THUMB_POOL[2], id: 'c1', name: 'Faust',     area: '강남구 청담동', cap: 600, rating: 4.6, genres: ['Techno','House'], glow: 'magenta', dress: 'smart', music: ['House','Techno'], openHours: '23:00 — 06:00', addr: '서울 강남구 도산대로 511', reviews: 1240, responseMin: 12, popularity: 92, priceLevel: 3, distance: 2.5, updatedAt: '2026-04-26T18:00:00' },
    { image: CP_THUMB_POOL[3], id: 'c2', name: 'vurt.',     area: '용산구 한남동', cap: 800, rating: 4.6, genres: ['Techno','Industrial'], glow: 'violet', dress: 'no-dresscode', music: ['Techno','Industrial'], openHours: '22:00 — 08:00', addr: '서울 용산구 한남동 32-22', reviews: 2870, responseMin: 8, popularity: 95, priceLevel: 2, distance: 5.2, updatedAt: '2026-04-27T03:00:00' },
    { image: CP_THUMB_POOL[4], id: 'c3', name: 'Pistil',    area: '용산구 이태원', cap: 250, rating: 4.4, genres: ['House','Deep'], glow: 'cyan', dress: 'casual', music: ['Deep House'], openHours: '23:00 — 05:00', addr: '서울 용산구 이태원동', reviews: 612, responseMin: 25, popularity: 74, priceLevel: 1, distance: 4.1, updatedAt: '2026-04-25T22:00:00' },
    { image: CP_THUMB_POOL[0], id: 'c4', name: 'Cakeshop',  area: '용산구 이태원', cap: 320, rating: 4.5, genres: ['House','Disco'], glow: 'magenta', dress: 'smart-casual', music: ['House','Disco','Hip-hop'], openHours: '22:00 — 06:00', addr: '서울 용산구 이태원동 134', reviews: 1850, responseMin: 18, popularity: 88, priceLevel: 2, distance: 4.5, updatedAt: '2026-04-27T01:30:00' },
    { image: CP_THUMB_POOL[1], id: 'c5', name: 'Volnost',   area: '마포구 합정동', cap: 400, rating: 4.3, genres: ['Techno'], glow: 'cyan', dress: 'no-dresscode', music: ['Techno','Minimal'], openHours: '21:00 — 04:00', addr: '서울 마포구 합정동', reviews: 480, responseMin: 35, popularity: 68, priceLevel: 1, distance: 7.8, updatedAt: '2026-04-24T20:00:00' },
    { image: CP_THUMB_POOL[2], id: 'c6', name: 'Soap Seoul',area: '강남구 신사동', cap: 500, rating: 4.5, genres: ['House','R&B'], glow: 'violet', dress: 'smart', music: ['House','R&B'], openHours: '23:00 — 06:00', addr: '서울 강남구 도산대로', reviews: 920, responseMin: 15, popularity: 81, priceLevel: 3, distance: 2.9, updatedAt: '2026-04-27T00:00:00' },
  ],
  friends: [
    { id: 'f1', name: 'Jisoo K',    handle: '@jisoo.k',    going: 'p1', avatar: '#FF1077' },
    { id: 'f2', name: 'Min',         handle: '@minmusic',   going: 'p1', avatar: '#1FD2FF' },
    { id: 'f3', name: 'Hyun',        handle: '@hyunday',    going: 'p3', avatar: '#7B49FF' },
    { id: 'f4', name: 'Sora',        handle: '@sora_n',     going: 'p4', avatar: '#C8FF1A' },
    { id: 'f5', name: 'Berlin Kev',  handle: '@kevbln',     going: 'p3', avatar: '#FF1077' },
    { id: 'f6', name: 'YJ',          handle: '@yj.beats',   going: 'p1', avatar: '#1FD2FF' },
  ],
  reviews: [
    { id: 'r1', club: 'c1', author: 'Jisoo K', rating: 5, body: '청담 사운드 시스템은 진짜 미쳤다. 새벽 3시 드랍에서 모든 게 멈춤.', date: '2026·04·20' },
    { id: 'r2', club: 'c1', author: 'Berlin Kev', rating: 4, body: 'Tight floor, great DJs but bring earplugs. Bathroom queues were brutal at 2am.', date: '2026·04·14' },
    { id: 'r3', club: 'c2', author: 'Hyun', rating: 5, body: '4개 플로어 다 다른 음악. 17년 묵은 클럽 답다.', date: '2026·04·18' },
  ],
  myTickets: [
    { id: 't1', party: 'p1', status: 'confirmed', code: 'CP-7K3M9', purchaseDate: '2026·04·22' },
    { id: 't2', party: 'p3', status: 'confirmed', code: 'CP-A2L5W', purchaseDate: '2026·04·24' },
  ],
  myHistory: [
    { id: 'h1', party: 'CONTOURS Vol.4',     venue: 'Volnost',  date: '2026·04·12', rating: 4 },
    { id: 'h2', party: 'PISTIL Late',        venue: 'Pistil',   date: '2026·04·05', rating: 5 },
    { id: 'h3', party: 'REVERB Vol.11',      venue: 'Faust',    date: '2026·03·29', rating: 5 },
    { id: 'h4', party: 'NIGHT.WAVE',         venue: 'vurt.',    date: '2026·03·22', rating: 4 },
  ],
  routes: [
    {
      id: 'rt1', name: '청담 → 한남 크롤', stops: ['c1', 'c2'],
      eta: '15min', total: '8.1km',
    },
  ],
  user: {
    name: '도현', handle: '@dohyun.cp', city: '서울 · 강남구',
    nights: 47, friends: 23, ticketsAhead: 2,
    badges: ['EARLY BIRD', '4AM CLUB', 'SCENE LOCAL'],
  },
};

// formatter helper
window.CP_FORMAT = {
  won: (n) => '₩' + n.toLocaleString('ko-KR'),
  km: (n) => n.toFixed(1) + 'km',
};

// Stable seeds per club/party so thumbnails look unique but consistent.
window.CP_THUMB_SEED = {
  // clubs — keeping particle counts MUCH lower so the grid stays smooth.
  c1: { hue: 330, hue2: 280, beams: 3, bokeh: 7 },
  c2: { hue: 270, hue2: 200, beams: 4, bokeh: 9 },
  c3: { hue: 195, hue2: 250, beams: 2, bokeh: 5 },
  c4: { hue: 340, hue2: 25,  beams: 3, bokeh: 8 },
  c5: { hue: 180, hue2: 260, beams: 3, bokeh: 6 },
  c6: { hue: 290, hue2: 320, beams: 3, bokeh: 7 },
  // parties (fall back to club seed)
  p1: { hue: 330, hue2: 270, beams: 4, bokeh: 9 },
  p2: { hue: 195, hue2: 250, beams: 3, bokeh: 6 },
  p3: { hue: 270, hue2: 200, beams: 4, bokeh: 10 },
  p4: { hue: 340, hue2: 30,  beams: 3, bokeh: 8 },
  p5: { hue: 220, hue2: 270, beams: 2, bokeh: 5 },
};
