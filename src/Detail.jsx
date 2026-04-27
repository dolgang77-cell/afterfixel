/* global React, Icon, GlowDot, Pill, GenreTag, Eyebrow, FloorGlow, OrganicLoader, GlobeLoader, CosmicScale, CalcKey, PulseBars, Aurora, Avatar, FriendStack */
const { useState: useStateD, useEffect: useEffectD } = React;

// PARTY DETAIL — full screen with lineup, RSVP (intent only — no payment).
// ClubParty connects you to clubs; payment happens at the door.
function PartyDetail({ party, onBack }) {
  if (!party) return null;
  const data = window.CP_DATA;
  const friendsGoing = data.friends.filter(f => f.going === party.id);
  const [rsvp, setRsvp] = useStateD(null); // 'going' | 'maybe' | 'pass'
  const fillPct = Math.round(party.going / party.capacity * 100);

  return (
    <div style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 84, background: '#07070A', overflowY: 'auto', zIndex: 100 }}>
      {/* Hero */}
      <div style={{ position: 'relative', height: 360, overflow: 'hidden' }}>
        <FloorGlow tint={party.glow} intensity={0.85} />
        <div style={{ position: 'absolute', inset: 0,
          background: 'radial-gradient(ellipse at 80% 20%, rgba(31,210,255,0.4), transparent 60%)' }} />
        <ClubThumb id={party.id} tint={party.glow} size={420} ratio={0.86} fill />
        <div style={{ position: 'absolute', inset: 0,
          background: 'linear-gradient(180deg, rgba(7,7,10,0.5) 0%, rgba(7,7,10,0) 30%, rgba(7,7,10,0) 60%, rgba(7,7,10,0.92) 100%)' }} />

        {/* Top bar */}
        <div style={{ position: 'absolute', top: 0, left: 0, right: 0, padding: '14px 16px', display: 'flex', justifyContent: 'space-between', zIndex: 2 }}>
          <button onClick={onBack} style={{ all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999, background: 'rgba(12,12,18,0.92)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', border: '1px solid rgba(255,255,255,0.08)' }}>
            <Icon name="chevron-left" size={18} />
          </button>
          <div style={{ display: 'flex', gap: 8 }}>
            <button style={{ all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999, background: 'rgba(12,12,18,0.92)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', border: '1px solid rgba(255,255,255,0.08)' }}>
              <Icon name="bookmark" size={16} />
            </button>
            <button style={{ all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999, background: 'rgba(12,12,18,0.92)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', border: '1px solid rgba(255,255,255,0.08)' }}>
              <Icon name="share-2" size={16} />
            </button>
          </div>
        </div>

        {/* Bottom info */}
        <div style={{ position: 'absolute', bottom: 0, left: 16, right: 16, paddingBottom: 16, zIndex: 2 }}>
          <div style={{ display: 'flex', gap: 6, marginBottom: 10 }}>
            {party.live && (
              <Pill glass bg="rgba(12,12,18,0.92)" color="#fff" style={{ fontSize: 10, fontWeight: 800, letterSpacing: '0.12em' }}>
                <PulseBars count={3} height={9} color="#FF1077" /> LIVE NOW
              </Pill>
            )}
            {party.hot && <Pill bg="#C8FF1A" color="#07070A" style={{ fontSize: 10, fontWeight: 900, letterSpacing: '0.10em' }}>PEAK TONIGHT</Pill>}
          </div>
          <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,122,184,0.95)', textTransform: 'uppercase' }}>
            {party.venue} · {party.area} · {party.distance}km
          </div>
          <h1 style={{ margin: '6px 0 4px', fontSize: 32, fontWeight: 900, letterSpacing: '-0.025em', lineHeight: 1.0, color: '#fff' }}>{party.title}</h1>
          <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.7)', fontWeight: 500 }}>{party.subtitle}</div>
        </div>
      </div>

      {/* Meta strip */}
      <div style={{ display: 'flex', gap: 8, padding: '16px', borderBottom: '1px solid rgba(255,255,255,0.06)' }}>
        <div style={{ flex: 1 }}>
          <div style={{ fontSize: 9, fontWeight: 700, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.12em' }}>DATE</div>
          <div style={{ fontSize: 13, fontWeight: 800, color: '#fff', marginTop: 2 }}>{party.date}</div>
        </div>
        <div style={{ flex: 1 }}>
          <div style={{ fontSize: 9, fontWeight: 700, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.12em' }}>TIME</div>
          <div style={{ fontSize: 13, fontWeight: 800, color: '#fff', marginTop: 2, fontFamily: 'JetBrains Mono, monospace' }}>{party.time}</div>
        </div>
        <div style={{ flex: 1 }}>
          <div style={{ fontSize: 9, fontWeight: 700, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.12em' }}>입장료(현장)</div>
          <div style={{ fontSize: 13, fontWeight: 800, color: '#fff', marginTop: 2 }}>{window.CP_FORMAT.won(party.price)}</div>
        </div>
      </div>

      {/* RSVP buttons */}
      <div style={{ padding: '18px 16px 0' }}>
        <div style={{ fontSize: 11, fontWeight: 700, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: 10 }}>당신은?</div>
        <div style={{ display: 'flex', gap: 8 }}>
          <CalcKey color={rsvp === 'going' ? 'magenta' : 'glass'} pressed={rsvp === 'going'} glow={rsvp === 'going'} height={52} width="100%" style={{ flex: 1 }} onClick={() => setRsvp('going')}>
            <span style={{ fontSize: 13, fontWeight: 900 }}>갈래 · Going</span>
          </CalcKey>
          <CalcKey color={rsvp === 'maybe' ? 'cyan' : 'glass'} pressed={rsvp === 'maybe'} height={52} width="100%" style={{ flex: 1 }} onClick={() => setRsvp('maybe')}>
            <span style={{ fontSize: 13, fontWeight: 900 }}>고민중</span>
          </CalcKey>
          <CalcKey color={rsvp === 'pass' ? 'ink' : 'glass'} pressed={rsvp === 'pass'} height={52} width="100%" style={{ flex: 1 }} onClick={() => setRsvp('pass')}>
            <span style={{ fontSize: 13, fontWeight: 900, opacity: 0.8 }}>안갈래</span>
          </CalcKey>
        </div>
      </div>

      {/* Capacity bar */}
      <div style={{ padding: '20px 16px 0' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 8 }}>
          <span style={{ fontSize: 11, fontWeight: 700, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.12em', textTransform: 'uppercase' }}>플로어 · CAPACITY</span>
          <span style={{ fontSize: 12, fontWeight: 800, color: '#fff', fontFamily: 'JetBrains Mono, monospace' }}>{party.going} / {party.capacity}</span>
        </div>
        <div style={{ height: 8, borderRadius: 999, background: 'rgba(255,255,255,0.06)', overflow: 'hidden' }}>
          <div style={{
            width: fillPct + '%', height: '100%',
            background: fillPct > 80 ? 'linear-gradient(90deg, #FFB020, #FF1077)' : 'linear-gradient(90deg, #1FD2FF, #7B49FF)',
            boxShadow: '0 0 12px rgba(255,16,119,0.6)',
            borderRadius: 999,
          }} />
        </div>
        <div style={{ fontSize: 11, color: fillPct > 80 ? '#FFB020' : 'rgba(255,255,255,0.55)', fontWeight: 600, marginTop: 6 }}>
          {fillPct > 80 ? '거의 매진 · Almost full' : `${fillPct}% 채워졌어`}
        </div>
      </div>

      {/* Tags */}
      <div style={{ padding: '20px 16px 0', display: 'flex', flexWrap: 'wrap', gap: 6 }}>
        {party.tags.map(t => (
          <span key={t} style={{ padding: '5px 10px', borderRadius: 999, background: 'rgba(255,255,255,0.06)', color: 'rgba(255,255,255,0.7)', fontSize: 10, fontWeight: 700, letterSpacing: '0.10em' }}>{t}</span>
        ))}
      </div>

      {/* Description */}
      <div style={{ padding: '20px 16px 0' }}>
        <div style={{ fontSize: 14, color: 'rgba(255,255,255,0.8)', lineHeight: 1.55, fontWeight: 500 }}>{party.description}</div>
      </div>

      {/* Lineup */}
      <div style={{ padding: '24px 16px 0' }}>
        <h2 style={{ margin: '0 0 12px', fontSize: 17, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>라인업 · Lineup</h2>
        <div style={{ borderRadius: 16, overflow: 'hidden', background: '#15151E', border: '1px solid rgba(255,255,255,0.06)' }}>
          {party.lineup.map((dj, i, a) => (
            <div key={dj.name} style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '14px 14px', borderBottom: i < a.length - 1 ? '1px solid rgba(255,255,255,0.06)' : 'none' }}>
              <Avatar color={['#FF1077','#1FD2FF','#7B49FF','#C8FF1A'][i % 4]} size={42} label={dj.name[0]} />
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', gap: 6, alignItems: 'center' }}>
                  <span style={{ fontSize: 14, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>{dj.name}</span>
                  {dj.resident && <span style={{ fontSize: 9, fontWeight: 800, color: '#C8FF1A', letterSpacing: '0.10em', padding: '2px 6px', borderRadius: 4, background: 'rgba(200,255,26,0.12)' }}>RESIDENT</span>}
                </div>
                <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.55)', fontWeight: 600, fontFamily: 'JetBrains Mono, monospace', marginTop: 2 }}>{dj.set}</div>
              </div>
              <button style={{ all: 'unset', cursor: 'pointer', width: 32, height: 32, borderRadius: 999, background: 'rgba(255,255,255,0.06)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff' }}>
                <Icon name="play" size={12} />
              </button>
            </div>
          ))}
        </div>
      </div>

      {/* Friends going */}
      {friendsGoing.length > 0 && (
        <div style={{ padding: '24px 16px 0' }}>
          <h2 style={{ margin: '0 0 12px', fontSize: 17, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>친구 {friendsGoing.length}명 가는중</h2>
          <div style={{ display: 'flex', gap: 10, overflowX: 'auto' }}>
            {friendsGoing.map(f => (
              <div key={f.id} style={{ minWidth: 110, padding: 12, borderRadius: 14, background: '#15151E', border: '1px solid rgba(255,255,255,0.06)', textAlign: 'center' }}>
                <Avatar color={f.avatar} size={48} label={f.name[0]} />
                <div style={{ fontSize: 12, fontWeight: 800, color: '#fff', marginTop: 8 }}>{f.name}</div>
                <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.5)' }}>{f.handle}</div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Sticky action bar — 'connect' actions only, no checkout. */}
      <div style={{ height: 96 }} />
      <div style={{ position: 'sticky', bottom: 0, padding: 14, background: 'linear-gradient(180deg, rgba(7,7,10,0) 0%, #07070A 30%)' }}>
        <div style={{ background: 'rgba(14,14,20,0.95)', border: '1px solid rgba(255,255,255,0.10)', borderRadius: 18, padding: 10, display: 'flex', gap: 8, alignItems: 'center' }}>
          <button style={{ all: 'unset', cursor: 'pointer', flex: 1, height: 50, borderRadius: 14, background: 'rgba(31,210,255,0.12)', border: '1px solid rgba(31,210,255,0.32)', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 6, color: '#9CF0FF', fontSize: 12, fontWeight: 800 }}>
            <Icon name="map-pin" size={14} color="#9CF0FF"/> 길찾기
          </button>
          <button style={{ all: 'unset', cursor: 'pointer', flex: 1, height: 50, borderRadius: 14, background: 'rgba(123,73,255,0.14)', border: '1px solid rgba(123,73,255,0.32)', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 6, color: '#C9B3FF', fontSize: 12, fontWeight: 800 }}>
            <Icon name="route" size={14} color="#C9B3FF"/> 루트추가
          </button>
          <button style={{ all: 'unset', cursor: 'pointer', flex: 1, height: 50, borderRadius: 14, background: 'rgba(255,16,119,0.16)', border: '1px solid rgba(255,16,119,0.34)', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 6, color: '#FFB8DA', fontSize: 12, fontWeight: 800 }}>
            <Icon name="share-2" size={14} color="#FFB8DA"/> 친구초대
          </button>
        </div>
      </div>
    </div>
  );
}
window.PartyDetail = PartyDetail;

// CLUB DETAIL
function ClubDetail({ club, onBack, onOpenParty }) {
  if (!club) return null;
  const data = window.CP_DATA;
  const partiesAt = data.parties.filter(p => p.venue === club.name);
  const reviews = data.reviews.filter(r => r.club === club.id);

  return (
    <div style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 84, background: '#07070A', overflowY: 'auto', zIndex: 100 }}>
      <div style={{ position: 'relative', height: 320, overflow: 'hidden' }}>
        <ClubThumb id={club.id} tint={club.glow} size={420} ratio={0.76} fill />
        <FloorGlow tint={club.glow} intensity={0.45} />
        <div style={{ position: 'absolute', inset: 0, background: 'linear-gradient(180deg, rgba(7,7,10,0.5) 0%, transparent 30%, transparent 60%, rgba(7,7,10,0.92) 100%)' }} />

        <div style={{ position: 'absolute', top: 0, left: 0, right: 0, padding: '14px 16px', display: 'flex', justifyContent: 'space-between' }}>
          <button onClick={onBack} style={{ all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999, background: 'rgba(12,12,18,0.92)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', border: '1px solid rgba(255,255,255,0.08)' }}>
            <Icon name="chevron-left" size={18} />
          </button>
          <button style={{ all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999, background: 'rgba(12,12,18,0.92)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', border: '1px solid rgba(255,255,255,0.08)' }}>
            <Icon name="bookmark" size={16} />
          </button>
        </div>

        <div style={{ position: 'absolute', bottom: 0, left: 16, right: 16, paddingBottom: 16 }}>
          <Eyebrow color="rgba(160,122,255,0.95)">CLUB · {club.area.split(' ').slice(-1)}</Eyebrow>
          <h1 style={{ margin: '4px 0 6px', fontSize: 36, fontWeight: 900, letterSpacing: '-0.025em', lineHeight: 1.0, color: '#fff' }}>{club.name}</h1>
          <div style={{ display: 'flex', gap: 12, alignItems: 'center', fontSize: 12, color: 'rgba(255,255,255,0.7)', fontWeight: 600 }}>
            <span style={{ display: 'inline-flex', gap: 3, alignItems: 'center' }}><Icon name="star" size={12} color="#C8FF1A" /> {club.rating} · {club.reviews}</span>
            <span style={{ color: 'rgba(255,255,255,0.3)' }}>·</span>
            <span>cap. {club.cap}</span>
            <span style={{ color: 'rgba(255,255,255,0.3)' }}>·</span>
            <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{club.openHours}</span>
          </div>
        </div>
      </div>

      {/* Address & quick actions */}
      <div style={{ padding: '16px', display: 'flex', gap: 8 }}>
        <CalcKey color="cyan" height={44} width="100%" style={{ flex: 1 }}>
          <span style={{ display: 'inline-flex', gap: 6, alignItems: 'center', fontSize: 12, fontWeight: 800 }}>
            <Icon name="map-pin" size={13}/> 길찾기
          </span>
        </CalcKey>
        <CalcKey color="violet" height={44} width="100%" style={{ flex: 1 }}>
          <span style={{ display: 'inline-flex', gap: 6, alignItems: 'center', fontSize: 12, fontWeight: 800 }}>
            <Icon name="route" size={13}/> 루트추가
          </span>
        </CalcKey>
        <CalcKey color="glass" height={44} width="100%" style={{ flex: 1 }}>
          <span style={{ display: 'inline-flex', gap: 6, alignItems: 'center', fontSize: 12, fontWeight: 800, color: '#fff' }}>
            <Icon name="phone" size={13}/> 전화
          </span>
        </CalcKey>
      </div>

      {/* Info grid */}
      <div style={{ padding: '0 16px' }}>
        <div style={{ borderRadius: 14, background: '#15151E', border: '1px solid rgba(255,255,255,0.06)', overflow: 'hidden' }}>
          {[
            { label: '주소', value: club.addr, icon: 'map-pin' },
            { label: '드레스코드', value: club.dress, icon: 'shirt' },
            { label: '음악', value: club.music.join(' · '), icon: 'music-2' },
            { label: '운영시간', value: club.openHours, icon: 'clock', mono: true },
          ].map((row, i, a) => (
            <div key={row.label} style={{ display: 'flex', gap: 12, padding: 14, borderBottom: i < a.length - 1 ? '1px solid rgba(255,255,255,0.06)' : 'none' }}>
              <Icon name={row.icon} size={15} color="rgba(255,255,255,0.45)" />
              <div style={{ flex: 1 }}>
                <div style={{ fontSize: 10, fontWeight: 700, color: 'rgba(255,255,255,0.45)', letterSpacing: '0.10em', textTransform: 'uppercase' }}>{row.label}</div>
                <div style={{ fontSize: 13, color: '#fff', fontWeight: 600, marginTop: 2, fontFamily: row.mono ? 'JetBrains Mono, monospace' : 'inherit' }}>{row.value}</div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Upcoming nights at this club */}
      {partiesAt.length > 0 && (
        <div style={{ padding: '24px 16px 0' }}>
          <h2 style={{ margin: '0 0 12px', fontSize: 16, fontWeight: 800, color: '#fff' }}>다가오는 밤</h2>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            {partiesAt.map(p => (
              <button key={p.id} onClick={() => onOpenParty(p)} style={{
                all: 'unset', cursor: 'pointer', display: 'flex', gap: 12, padding: 12,
                borderRadius: 12, background: '#15151E', border: '1px solid rgba(255,255,255,0.06)', alignItems: 'center',
              }}>
                <div style={{ width: 50, height: 50, borderRadius: 10, position: 'relative', overflow: 'hidden', background: '#0E0E14', flexShrink: 0 }}>
                  <ClubThumb id={p.id} tint={p.glow} size={50} ratio={1} fill />
                  <FloorGlow tint={p.glow} intensity={0.4}/>
                </div>
                <div style={{ flex: 1 }}>
                  <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{p.title}</div>
                  <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', fontWeight: 500, marginTop: 2 }}>{p.date} · <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{p.time}</span></div>
                </div>
                <Icon name="chevron-right" size={14} color="rgba(255,255,255,0.4)" />
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Reviews */}
      <div style={{ padding: '24px 16px 80px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 12 }}>
          <h2 style={{ margin: 0, fontSize: 16, fontWeight: 800, color: '#fff' }}>후기 · {club.reviews}</h2>
          <button style={{ all: 'unset', cursor: 'pointer', fontSize: 11, color: '#1FD2FF', fontWeight: 700 }}>리뷰 쓰기 →</button>
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {reviews.length > 0 ? reviews.map(r => (
            <div key={r.id} style={{ padding: 14, borderRadius: 14, background: '#15151E', border: '1px solid rgba(255,255,255,0.06)' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                <span style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{r.author}</span>
                <div style={{ display: 'flex', gap: 1 }}>
                  {[1,2,3,4,5].map(s => <Icon key={s} name="star" size={10} color={s <= r.rating ? '#C8FF1A' : 'rgba(255,255,255,0.15)'} />)}
                </div>
              </div>
              <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.7)', marginTop: 6, lineHeight: 1.5, fontWeight: 500 }}>{r.body}</div>
              <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.35)', marginTop: 8, fontFamily: 'JetBrains Mono, monospace' }}>{r.date}</div>
            </div>
          )) : (
            <div style={{ padding: 24, textAlign: 'center', color: 'rgba(255,255,255,0.4)', fontSize: 12 }}>아직 후기가 없어요</div>
          )}
        </div>
      </div>
    </div>
  );
}
window.ClubDetail = ClubDetail;

// SEARCH MODAL
function SearchModal({ onClose, onOpenParty, onOpenClub }) {
  const data = window.CP_DATA;
  const [q, setQ] = useStateD('');
  const [recent] = useStateD(['Honey Dijon', 'Faust', 'TECHNO 청담', 'vurt.']);

  const matches = q.trim() === '' ? null : {
    parties: data.parties.filter(p =>
      p.title.toLowerCase().includes(q.toLowerCase()) ||
      p.venue.toLowerCase().includes(q.toLowerCase()) ||
      p.lineup.some(d => d.name.toLowerCase().includes(q.toLowerCase()))
    ),
    clubs: data.clubs.filter(c =>
      c.name.toLowerCase().includes(q.toLowerCase()) ||
      c.area.toLowerCase().includes(q.toLowerCase())
    ),
  };

  return (
    <div style={{ position: 'absolute', inset: 0, background: '#07070A', zIndex: 150, display: 'flex', flexDirection: 'column' }}>
      {/* Search top */}
      <div style={{ padding: '50px 16px 12px', display: 'flex', gap: 10, alignItems: 'center', borderBottom: '1px solid rgba(255,255,255,0.06)', background: '#0E0E14' }}>
        <div style={{ flex: 1, display: 'flex', gap: 10, alignItems: 'center', padding: '12px 14px', borderRadius: 12, background: '#15151E', border: '1px solid rgba(255,255,255,0.08)' }}>
          <Icon name="search" size={16} color="#FF7AB8" />
          <input
            autoFocus
            value={q}
            onChange={e => setQ(e.target.value)}
            placeholder="DJ, 클럽, 파티 검색"
            style={{
              flex: 1, all: 'unset', color: '#fff', fontSize: 14, fontWeight: 600, fontFamily: 'Pretendard, system-ui',
            }}
          />
          {q && (
            <button onClick={() => setQ('')} style={{ all: 'unset', cursor: 'pointer', color: 'rgba(255,255,255,0.4)' }}>
              <Icon name="x" size={14}/>
            </button>
          )}
        </div>
        <button onClick={onClose} style={{ all: 'unset', cursor: 'pointer', fontSize: 13, fontWeight: 700, color: '#FF7AB8' }}>취소</button>
      </div>

      <div style={{ flex: 1, overflowY: 'auto', padding: '0 16px 16px' }}>
        {!matches && (
          <>
            <div style={{ padding: '20px 0 12px' }}>
              <h3 style={{ margin: 0, fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase' }}>최근 검색</h3>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 4 }}>
              {recent.map(r => (
                <button key={r} onClick={() => setQ(r)} style={{ all: 'unset', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: 10, padding: '12px 0', borderBottom: '1px solid rgba(255,255,255,0.04)' }}>
                  <Icon name="clock" size={14} color="rgba(255,255,255,0.4)" />
                  <span style={{ flex: 1, fontSize: 13, color: '#fff' }}>{r}</span>
                  <Icon name="arrow-up-left" size={13} color="rgba(255,255,255,0.3)" />
                </button>
              ))}
            </div>

            <div style={{ padding: '24px 0 12px' }}>
              <h3 style={{ margin: 0, fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase' }}>지금 트렌드</h3>
            </div>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
              {['#Peggy Gou', '#청담', '#TECHNO', '#All-nighter', '#vurt.', '#보일러룸'].map(t => (
                <span key={t} style={{ padding: '7px 12px', borderRadius: 999, background: 'rgba(255,255,255,0.06)', color: '#fff', fontSize: 12, fontWeight: 700 }}>{t}</span>
              ))}
            </div>
          </>
        )}

        {matches && (
          <>
            {matches.parties.length > 0 && (
              <div style={{ padding: '16px 0 0' }}>
                <h3 style={{ margin: '0 0 10px', fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,122,184,0.85)', textTransform: 'uppercase' }}>파티 · {matches.parties.length}</h3>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                  {matches.parties.map(p => (
                    <button key={p.id} onClick={() => { onOpenParty(p); onClose(); }} style={{ all: 'unset', cursor: 'pointer', display: 'flex', gap: 10, padding: 10, borderRadius: 10, background: '#15151E', alignItems: 'center' }}>
                      <div style={{ width: 36, height: 36, borderRadius: 8, position: 'relative', overflow: 'hidden', background: '#0E0E14' }}>
                        <ClubThumb id={p.id} tint={p.glow} size={36} ratio={1} fill />
                        <FloorGlow tint={p.glow} intensity={0.4}/>
                      </div>
                      <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{p.title}</div>
                        <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)' }}>{p.venue} · {p.date}</div>
                      </div>
                      <Icon name="chevron-right" size={13} color="rgba(255,255,255,0.4)" />
                    </button>
                  ))}
                </div>
              </div>
            )}
            {matches.clubs.length > 0 && (
              <div style={{ padding: '16px 0 0' }}>
                <h3 style={{ margin: '0 0 10px', fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(160,122,255,0.85)', textTransform: 'uppercase' }}>클럽 · {matches.clubs.length}</h3>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                  {matches.clubs.map(c => (
                    <button key={c.id} onClick={() => { onOpenClub(c); onClose(); }} style={{ all: 'unset', cursor: 'pointer', display: 'flex', gap: 10, padding: 10, borderRadius: 10, background: '#15151E', alignItems: 'center' }}>
                      <div style={{ width: 36, height: 36, borderRadius: 8, position: 'relative', overflow: 'hidden', background: '#0E0E14' }}>
                        <ClubThumb id={c.id} tint={c.glow} size={36} ratio={1} fill />
                        <FloorGlow tint={c.glow} intensity={0.4}/>
                      </div>
                      <div style={{ flex: 1 }}>
                        <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{c.name}</div>
                        <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)' }}>{c.area} · ★ {c.rating}</div>
                      </div>
                      <Icon name="chevron-right" size={13} color="rgba(255,255,255,0.4)" />
                    </button>
                  ))}
                </div>
              </div>
            )}
            {matches.parties.length === 0 && matches.clubs.length === 0 && (
              <div style={{ padding: 40, textAlign: 'center', color: 'rgba(255,255,255,0.4)' }}>
                <OrganicLoader size={70} tint="cyan" />
                <div style={{ marginTop: 12, fontSize: 13, fontWeight: 600 }}>찾는 게 없어</div>
                <div style={{ marginTop: 4, fontSize: 11 }}>Try another keyword</div>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
window.SearchModal = SearchModal;

// SPLASH SCREEN with organic loader
function SplashScreen({ onDone }) {
  useEffectD(() => {
    const id = setTimeout(onDone, 1800);
    return () => clearTimeout(id);
  }, []);
  return (
    <div style={{ position: 'absolute', inset: 0, background: '#07070A', display: 'flex', alignItems: 'center', justifyContent: 'center', flexDirection: 'column', zIndex: 500 }}>
      <Aurora intensity={1} />
      <div style={{ position: 'relative', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
        <OrganicLoader size={200} tint="magenta" />
      </div>
      <div style={{ marginTop: 20, fontSize: 36, fontWeight: 900, letterSpacing: '-0.025em', background: 'linear-gradient(135deg,#FF1077 0%,#7B49FF 50%,#1FD2FF 100%)', WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent' }}>클럽파티</div>
      <div style={{ marginTop: 6, fontSize: 11, fontWeight: 700, letterSpacing: '0.20em', color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase' }}>FIND TONIGHT</div>
    </div>
  );
}
window.SplashScreen = SplashScreen;

// NOTIFICATIONS PANEL
function NotificationsPanel({ onClose, onOpenParty }) {
  const data = window.CP_DATA;
  const notifs = [
    { id: 'n1', type: 'live', icon: 'flame', tint: 'magenta', title: 'REVERB Vol.12 진행중', body: 'Peggy Gou 곧 들어가 · 02:00 set', time: '5min ago', party: 'p1' },
    { id: 'n2', type: 'friend', icon: 'users', tint: 'cyan', title: 'Min이 갈래 했어', body: 'PISTIL — DEEP HOUSE NIGHT', time: '12min ago', party: 'p2' },
    { id: 'n3', type: 'sale', icon: 'tag', tint: 'lime', title: '90% 매진 임박', body: 'CAKESHOP × Boiler Room', time: '38min ago', party: 'p4' },
    { id: 'n4', type: 'system', icon: 'bell', tint: 'violet', title: '새 클럽 오픈', body: 'Volnost · 합정에 새 플로어', time: '2h ago' },
  ];
  return (
    <div style={{ position: 'absolute', inset: 0, zIndex: 150, display: 'flex', flexDirection: 'column' }}>
      <div onClick={onClose} style={{ position: 'absolute', inset: 0, background: 'rgba(0,0,0,0.5)' }} />
      <div style={{ position: 'relative', marginTop: 50, marginLeft: 12, marginRight: 12, background: '#0E0E14', borderRadius: 22, border: '1px solid rgba(255,255,255,0.10)', overflow: 'hidden', boxShadow: '0 24px 64px rgba(0,0,0,0.7)' }}>
        <div style={{ padding: 16, display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '1px solid rgba(255,255,255,0.06)' }}>
          <div>
            <Eyebrow color="rgba(255,122,184,0.9)">알림 · 4 NEW</Eyebrow>
            <div style={{ fontSize: 18, fontWeight: 900, color: '#fff', marginTop: 2 }}>오늘 밤의 신호</div>
          </div>
          <button onClick={onClose} style={{ all: 'unset', cursor: 'pointer', width: 32, height: 32, borderRadius: 999, background: 'rgba(255,255,255,0.06)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff' }}>
            <Icon name="x" size={14} />
          </button>
        </div>
        <div style={{ maxHeight: 460, overflowY: 'auto' }}>
          {notifs.map(n => {
            const tintColor = { magenta: '#FF1077', cyan: '#1FD2FF', violet: '#7B49FF', lime: '#C8FF1A' }[n.tint];
            const onClick = n.party ? () => { const p = data.parties.find(x => x.id === n.party); if (p) { onOpenParty(p); onClose(); } } : undefined;
            return (
              <button key={n.id} onClick={onClick} style={{ all: 'unset', cursor: onClick ? 'pointer' : 'default', display: 'flex', gap: 12, padding: 14, borderBottom: '1px solid rgba(255,255,255,0.04)', alignItems: 'flex-start', width: '100%', boxSizing: 'border-box' }}>
                <div style={{ width: 36, height: 36, borderRadius: 10, background: `${tintColor}22`, border: `1px solid ${tintColor}55`, display: 'flex', alignItems: 'center', justifyContent: 'center', color: tintColor, flexShrink: 0 }}>
                  <Icon name={n.icon} size={16} />
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{n.title}</div>
                  <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', marginTop: 2 }}>{n.body}</div>
                  <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.35)', marginTop: 4, fontFamily: 'JetBrains Mono, monospace' }}>{n.time}</div>
                </div>
              </button>
            );
          })}
        </div>
      </div>
    </div>
  );
}
window.NotificationsPanel = NotificationsPanel;
