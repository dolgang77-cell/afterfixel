/* global React, Icon, GlowDot, Pill, GenreTag, Eyebrow, FloorGlow, OrganicLoader, GlobeLoader, CosmicScale, CalcKey, PulseBars, Aurora, Avatar, FriendStack, PartyRow, CPTopBar */

// JSX requires capitalized identifiers; aliases for ListControls components.
const ListBar = (p) => React.createElement(window.ListBar, p);
const FilterSheet = (p) => React.createElement(window.FilterSheet, p);
const SortSheet = (p) => React.createElement(window.SortSheet, p);
const MapView = (p) => React.createElement(window.MapView, p);
const CompareTray = (p) => React.createElement(window.CompareTray, p);
const CompareSheet = (p) => React.createElement(window.CompareSheet, p);
const { useState: useStateP, useMemo: useMemoP, useEffect: useEffectP } = React;

// ⚡ Deferred mount hook — 탭 전환 즉시 화면 표시, 무거운 리스트는 다음 idle 틱에 렌더
function useDeferredReady() {
  const [ready, setReady] = useStateP(false);
  useEffectP(() => {
    const ric = window.requestIdleCallback;
    const cic = window.cancelIdleCallback;
    if (ric) {
      const id = ric(() => setReady(true), { timeout: 200 });
      return () => cic && cic(id);
    }
    const id = setTimeout(() => setReady(true), 0);
    return () => clearTimeout(id);
  }, []);
  return ready;
}

// PARTY SCREEN — 100% 정적. 데이터 룩업/외부 컴포넌트/아이콘 폰트 전부 제거.
function PartyScreen({ onOpenParty }) {
  // 정적 더미 데이터 (탭 클릭 시 즉시 페인트, 외부 의존성 0)
  const items = [
    { id: 'p1', title: 'REVERB Vol.12',           venue: 'Faust',     area: '청담동', time: '23:00', price: '₩45k', live: true  },
    { id: 'p2', title: 'PISTIL — DEEP HOUSE',     venue: 'Pistil',    area: '이태원', time: '23:30', price: '₩30k', live: false },
    { id: 'p3', title: 'VURT. 17th Anniversary',  venue: 'vurt.',     area: '한남동', time: '22:00', price: '₩55k', live: false },
    { id: 'p4', title: 'CAKESHOP × Boiler Room',  venue: 'Cakeshop',  area: '이태원', time: '00:00', price: '₩60k', live: false },
    { id: 'p5', title: 'CONTOURS',                venue: 'Volnost',   area: '합정동', time: '21:00', price: '₩25k', live: false },
  ];
  const open = (it) => {
    if (!onOpenParty) return;
    const real = window.CP_DATA && window.CP_DATA.parties.find(p => p.id === it.id);
    onOpenParty(real || it);
  };
  return (
    <div style={{ padding: '12px 16px 32px' }}>
      <h1 style={{ margin: 0, fontSize: 22, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em' }}>
        이번 주 라인업 <span style={{ fontSize: 10, color: 'rgba(95,224,255,0.85)', letterSpacing: '0.12em', marginLeft: 6 }}>PARTY</span>
      </h1>

      {/* 시각용 검색바 */}
      <div style={{ marginTop: 12, padding: '11px 14px', borderRadius: 12, background: '#15151E', border: '1px solid rgba(255,255,255,0.08)', color: 'rgba(255,255,255,0.4)', fontSize: 12 }}>
        DJ, 클럽, 파티 검색
      </div>

      {/* 시각용 툴바 */}
      <div style={{ display: 'flex', gap: 6, marginTop: 10 }}>
        {['필터', '정렬', '지도', '비교'].map(t => (
          <div key={t} style={{
            flex: 1, textAlign: 'center', padding: '8px 0', borderRadius: 10,
            background: 'rgba(255,255,255,0.04)', border: '1px solid rgba(255,255,255,0.06)',
            color: 'rgba(255,255,255,0.55)', fontSize: 11, fontWeight: 700,
          }}>{t}</div>
        ))}
      </div>

      {/* 리스트 */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginTop: 12 }}>
        {items.map(p => (
          <div key={p.id} onClick={() => open(p)} style={{
            cursor: 'pointer', padding: 14, borderRadius: 14,
            background: '#15151E', border: '1px solid rgba(255,255,255,0.06)',
            display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 10,
          }}>
            <div style={{ minWidth: 0, flex: 1 }}>
              <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.5)' }}>{p.venue}</div>
              <div style={{ fontSize: 14, fontWeight: 800, color: '#fff', marginTop: 2 }}>{p.title}</div>
              <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', marginTop: 4 }}>{p.area} · {p.time}</div>
            </div>
            <div style={{ textAlign: 'right' }}>
              <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{p.price}</div>
              {p.live && <div style={{ fontSize: 9, fontWeight: 700, color: '#FF1077', letterSpacing: '0.12em', marginTop: 2 }}>LIVE</div>}
            </div>
          </div>
        ))}
      </div>
    </div>);

}
window.PartyScreen = PartyScreen;

