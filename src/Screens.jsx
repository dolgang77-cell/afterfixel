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

// PARTY SCREEN — minimal navigation hub. Connects to home & detail pages.
// PARTY SCREEN — full features (filter/sort/map/compare/list) but kept light via
// deferred mount + content-visibility on rows.
function PartyScreen({ onOpenParty, onSearch, onNotif, onTab }) {
  const ready = useDeferredReady();
  const data = window.CP_DATA;
  const [genre, setGenre] = useStateP('전체');
  const [day, setDay] = useStateP('all');
  const [filterValue, setFilterValue] = useStateP({ areas: [], genres: [] });
  const [sortBy, setSortBy] = useStateP('recent');
  const [filterOpen, setFilterOpen] = useStateP(false);
  const [sortOpen, setSortOpen] = useStateP(false);
  const [mapMode, setMapMode] = useStateP(false);
  const [compareMode, setCompareMode] = useStateP(false);
  const [compareItems, setCompareItems] = useStateP([]);
  const [compareSheetOpen, setCompareSheetOpen] = useStateP(false);

  const genres = ['전체', 'TECHNO', 'HOUSE', 'DEEP', 'DISCO', 'INDUSTRIAL', 'MINIMAL', 'AMBIENT', 'R&B'];
  const allAreas = data ? Array.from(new Set(data.parties.map(p => p.area))) : [];
  const allGenres = data ? Array.from(new Set(data.parties.flatMap(p => p.genres))) : [];
  const days = [
    { id: 'all', label: '전체' },
    { id: '2026-04-27', label: '오늘' },
    { id: '2026-04-28', label: '토요일' },
    { id: '2026-04-29', label: '일요일' },
  ];
  const venueOf = (p) => data && data.clubs.find(c => c.name === p.venue);

  const list = useMemoP(() => {
    if (!data) return [];
    let l = data.parties.slice();
    if (genre !== '전체') l = l.filter(p => p.genres.includes(genre));
    if (day !== 'all') l = l.filter(p => p.dateISO === day);
    if ((filterValue.areas || []).length) l = l.filter(p => filterValue.areas.includes(p.area));
    if ((filterValue.genres || []).length) l = l.filter(p => p.genres.some(g => filterValue.genres.includes(g)));
    const cmp = {
      recent:    (a, b) => new Date(b.dateISO) - new Date(a.dateISO),
      popular:   (a, b) => (b.going || 0) - (a.going || 0),
      priceAsc:  (a, b) => (a.price || 0) - (b.price || 0),
      priceDesc: (a, b) => (b.price || 0) - (a.price || 0),
      response:  (a, b) => (venueOf(a)?.responseMin ?? 99) - (venueOf(b)?.responseMin ?? 99),
    }[sortBy] || ((a, b) => a.distance - b.distance);
    return l.sort(cmp);
  }, [genre, day, filterValue, sortBy]);

  const filterCount = (filterValue.areas || []).length + (filterValue.genres || []).length;
  const toggleCompare = (p) => {
    setCompareItems(items => {
      if (items.find(x => x.id === p.id)) return items.filter(x => x.id !== p.id);
      if (items.length >= 3) return items;
      return [...items, p];
    });
  };

  // 헤더 (항상 그림 — 가벼움)
  const header = (
    <>
      <CPTopBar onSearch={onSearch} onNotif={onNotif} />
      <div style={{ padding: '14px 16px 10px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 10 }}>
          <h1 style={{ margin: 0, fontSize: 22, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em', lineHeight: 1.05 }}>
            이번 주 라인업
            <span style={{ fontSize: 10, fontWeight: 700, color: 'rgba(95,224,255,0.85)', letterSpacing: '0.12em', marginLeft: 8, textTransform: 'uppercase' }}>PARTY</span>
          </h1>
          <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', fontWeight: 600, fontFamily: 'JetBrains Mono, monospace', whiteSpace: 'nowrap' }}>{ready ? `${list.length} parties` : '로딩...'}</div>
        </div>
      </div>
    </>
  );

  if (!ready) {
    return (
      <div style={{ paddingBottom: 32 }}>
        {header}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, padding: '0 16px' }}>
          {[0,1,2].map(i => (
            <div key={i} style={{ height: 88, borderRadius: 14, background: 'rgba(21,21,30,0.6)', border: '1px solid rgba(255,255,255,0.04)' }} />
          ))}
        </div>
      </div>
    );
  }

  return (
    <div style={{ paddingBottom: compareMode ? 230 : 32 }}>
      {header}

      {/* 검색바 */}
      <div style={{ padding: '0 16px 12px' }}>
        <button onClick={onSearch} style={{
          all: 'unset', cursor: 'pointer', width: '100%',
          display: 'flex', gap: 10, alignItems: 'center',
          padding: '13px 14px', borderRadius: 12,
          background: '#15151E', border: '1px solid rgba(255,255,255,0.08)', boxSizing: 'border-box',
        }}>
          <Icon name="search" size={16} color="rgba(255,255,255,0.5)" />
          <span style={{ flex: 1, color: 'rgba(255,255,255,0.4)', fontSize: 13 }}>DJ, 클럽, 파티 검색</span>
        </button>
      </div>

      {/* 요일 탭 */}
      <div style={{ padding: '0 16px 12px', display: 'flex', gap: 6 }}>
        {days.map(d => (
          <button key={d.id} onClick={() => setDay(d.id)} style={{
            all: 'unset', cursor: 'pointer', flex: 1, textAlign: 'center',
            padding: '10px 0', borderRadius: 10,
            background: day === d.id ? '#1FD2FF' : 'rgba(255,255,255,0.06)',
            color: day === d.id ? '#07070A' : '#fff',
            fontSize: 11, fontWeight: 800, letterSpacing: '0.04em',
          }}>{d.label}</button>
        ))}
      </div>

      {/* 장르 칩 */}
      <div style={{ display: 'flex', gap: 8, overflowX: 'auto', padding: '0 16px 14px' }}>
        {genres.map(f => (
          <button key={f} onClick={() => setGenre(f)} style={{
            all: 'unset', cursor: 'pointer', padding: '8px 14px', borderRadius: 999,
            background: genre === f ? '#1FD2FF' : 'rgba(255,255,255,0.06)',
            color: genre === f ? '#07070A' : 'rgba(255,255,255,0.72)',
            fontSize: 11, fontWeight: 800, letterSpacing: '0.04em',
            whiteSpace: 'nowrap', textTransform: 'uppercase',
          }}>{f}</button>
        ))}
      </div>

      {/* 툴바: 필터 / 정렬 / 지도 / 비교 */}
      <ListBar
        active={{ filter: filterCount > 0, sort: sortBy !== 'recent', map: mapMode, compare: compareMode }}
        count={{ filter: filterCount }}
        onFilter={() => setFilterOpen(true)}
        onSort={() => setSortOpen(true)}
        onMap={() => setMapMode(m => !m)}
        onCompare={() => { setCompareMode(m => !m); if (compareMode) setCompareItems([]); }}
      />

      {mapMode ? (
        <MapView
          items={list}
          getPin={(p) => ({ id: p.id, label: p.title, color: p.glow === 'magenta' ? '#FF1077' : p.glow === 'cyan' ? '#1FD2FF' : '#7B49FF' })}
          onSelect={onOpenParty}
        />
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10, padding: '0 16px' }}>
          {list.length === 0 ? (
            <div style={{ padding: '40px 0', textAlign: 'center', color: 'rgba(255,255,255,0.4)' }}>
              <div style={{ fontSize: 13, fontWeight: 600 }}>맞는 파티가 없어</div>
              <div style={{ marginTop: 4, fontSize: 11 }}>필터를 다시 시도해봐</div>
            </div>
          ) : list.map(p => (
            <div key={p.id} style={{ contentVisibility: 'auto', containIntrinsicSize: 'auto 120px' }}>
              <CompareWrapRow
                mode={compareMode}
                selected={!!compareItems.find(x => x.id === p.id)}
                onToggle={() => toggleCompare(p)}
              >
                <PartyRow party={p} onOpen={compareMode ? () => toggleCompare(p) : onOpenParty} />
              </CompareWrapRow>
            </div>
          ))}
        </div>
      )}

      {filterOpen && <FilterSheet
        areas={allAreas} genres={allGenres}
        value={filterValue}
        onChange={setFilterValue}
        onClear={() => setFilterValue({ areas: [], genres: [] })}
        onClose={() => setFilterOpen(false)}
      />}
      {sortOpen && <SortSheet value={sortBy} onChange={setSortBy} onClose={() => setSortOpen(false)} />}
      {compareMode && <CompareTray
        items={compareItems} max={3}
        onRemove={(p) => setCompareItems(items => items.filter(x => x.id !== p.id))}
        onClear={() => setCompareItems([])}
        onCompare={() => setCompareSheetOpen(true)}
        onClose={() => { setCompareMode(false); setCompareItems([]); }}
      />}
      {compareSheetOpen && <CompareSheet
        items={compareItems}
        eyebrow="VS · 파티 비교"
        title="라인업 비교"
        rows={[
          { label: '날짜',    render: (it) => it.date || it.dateISO },
          { label: '베뉴',    render: (it) => it.venue },
          { label: '지역',    render: (it) => it.area },
          { label: '장르',    render: (it) => (it.genres || []).join(' · ') },
          { label: '예약',    render: (it) => `${venueOf(it)?.responseMin ?? '—'}분 응답` },
          { label: '입장료',  render: (it) => it.price ? `₩${it.price.toLocaleString()}` : '무료' },
          { label: '거리',    render: (it) => `${it.distance}km` },
        ]}
        onClose={() => setCompareSheetOpen(false)}
      />}
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

// CLUB SCREEN — minimal navigation hub. Connects to home & detail pages.
// CLUB SCREEN — full features (filter/sort/map/compare/grid) but light via
// deferred mount + content-visibility on cards. Aurora removed for perf.
function ClubScreen({ onOpenClub, onSearch, onNotif, onTab }) {
  const ready = useDeferredReady();
  const data = window.CP_DATA;
  const [filterValue, setFilterValue] = useStateP({ areas: [], genres: [] });
  const [sortBy, setSortBy] = useStateP('recent');
  const [filterOpen, setFilterOpen] = useStateP(false);
  const [sortOpen, setSortOpen] = useStateP(false);
  const [mapMode, setMapMode] = useStateP(false);
  const [compareMode, setCompareMode] = useStateP(false);
  const [compareItems, setCompareItems] = useStateP([]);
  const [compareSheetOpen, setCompareSheetOpen] = useStateP(false);

  const allAreas = data ? Array.from(new Set(data.clubs.map(c => c.area))) : [];
  const allGenres = data ? Array.from(new Set(data.clubs.flatMap(c => c.genres))) : [];

  const list = useMemoP(() => {
    if (!data) return [];
    let l = data.clubs.slice();
    if ((filterValue.areas || []).length) l = l.filter(c => filterValue.areas.includes(c.area));
    if ((filterValue.genres || []).length) l = l.filter(c => c.genres.some(g => filterValue.genres.includes(g)));
    const cmp = {
      recent:    (a, b) => new Date(b.updatedAt) - new Date(a.updatedAt),
      popular:   (a, b) => (b.popularity || 0) - (a.popularity || 0),
      priceAsc:  (a, b) => (a.priceLevel || 0) - (b.priceLevel || 0),
      priceDesc: (a, b) => (b.priceLevel || 0) - (a.priceLevel || 0),
      response:  (a, b) => (a.responseMin || 99) - (b.responseMin || 99),
    }[sortBy] || ((a, b) => (a.distance || 0) - (b.distance || 0));
    return l.sort(cmp);
  }, [filterValue, sortBy]);

  const filterCount = (filterValue.areas || []).length + (filterValue.genres || []).length;
  const toggleCompare = (c) => {
    setCompareItems(items => {
      if (items.find(x => x.id === c.id)) return items.filter(x => x.id !== c.id);
      if (items.length >= 3) return items;
      return [...items, c];
    });
  };

  // 헤더 (Aurora 제거 — GPU 부담 줄임)
  const header = (
    <>
      <CPTopBar onSearch={onSearch} onNotif={onNotif} />
      <div style={{ padding: '14px 16px 10px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 10 }}>
          <h1 style={{ margin: 0, fontSize: 22, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em', lineHeight: 1.05 }}>
            서울의 플로어
            <span style={{ fontSize: 10, fontWeight: 700, color: 'rgba(160,122,255,0.85)', letterSpacing: '0.12em', marginLeft: 8, textTransform: 'uppercase' }}>CLUB</span>
          </h1>
          <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', fontWeight: 600, fontFamily: 'JetBrains Mono, monospace', whiteSpace: 'nowrap' }}>{ready ? `${list.length} clubs` : '로딩...'}</div>
        </div>
      </div>
    </>
  );

  if (!ready) {
    return (
      <div style={{ paddingBottom: 32 }}>
        {header}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, padding: '0 16px' }}>
          {[0,1,2,3].map(i => (
            <div key={i} style={{ height: 200, borderRadius: 16, background: 'rgba(21,21,30,0.6)', border: '1px solid rgba(255,255,255,0.04)' }} />
          ))}
        </div>
      </div>
    );
  }

  return (
    <div style={{ paddingBottom: compareMode ? 230 : 32 }}>
      {header}

      {/* 툴바: 필터 / 정렬 / 지도 / 비교 */}
      <ListBar
        active={{ filter: filterCount > 0, sort: sortBy !== 'recent', map: mapMode, compare: compareMode }}
        count={{ filter: filterCount }}
        onFilter={() => setFilterOpen(true)}
        onSort={() => setSortOpen(true)}
        onMap={() => setMapMode(m => !m)}
        onCompare={() => { setCompareMode(m => !m); if (compareMode) setCompareItems([]); }}
      />

      {mapMode ? (
        <MapView
          items={list}
          getPin={(c) => ({ id: c.id, label: c.name, color: c.glow === 'magenta' ? '#FF1077' : c.glow === 'cyan' ? '#1FD2FF' : '#7B49FF' })}
          onSelect={onOpenClub}
        />
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 10, padding: '0 16px' }}>
          {list.length === 0 ? (
            <div style={{ gridColumn: '1 / -1', padding: '40px 0', textAlign: 'center', color: 'rgba(255,255,255,0.4)' }}>
              <div style={{ fontSize: 13, fontWeight: 600 }}>맞는 클럽이 없어</div>
              <div style={{ marginTop: 4, fontSize: 11 }}>필터를 다시 시도해봐</div>
            </div>
          ) : list.map(c => {
            const selected = !!compareItems.find(x => x.id === c.id);
            const card = (
              <button onClick={() => compareMode ? toggleCompare(c) : onOpenClub(c)} style={{
                all: 'unset', cursor: 'pointer', display: 'block',
                borderRadius: 16, overflow: 'hidden',
                background: '#15151E', border: '1px solid rgba(255,255,255,0.08)',
                boxShadow: '0 4px 12px rgba(0,0,0,0.55)',
              }}>
                <div style={{ position: 'relative', height: 130, background: '#0E0E14' }}>
                  <ClubThumb id={c.id} tint={c.glow} size={170} ratio={0.76} fill />
                  <FloorGlow tint={c.glow} intensity={0.30} />
                  <div style={{ position: 'absolute', top: 8, right: 8 }}>
                    <span style={{ display: 'inline-flex', gap: 3, alignItems: 'center', padding: '3px 8px', borderRadius: 999, background: 'rgba(12,12,18,0.94)', color: '#fff', fontSize: 11, fontWeight: 800 }}>
                      <Icon name="star" size={11} color="#C8FF1A" />{c.rating}
                    </span>
                  </div>
                </div>
                <div style={{ padding: '11px 12px 13px' }}>
                  <div style={{ fontSize: 14, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>{c.name}</div>
                  <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.5)', marginTop: 2 }}>{c.area}</div>
                  <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.7)', fontWeight: 600, marginTop: 6 }}>cap. {c.cap} · {c.genres.join(', ')}</div>
                </div>
              </button>
            );
            return (
              <div key={c.id} style={{ contentVisibility: 'auto', containIntrinsicSize: 'auto 240px' }}>
                <CompareWrapRow mode={compareMode} selected={selected} onToggle={() => toggleCompare(c)}>
                  {card}
                </CompareWrapRow>
              </div>
            );
          })}
        </div>
      )}

      {filterOpen && <FilterSheet
        areas={allAreas} genres={allGenres}
        value={filterValue}
        onChange={setFilterValue}
        onClear={() => setFilterValue({ areas: [], genres: [] })}
        onClose={() => setFilterOpen(false)}
      />}
      {sortOpen && <SortSheet value={sortBy} onChange={setSortBy} onClose={() => setSortOpen(false)} />}
      {compareMode && <CompareTray
        items={compareItems} max={3}
        onRemove={(c) => setCompareItems(items => items.filter(x => x.id !== c.id))}
        onClear={() => setCompareItems([])}
        onCompare={() => setCompareSheetOpen(true)}
        onClose={() => { setCompareMode(false); setCompareItems([]); }}
      />}
      {compareSheetOpen && <CompareSheet
        items={compareItems}
        eyebrow="VS · 클럽 비교"
        title="플로어 한눈에"
        rows={[
          { label: '지역',    render: (it) => it.area },
          { label: '캐파',    render: (it) => `${it.cap}명` },
          { label: '장르',    render: (it) => it.genres.join(' · ') },
          { label: '드레스',  render: (it) => it.dress },
          { label: '오픈',    render: (it) => it.openHours },
          { label: '응답',    render: (it) => `${it.responseMin}분` },
          { label: '평점',    render: (it) => `★ ${it.rating}` },
          { label: '리뷰',    render: (it) => `${it.reviews.toLocaleString()}` },
        ]}
        onClose={() => setCompareSheetOpen(false)}
      />}
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