// Wrapper that adds a checkbox-style overlay around a row in compare mode.
function CompareWrapRow({ mode, selected, onToggle, children }) {
  if (!mode) return children;
  return (
    <div style={{
      position: 'relative', borderRadius: 14,
      outline: selected ? '2px solid #FF1077' : '2px solid transparent',
      outlineOffset: 2,
      transition: 'outline-color 160ms ease',
    }}>
      {children}
      <button onClick={(e) => { e.stopPropagation(); onToggle(); }} style={{
        all: 'unset', cursor: 'pointer',
        position: 'absolute', top: 8, right: 8,
        width: 26, height: 26, borderRadius: 999,
        background: selected ? '#FF1077' : 'rgba(12,12,18,0.94)',
        border: selected ? '2px solid #FF1077' : '2px solid rgba(255,255,255,0.4)',
        boxShadow: selected ? '0 0 12px rgba(255,16,119,0.6)' : 'none',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        color: '#fff', zIndex: 2,
      }}>
        {selected && <Icon name="check" size={14} color="#fff" />}
      </button>
    </div>
  );
}
window.CompareWrapRow = CompareWrapRow;

// CLUB SCREEN — 100% 정적. 데이터 룩업/외부 컴포넌트/아이콘 폰트 전부 제거.
function ClubScreen({ onOpenClub }) {
  const items = [
    { id: 'c1', name: 'Faust',    area: '강남구 청담동', genres: 'Techno · House',     rating: 4.6, glow: 'magenta' },
    { id: 'c2', name: 'vurt.',    area: '용산구 한남동', genres: 'Techno · Industrial', rating: 4.6, glow: 'violet' },
    { id: 'c3', name: 'Pistil',   area: '용산구 이태원', genres: 'House · Deep',        rating: 4.4, glow: 'cyan' },
    { id: 'c4', name: 'Cakeshop', area: '용산구 이태원', genres: 'House · Disco',       rating: 4.5, glow: 'magenta' },
    { id: 'c5', name: 'Volnost',  area: '마포구 합정동', genres: 'Techno',              rating: 4.3, glow: 'cyan' },
  ];
  const tintBg = (g) => g === 'magenta' ? 'linear-gradient(135deg,#FF1077,#7B49FF)'
                     : g === 'cyan'    ? 'linear-gradient(135deg,#1FD2FF,#7B49FF)'
                     :                   'linear-gradient(135deg,#7B49FF,#FF1077)';
  const open = (it) => {
    if (!onOpenClub) return;
    const real = window.CP_DATA && window.CP_DATA.clubs.find(c => c.id === it.id);
    onOpenClub(real || it);
  };

  return (
    <div style={{ padding: '12px 16px 32px' }}>
      <h1 style={{ margin: 0, fontSize: 22, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em' }}>
        서울의 플로어 <span style={{ fontSize: 10, color: 'rgba(160,122,255,0.85)', letterSpacing: '0.12em', marginLeft: 6 }}>CLUB</span>
      </h1>

      {/* 시각용 검색바 */}
      <div style={{ marginTop: 12, padding: '11px 14px', borderRadius: 12, background: '#15151E', border: '1px solid rgba(255,255,255,0.08)', color: 'rgba(255,255,255,0.4)', fontSize: 12 }}>
        클럽 검색
      </div>

      {/* 시각용 툴바 */}
      <div style={{ display: 'flex', gap: 6, marginTop: 10 }}>
        {['필터', '정렬', '지도', '비교'].map(t => (
          <div key={t} style={{
            flex: 1, textAlign: 'center', padding: '8px 0', borderRadius: 10,
            background: 'rgba(255,255,255,0.04)', border: '1px solid rgba(255,255,255,0.06)',
            color: 'rgba(255,255,255,0.55)', fontSize: 11, fontWeight: 700,
          }}>{t}</div>
        ))}
      </div>

      {/* 그리드 */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, marginTop: 12 }}>
        {items.map(c => (
          <div key={c.id} onClick={() => open(c)} style={{
            cursor: 'pointer', borderRadius: 14, overflow: 'hidden',
            background: '#15151E', border: '1px solid rgba(255,255,255,0.08)',
          }}>
            <div style={{ height: 90, background: tintBg(c.glow), position: 'relative' }}>
              <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(0deg, rgba(7,7,10,0.55), transparent 60%)' }} />
              <div style={{ position: 'absolute', top: 8, right: 8, padding: '3px 8px', borderRadius: 999, background: 'rgba(12,12,18,0.94)', color: '#fff', fontSize: 11, fontWeight: 800 }}>★ {c.rating}</div>
            </div>
            <div style={{ padding: '10px 12px 12px' }}>
              <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{c.name}</div>
              <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.5)', marginTop: 2 }}>{c.area}</div>
              <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.65)', marginTop: 4 }}>{c.genres}</div>
            </div>
          </div>
        ))}
      </div>
    </div>);

}
window.ClubScreen = ClubScreen;

// ROUTE SCREEN — multi-stop crawl with cosmic scale visualizer
function RouteScreen({ onOpenClub, onSearch, onNotif }) {
  const data = window.CP_DATA;
  const route = data.routes[0];
  const stops = route.stops.map(id => data.clubs.find(c => c.id === id));
  // Add proposed third stop
  const proposed = data.clubs[2];

  return (
    <div style={{ paddingBottom: 32 }}>
      <CPTopBar onSearch={onSearch} onNotif={onNotif} />
      <div style={{ padding: '14px 16px 10px', position: 'relative' }}>
        <Aurora intensity={0.35} />
        <div style={{ position: 'relative', zIndex: 1, display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 10 }}>
          <h1 style={{ margin: 0, fontSize: 22, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em', lineHeight: 1.05 }}>
            오늘의 크롤
            <span style={{ fontSize: 10, fontWeight: 700, color: 'rgba(200,255,26,0.85)', letterSpacing: '0.12em', marginLeft: 8, textTransform: 'uppercase', verticalAlign: 'middle' }}>ROUTE</span>
          </h1>
          <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', fontWeight: 600, fontFamily: 'JetBrains Mono, monospace', whiteSpace: 'nowrap' }}>{stops.length} stops · {route.total}</div>
        </div>
      </div>

      {/* Cosmic scale viz of total route */}
      <div style={{ margin: '0 16px 20px', borderRadius: 20, padding: 20, background: 'linear-gradient(135deg, #15151E 0%, #1E1E2A 100%)', border: '1px solid rgba(200,255,26,0.25)', display: 'flex', gap: 16, alignItems: 'center' }}>
        <CosmicScale size={130} tint="lime" rings={5} />
        <div style={{ flex: 1 }}>
          <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(200,255,26,0.9)', textTransform: 'uppercase' }}>TOTAL ROUTE</div>
          <div style={{ fontSize: 28, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em', marginTop: 4, fontFamily: 'JetBrains Mono, monospace' }}>{route.total}</div>
          <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', fontWeight: 600, marginTop: 4 }}>22:30 → 06:00 · 7.5h</div>
          <div style={{ display: 'flex', gap: 6, marginTop: 10 }}>
            <span style={{ padding: '3px 8px', borderRadius: 999, fontSize: 9, fontWeight: 800, letterSpacing: '0.08em', background: 'rgba(200,255,26,0.16)', color: '#C8FF1A' }}>WALK · CAB · WALK</span>
          </div>
        </div>
      </div>

      {/* timeline */}
      <div style={{ padding: '0 16px', position: 'relative' }}>
        {/* vertical line */}
        <div style={{ position: 'absolute', left: 36, top: 20, bottom: 20, width: 2, background: 'linear-gradient(180deg, #FF1077 0%, #7B49FF 50%, #C8FF1A 100%)', opacity: 0.5 }} />

        {stops.map((c, i) => (
          <div key={c.id} style={{ display: 'flex', gap: 16, alignItems: 'flex-start', marginBottom: 16, position: 'relative' }}>
            <div style={{ width: 40, flexShrink: 0, display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
              <div style={{
                width: 40, height: 40, borderRadius: 999,
                background: c.glow === 'magenta' ? 'linear-gradient(180deg, #FF1077, #E60068)'
                          : c.glow === 'cyan' ? 'linear-gradient(180deg, #5AE3FF, #00B8E6)'
                          : 'linear-gradient(180deg, #A07AFF, #5E28E6)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: '#07070A', fontWeight: 900, fontSize: 16,
                boxShadow: `0 0 20px ${c.glow === 'magenta' ? 'rgba(255,16,119,0.6)' : c.glow === 'cyan' ? 'rgba(31,210,255,0.6)' : 'rgba(123,73,255,0.6)'}`,
                border: '2px solid #07070A', zIndex: 1,
              }}>{i + 1}</div>
              <div style={{ fontSize: 9, fontWeight: 700, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.08em', marginTop: 6, fontFamily: 'JetBrains Mono, monospace' }}>{i === 0 ? '22:30' : '02:30'}</div>
            </div>

            <button onClick={() => onOpenClub(c)} style={{
              all: 'unset', cursor: 'pointer', flex: 1,
              padding: 14, borderRadius: 16,
              background: '#15151E', border: '1px solid rgba(255,255,255,0.08)',
            }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                <div>
                  <div style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.45)', textTransform: 'uppercase' }}>STOP {i+1} · {c.area.split(' ').pop()}</div>
                  <div style={{ fontSize: 17, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em', marginTop: 3 }}>{c.name}</div>
                  <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.6)', fontWeight: 600, marginTop: 4 }}>{c.genres.join(' · ')} · cap. {c.cap}</div>
                </div>
                <div style={{ display: 'inline-flex', gap: 3, alignItems: 'center', padding: '3px 8px', borderRadius: 999, background: 'rgba(12,12,18,0.92)', color: '#fff', fontSize: 11, fontWeight: 700 }}>
                  <Icon name="star" size={11} color="#C8FF1A" />{c.rating}
                </div>
              </div>
            </button>

            {i < stops.length - 1 && (
              <div style={{ position: 'absolute', left: 56, top: 60, fontSize: 10, color: 'rgba(255,255,255,0.5)', fontWeight: 600, fontFamily: 'JetBrains Mono, monospace', background: '#07070A', padding: '2px 6px', borderRadius: 4, border: '1px solid rgba(255,255,255,0.08)', display: 'flex', alignItems: 'center', gap: 4 }}>
                <Icon name="arrow-down" size={9} /> 8.1km · {route.eta}
              </div>
            )}
          </div>
        ))}

        {/* add stop suggestion */}
        <div style={{ display: 'flex', gap: 16, alignItems: 'flex-start', position: 'relative' }}>
          <div style={{ width: 40, flexShrink: 0, display: 'flex', justifyContent: 'center' }}>
            <div style={{ width: 40, height: 40, borderRadius: 999, background: 'rgba(255,255,255,0.04)', border: '2px dashed rgba(255,255,255,0.2)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'rgba(255,255,255,0.5)', fontSize: 18 }}>+</div>
          </div>
          <button onClick={() => onOpenClub(proposed)} style={{
            all: 'unset', cursor: 'pointer', flex: 1,
            padding: 14, borderRadius: 16,
            background: 'rgba(255,255,255,0.03)', border: '1px dashed rgba(255,255,255,0.15)',
          }}>
            <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(31,210,255,0.85)', textTransform: 'uppercase' }}>SUGGESTED · ADD STOP</div>
            <div style={{ fontSize: 15, fontWeight: 800, color: '#fff', marginTop: 4 }}>{proposed.name}</div>
            <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', fontWeight: 500, marginTop: 4 }}>친구 5명이 가는중 · 4km from Vurt</div>
          </button>
        </div>
      </div>

      {/* Action — start route */}
      <div style={{ padding: '24px 16px 0' }}>
        <CalcKey color="lime" width="100%" height={60} glow style={{ width: '100%' }}>
          <span style={{ display: 'inline-flex', gap: 8, alignItems: 'center', fontSize: 15, fontWeight: 900, letterSpacing: '0.02em' }}>
            <Icon name="route" size={18} /> 내 루트로 시작하기
          </span>
        </CalcKey>
      </div>
    </div>
  );
}
window.RouteScreen = RouteScreen;

// ME / 마이 SCREEN
function MeScreen({ onOpenParty, onSearch, onNotif, onNav }) {
  const data = window.CP_DATA;
  const u = data.user;
  const [tab, setTab] = useStateP('going');

  return (
    <div style={{ paddingBottom: 32 }}>
      <CPTopBar onSearch={onSearch} onNotif={onNotif} />
      <div style={{ position: 'relative', padding: '20px 16px 16px' }}>
        <Aurora intensity={0.5} />
        <div style={{ position: 'relative', zIndex: 1, display: 'flex', gap: 14, alignItems: 'center' }}>
          <div style={{ position: 'relative' }}>
            <Avatar color="#FF1077" size={64} label={u.name[0]} />
            <div style={{ position: 'absolute', bottom: -2, right: -2, width: 18, height: 18, borderRadius: 999, background: '#2DE38E', border: '2px solid #07070A' }} />
          </div>
          <div style={{ flex: 1 }}>
            <Eyebrow color="rgba(255,255,255,0.5)">MY · 마이</Eyebrow>
            <div style={{ fontSize: 22, fontWeight: 900, color: '#fff', marginTop: 2, letterSpacing: '-0.02em' }}>{u.name}</div>
            <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.5)', fontWeight: 500 }}>{u.handle} · {u.city}</div>
          </div>
          <button style={{ all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.08)', color: '#fff' }}>
            <Icon name="settings-2" size={17} />
          </button>
        </div>

        {/* Stats — calculator keys */}
        <div style={{ display: 'flex', gap: 8, marginTop: 18 }}>
          <CalcKey color="magenta" width="100%" height={62} style={{ flex: 1 }} glow>
            <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 1 }}>
              <span style={{ fontSize: 18, fontWeight: 900, fontFamily: 'JetBrains Mono, monospace' }}>{u.nights}</span>
              <span style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.10em' }}>NIGHTS</span>
            </span>
          </CalcKey>
          <CalcKey color="cyan" width="100%" height={62} style={{ flex: 1 }}>
            <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 1 }}>
              <span style={{ fontSize: 18, fontWeight: 900, fontFamily: 'JetBrains Mono, monospace' }}>{u.friends}</span>
              <span style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.10em' }}>FRIENDS</span>
            </span>
          </CalcKey>
          <CalcKey color="lime" width="100%" height={62} style={{ flex: 1 }}>
            <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 1 }}>
              <span style={{ fontSize: 18, fontWeight: 900, fontFamily: 'JetBrains Mono, monospace' }}>{u.ticketsAhead}</span>
              <span style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.10em' }}>GOING</span>
            </span>
          </CalcKey>
        </div>

        {/* badges */}
        <div style={{ display: 'flex', gap: 6, marginTop: 14, flexWrap: 'wrap' }}>
          {u.badges.map(b => (
            <span key={b} style={{ padding: '5px 10px', borderRadius: 999, background: 'rgba(255,16,119,0.12)', color: '#FF7AB8', fontSize: 10, fontWeight: 800, letterSpacing: '0.10em', border: '1px solid rgba(255,16,119,0.25)' }}>{b}</span>
          ))}
        </div>
      </div>

      {/* Tabs */}
      <div style={{ display: 'flex', gap: 4, padding: '8px 16px 14px', borderTop: '1px solid rgba(255,255,255,0.04)', marginTop: 8 }}>
        {[
          { id: 'going',   label: '갈 곳' },
          { id: 'history', label: '히스토리' },
          { id: 'saved',   label: '저장' },
        ].map(t => (
          <button key={t.id} onClick={() => setTab(t.id)} style={{
            all: 'unset', cursor: 'pointer', flex: 1, padding: '9px 0', textAlign: 'center',
            borderRadius: 10, fontSize: 12, fontWeight: 800,
            background: tab === t.id ? 'rgba(255,255,255,0.08)' : 'transparent',
            color: tab === t.id ? '#fff' : 'rgba(255,255,255,0.5)',
            letterSpacing: '0.04em',
          }}>{t.label}</button>
        ))}
      </div>

      {tab === 'going' && (
        <div style={{ padding: '0 16px', display: 'flex', flexDirection: 'column', gap: 10 }}>
          {data.myTickets.map(t => {
            const p = data.parties.find(x => x.id === t.party);
            if (!p) return null;
            const friendsCount = data.friends.filter(f => f.going === p.id).length;
            return (
              <button key={t.id} onClick={() => onOpenParty(p)} style={{
                all: 'unset', cursor: 'pointer', display: 'block',
                borderRadius: 18, overflow: 'hidden',
                background: '#15151E', border: '1px solid rgba(45,227,142,0.22)',
                position: 'relative',
              }}>
                <div style={{ position: 'relative', height: 110, background: '#0E0E14', overflow: 'hidden' }}>
                  <ClubThumb id={p.id} tint={p.glow} size={400} ratio={0.28} fill />
                  <FloorGlow tint={p.glow} intensity={0.5} />
                  <div style={{ position: 'absolute', inset: 0, padding: 14, display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', gap: 6 }}>
                      <Pill bg="rgba(45,227,142,0.18)" color="#2DE38E" style={{ fontSize: 9, fontWeight: 800, letterSpacing: '0.12em', textTransform: 'uppercase' }}>
                        <Icon name="check" size={10} /> 갈래 · GOING
                      </Pill>
                      {friendsCount > 0 && (
                        <Pill bg="rgba(7,7,10,0.55)" color="#fff" glass style={{ fontSize: 9, fontWeight: 800, letterSpacing: '0.10em' }}>
                          친구 {friendsCount}
                        </Pill>
                      )}
                    </div>
                    <div>
                      <div style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.6)' }}>{p.venue} · {p.area}</div>
                      <div style={{ fontSize: 17, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em', marginTop: 2 }}>{p.title}</div>
                    </div>
                  </div>
                </div>
                <div style={{ padding: '12px 14px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <div>
                    <div style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.4)' }}>WHEN · 일정</div>
                    <div style={{ fontSize: 13, fontWeight: 800, color: '#fff', marginTop: 2 }}>{p.date} <span style={{ fontFamily: 'JetBrains Mono, monospace', color: 'rgba(255,255,255,0.6)' }}>· {p.time}</span></div>
                  </div>
                  <div style={{ display: 'inline-flex', alignItems: 'center', gap: 4, padding: '6px 10px', borderRadius: 999, background: 'rgba(31,210,255,0.10)', border: '1px solid rgba(31,210,255,0.28)', color: '#9CF0FF', fontSize: 10, fontWeight: 800, letterSpacing: '0.06em' }}>
                    <Icon name="map-pin" size={11} color="#9CF0FF" /> 길찾기
                  </div>
                </div>
              </button>
            );
          })}
          <div style={{ marginTop: 4, padding: '10px 12px', borderRadius: 12, background: 'rgba(255,255,255,0.03)', border: '1px dashed rgba(255,255,255,0.08)', fontSize: 11, color: 'rgba(255,255,255,0.5)', lineHeight: 1.5 }}>
            <span style={{ color: 'rgba(255,255,255,0.7)', fontWeight: 700 }}>참고</span> · ClubParty는 결제 플랫폼이 아니에요. 입장료는 현장에서 직접 결제하고, 우리는 너의 밤을 연결만 해줄게.
          </div>
        </div>
      )}

      {tab === 'history' && (
        <div style={{ padding: '0 16px', display: 'flex', flexDirection: 'column', gap: 8 }}>
          {data.myHistory.map(h => (
            <div key={h.id} style={{ padding: 12, borderRadius: 12, background: '#15151E', border: '1px solid rgba(255,255,255,0.06)', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{h.party}</div>
                <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', marginTop: 2 }}>{h.venue} · <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{h.date}</span></div>
              </div>
              <div style={{ display: 'flex', gap: 1 }}>
                {[1,2,3,4,5].map(s => <Icon key={s} name="star" size={11} color={s <= h.rating ? '#C8FF1A' : 'rgba(255,255,255,0.15)'} />)}
              </div>
            </div>
          ))}
        </div>
      )}

      {tab === 'saved' && (
        <div style={{ padding: '0 16px', display: 'flex', flexDirection: 'column', gap: 8 }}>
          {data.parties.slice(0, 3).map(p => <PartyRow key={p.id} party={p} onOpen={onOpenParty} />)}
        </div>
      )}

      {/* Settings shortcuts */}
      <div style={{ padding: '24px 16px 0' }}>
        <h3 style={{ margin: '0 0 10px', fontSize: 13, fontWeight: 700, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.12em', textTransform: 'uppercase' }}>설정</h3>
        <div style={{ borderRadius: 14, overflow: 'hidden', background: '#15151E', border: '1px solid rgba(255,255,255,0.06)' }}>
          {[
            { icon: 'bell',          label: '알림', meta: 'Live · 친구' },
            { icon: 'globe',         label: '언어 / 지역', meta: '한국어 · KR' },
            { icon: 'shield-check',  label: '프라이버시', meta: '친구만' },
            { icon: 'log-out',       label: '로그아웃', meta: '' },
          ].map((row, i, a) => (
            <div key={row.label} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '14px 14px', borderBottom: i < a.length - 1 ? '1px solid rgba(255,255,255,0.06)' : 'none' }}>
              <Icon name={row.icon} size={16} color="rgba(255,255,255,0.6)" />
              <div style={{ flex: 1, fontSize: 13, color: '#fff', fontWeight: 600 }}>{row.label}</div>
              <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.4)', fontWeight: 500 }}>{row.meta}</div>
              <Icon name="chevron-right" size={13} color="rgba(255,255,255,0.3)" />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
window.MeScreen = MeScreen;