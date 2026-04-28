/* global React, Icon, GlowDot, Pill, GenreTag, Eyebrow, FloorGlow, OrganicLoader, GlobeLoader, CosmicScale, CalcKey, PulseBars, Aurora */
const { useState: useStateH, useEffect: useEffectH } = React;

function Avatar({ color = '#FF1077', size = 28, label = '' }) {
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
      width: size, height: size, borderRadius: 999,
      background: `linear-gradient(135deg, ${color} 0%, rgba(0,0,0,0.4) 100%)`,
      border: '1.5px solid rgba(7,7,10,0.9)',
      color: '#fff', fontSize: size * 0.36, fontWeight: 800,
      boxShadow: `0 0 12px ${color}55`
    }}>{label}</span>);

}
window.Avatar = Avatar;

function FriendStack({ count = 4, going }) {
  const friends = window.CP_DATA.friends.slice(0, count);
  return (
    <div style={{ display: 'inline-flex', alignItems: 'center' }}>
      {friends.map((f, i) =>
      <span key={f.id} style={{ marginLeft: i ? -10 : 0, zIndex: count - i }}>
          <Avatar color={f.avatar} size={24} label={f.name[0]} />
        </span>
      )}
      {going && <span style={{ marginLeft: 8, fontSize: 12, color: 'rgba(255,255,255,0.65)', fontWeight: 600 }}>+{going - count} 친구</span>}
    </div>);

}
window.FriendStack = FriendStack;

function PartyHeroCard({ party, onOpen }) {
  return (
    <button onClick={() => onOpen(party)} style={{
      all: 'unset', cursor: 'pointer', display: 'block', width: '100%',
      borderRadius: 20, overflow: 'hidden',
      background: 'linear-gradient(180deg, #0E0E14 0%, #07070A 100%)',
      border: '1px solid rgba(255,255,255,0.10)',
      boxShadow: party.hot ?
      '0 0 32px rgba(255,16,119,0.5), 0 0 80px rgba(255,16,119,0.2), 0 8px 24px rgba(0,0,0,0.6)' :
      '0 8px 24px rgba(0,0,0,0.55)',
      position: 'relative'
    }}>
      <div style={{ position: 'relative', height: 200, overflow: 'hidden' }}>
        <FloorGlow tint={party.glow} intensity={0.7} />
        <div style={{ position: 'absolute', inset: 0,
          background: 'radial-gradient(ellipse at 80% 0%, rgba(31,210,255,0.30), transparent 55%)' }} />
        {/* Club photo thumb fills hero */}
        <ClubThumb id={party.id} tint={party.glow} size={400} ratio={0.5} fill />
        <div style={{ position: 'absolute', inset: 0,
          background: 'linear-gradient(180deg, rgba(7,7,10,0) 40%, rgba(12,12,18,0.94) 100%)', opacity: "1" }} />

        <div style={{ position: 'absolute', top: 14, left: 14, display: 'flex', gap: 6 }}>
          {party.live &&
          <Pill glass bg="rgba(12,12,18,0.92)" color="#fff" style={{ letterSpacing: '0.12em', textTransform: 'uppercase', fontWeight: 800, fontSize: 10, gap: 5 }}>
              <PulseBars count={3} height={9} color="#FF1077" /> LIVE NOW
            </Pill>
          }
          {party.hot &&
          <Pill bg="#C8FF1A" color="#07070A" style={{ fontWeight: 900, letterSpacing: '0.10em', fontSize: 10 }}>PEAK TONIGHT</Pill>
          }
        </div>
        <div style={{ position: 'absolute', top: 14, right: 14, display: 'flex', gap: 6 }}>
          <Pill glass bg="rgba(12,12,18,0.92)" color="#fff" style={{ fontSize: 10 }}>
            <Icon name="map-pin" size={11} color="#FF7AB8" /> {party.distance}km
          </Pill>
        </div>
        <div style={{ position: 'absolute', left: 16, right: 16, bottom: 14 }}>
          <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'rgba(255,122,184,0.9)' }}>
            {party.venue} · {party.area}
          </div>
          <div style={{ fontSize: 22, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em', lineHeight: 1.1, marginTop: 4 }}>{party.title}</div>
          <div style={{ fontSize: 13, color: 'rgba(255,255,255,0.7)', fontWeight: 500, marginTop: 2 }}>{party.subtitle}</div>
        </div>
      </div>
      <div style={{ padding: '14px 16px 16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div style={{ display: 'flex', gap: 10, alignItems: 'center', color: 'rgba(255,255,255,0.7)', fontSize: 12, fontWeight: 600 }}>
          <span style={{ fontFamily: 'JetBrains Mono, monospace', fontFeatureSettings: '"tnum"', color: '#fff' }}>{party.time}</span>
          <span style={{ color: 'rgba(255,255,255,0.3)' }}>·</span>
          <FriendStack count={3} going={party.friends} />
        </div>
        <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{window.CP_FORMAT.won(party.price)}</div>
      </div>
    </button>);

}
window.PartyHeroCard = PartyHeroCard;

// Compact party row
function PartyRow({ party, onOpen }) {
  return (
    <button onClick={() => onOpen(party)} style={{
      all: 'unset', cursor: 'pointer', display: 'flex', gap: 12,
      width: '100%', padding: 12, borderRadius: 14,
      background: '#15151E', border: '1px solid rgba(255,255,255,0.06)',
      alignItems: 'center', boxSizing: 'border-box'
    }}>
      <div style={{ width: 64, height: 64, borderRadius: 12, position: 'relative', overflow: 'hidden', background: '#0E0E14', flexShrink: 0 }}>
        <ClubThumb id={party.id} tint={party.glow} size={64} ratio={1} fill />
        <FloorGlow tint={party.glow} intensity={0.4} />
      </div>
      <div style={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column', gap: 3 }}>
        <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.5)' }}>{party.venue}</div>
        <div style={{ fontSize: 14, fontWeight: 800, color: '#fff', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis', letterSpacing: '-0.01em' }}>{party.title}</div>
        <div style={{ display: 'flex', gap: 8, color: 'rgba(255,255,255,0.55)', fontSize: 11, fontWeight: 600, alignItems: 'center' }}>
          <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{party.time.split(' ')[0]}</span>
          <span>·</span>
          <span>{party.distance}km</span>
        </div>
      </div>
      <div style={{ textAlign: 'right' }}>
        <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{window.CP_FORMAT.won(party.price)}</div>
        {party.live && <div style={{ fontSize: 9, fontWeight: 700, color: '#FF1077', letterSpacing: '0.12em', marginTop: 2 }}>LIVE</div>}
      </div>
    </button>);

}
window.PartyRow = PartyRow;

// Top bar with location detection (uses globe loader as decoration)
function CPTopBar({ city = '서울 · 강남구', onSearch, onNotif }) {
  return (
    <div style={{
      position: 'sticky', top: 0, zIndex: 5,
      padding: '8px 16px 8px',
      background: 'rgba(7,7,10,0.92)',
      borderBottom: '1px solid rgba(255,255,255,0.06)',
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      minHeight: 38,
    }}>
      <div style={{
        display: 'flex', alignItems: 'center', gap: 5,
        filter: 'drop-shadow(0 0 6px rgba(160,122,255,0.4))',
      }}>
        <span style={{
          width: 5, height: 5, borderRadius: 999,
          background: '#FF1077',
          boxShadow: '0 0 6px #FF1077, 0 0 12px rgba(255,16,119,0.6)',
          animation: 'cp-pulse 1.8s ease-in-out infinite',
          flexShrink: 0,
        }} />
        <span style={{
          fontSize: 14, fontWeight: 900,
          letterSpacing: '-0.01em',
          background: 'linear-gradient(135deg,#FF7AB8 0%,#A07AFF 50%,#5AE3FF 100%)',
          WebkitBackgroundClip: 'text', backgroundClip: 'text',
          WebkitTextFillColor: 'transparent', color: 'transparent',
          whiteSpace: 'nowrap',
          fontFamily: "'Pretendard', system-ui, sans-serif",
        }}>noxiahub</span>
      </div>

      <div style={{
        position: 'absolute', left: '50%', top: '50%',
        transform: 'translate(-50%, -50%)',
        display: 'flex', alignItems: 'center', gap: 6,
      }}>
        <div style={{ width: 22, height: 22, position: 'relative', flexShrink: 0 }}>
          <GlobeLoader size={22} speed={0.7} cities={false} />
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', lineHeight: 1.2 }}>
          <span style={{ fontSize: 8, fontWeight: 700, color: 'rgba(255,255,255,0.5)', letterSpacing: '0.16em', textTransform: 'uppercase' }}>위치 · LIVE</span>
          <span style={{ fontSize: 12, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em', whiteSpace: 'nowrap' }}>{city}</span>
        </div>
        <Icon name="chevron-down" size={11} color="rgba(255,255,255,0.4)" />
      </div>

      <div style={{ display: 'flex', gap: 6 }}>
        <button onClick={onSearch} style={{ all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.08)', color: '#fff' }}>
          <Icon name="search" size={17} />
        </button>
        <button onClick={onNotif} style={{ all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.08)', color: '#fff', position: 'relative' }}>
          <Icon name="bell" size={17} />
          <span style={{ position: 'absolute', top: 8, right: 8, width: 7, height: 7, borderRadius: 999, background: '#FF1077', boxShadow: '0 0 6px #FF1077' }} />
        </button>
      </div>
    </div>);

}
window.CPTopBar = CPTopBar;

// ────────────────────────────────────────────────────────────────────────────
// HOME SCREEN
// ────────────────────────────────────────────────────────────────────────────
function HomeScreen({ onOpenParty, onOpenClub, onSearch, onNotif, onOpenRoute, onTab }) {
  const data = window.CP_DATA;
  const liveOnes = data.parties.filter((p) => p.live);
  const tonight = data.parties.filter((p) => p.dateISO === '2026-04-27');
  const featured = data.parties[0];
  const [spotsOpen, setSpotsOpen] = useStateH(false);

  return (
    <div style={{ paddingBottom: 24, position: 'relative' }}>
      <CPTopBar onSearch={onSearch} onNotif={onNotif} />

      {/* HERO — full-bleed club photo with single-line headline (shorter for tighter top) */}
      <div style={{ position: 'relative', height: 170, overflow: 'hidden', marginBottom: 4 }}>
        <ClubThumb id={featured.id} tint={featured.glow} size={420} ratio={0.55} fill
          image="https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=1200&q=80" />
        {/* Tonal scrim — lay design-system tones over photo so the hero
            doesn't read as raw saturated neon. */}
        <div style={{ position: 'absolute', inset: 0,
          background: 'linear-gradient(180deg, rgba(7,7,10,0.20) 0%, rgba(7,7,10,0.55) 55%, rgba(7,7,10,0.96) 100%)' }} />
        <div style={{ position: 'absolute', inset: 0,
          background: 'radial-gradient(ellipse at 70% 20%, rgba(255,16,119,0.18), transparent 60%)' }} />

        <div style={{ position: 'absolute', left: 16, right: 16, bottom: 14, zIndex: 2 }}>
          <Eyebrow color="rgba(255,184,218,0.9)">2026·04·27 · SAT · 21:43 · 강남구</Eyebrow>
          <h1 style={{
            margin: '4px 0 4px', fontSize: 26, fontWeight: 900, letterSpacing: '-0.025em', lineHeight: 1.05,
            whiteSpace: 'nowrap', color: '#fff',
            textShadow: '0 2px 24px rgba(0,0,0,0.6)',
          }}>
            오늘 밤, <span style={{
              background: 'linear-gradient(135deg,#FF7AB8 0%,#A07AFF 50%,#5AE3FF 100%)',
              WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent',
            }}>근처에서</span>
          </h1>
          <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.62)', fontWeight: 500 }}>
            Tonight near you · <span style={{ color: '#FFB8DA', fontWeight: 700 }}>{tonight.length} parties</span>
            <span style={{ color: 'rgba(255,255,255,0.30)' }}> · </span>
            <span style={{ color: '#9CF0FF', fontWeight: 700 }}>{liveOnes.length} live now</span>
          </div>
        </div>
      </div>

      {/* Primary CTA — opens nearby spots sheet (toggle) */}
      <div style={{ padding: '4px 16px 0' }}>
        <button onClick={() => setSpotsOpen(v => !v)} style={{
          all: 'unset', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'space-between',
          width: '100%', boxSizing: 'border-box', gap: 12,
          padding: '14px 16px 14px 18px', borderRadius: 16,
          background: 'linear-gradient(135deg, rgba(255,16,119,0.92) 0%, rgba(123,73,255,0.92) 100%)',
          color: '#fff', border: '1px solid rgba(255,255,255,0.12)',
          boxShadow: '0 8px 24px rgba(255,16,119,0.28), 0 2px 6px rgba(0,0,0,0.4)',
        }}>
          <span style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <span style={{
              width: 36, height: 36, borderRadius: 10,
              background: 'rgba(7,7,10,0.35)', border: '1px solid rgba(255,255,255,0.18)',
              display: 'inline-flex', alignItems: 'center', justifyContent: 'center'
            }}>
              <Icon name="map-pin" size={17} color="#fff" />
            </span>
            <span style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
              <span style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.14em', color: 'rgba(255,255,255,0.75)' }}>
                FIND HOT SPOTS NEAR ME
              </span>
              <span style={{ fontSize: 16, fontWeight: 800, letterSpacing: '-0.01em' }}>
                지금 뜨는 밤 찾기
              </span>
            </span>
          </span>
          <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
            <PulseBars count={3} height={12} color="#fff" />
            <Icon name={spotsOpen ? 'chevron-up' : 'chevron-down'} size={18} color="rgba(255,255,255,0.85)" />
          </span>
        </button>

        {/* Toggle sheet — pulls together live + tonight + nearby hotspots */}
        {spotsOpen && (
          <div style={{
            marginTop: 10, borderRadius: 18,
            border: '1px solid rgba(255,16,119,0.22)',
            background: 'radial-gradient(circle at top right, rgba(255,16,119,0.10), transparent 50%), linear-gradient(180deg,#171727 0%,#0E0E14 100%)',
            overflow: 'hidden',
          }}>
            <div style={{ padding: '14px 16px 6px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12 }}>
              <div style={{ minWidth: 0 }}>
                <p style={{ margin: 0, fontSize: 10, fontWeight: 800, color: '#FFB8DA', letterSpacing: '0.14em', textTransform: 'uppercase' }}>HOT TONIGHT</p>
                <p style={{ margin: '4px 0 0', fontSize: 14, fontWeight: 800, color: '#fff' }}>지금 뜨는 클럽 · 파티</p>
                <p style={{ margin: '2px 0 0', fontSize: 11, color: 'rgba(255,255,255,0.5)' }}>강남구에서 가까운 순으로 모았어요.</p>
              </div>
              <button onClick={() => setSpotsOpen(false)} style={{
                all: 'unset', cursor: 'pointer', width: 30, height: 30, borderRadius: 999,
                background: 'rgba(36,36,46,0.6)', border: '1px solid rgba(255,255,255,0.08)',
                display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'rgba(255,255,255,0.6)',
                flexShrink: 0,
              }}><Icon name="x" size={13} /></button>
            </div>
            <div style={{ padding: '8px 12px 12px', display: 'flex', flexDirection: 'column', gap: 6 }}>
              {data.parties
                .slice()
                .sort((a, b) => (b.live ? 1 : 0) - (a.live ? 1 : 0) || (a.distance || 0) - (b.distance || 0))
                .slice(0, 5)
                .map(p => (
                  <button key={p.id} onClick={() => onOpenParty && onOpenParty(p)} style={{
                    all: 'unset', cursor: 'pointer',
                    display: 'flex', alignItems: 'center', gap: 10,
                    padding: 10, borderRadius: 12,
                    background: 'rgba(36,36,46,0.55)',
                    border: '1px solid rgba(255,255,255,0.04)',
                  }}>
                    <div style={{ width: 44, height: 44, borderRadius: 10, position: 'relative', overflow: 'hidden', background: '#0E0E14', flexShrink: 0 }}>
                      <ClubThumb id={p.id} tint={p.glow} size={44} ratio={1} fill />
                      <FloorGlow tint={p.glow} intensity={0.4} />
                    </div>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                        {p.live && (
                          <span style={{
                            display: 'inline-flex', alignItems: 'center', gap: 4,
                            padding: '1px 6px', borderRadius: 4,
                            background: 'rgba(255,16,119,0.18)', color: '#FF7AB8',
                            fontSize: 9, fontWeight: 800, letterSpacing: '0.10em',
                          }}><PulseBars count={2} height={6} color="#FF1077" />LIVE</span>
                        )}
                        <span style={{
                          fontSize: 13, fontWeight: 800, color: '#fff',
                          overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                        }}>{p.title}</span>
                      </div>
                      <div style={{ marginTop: 2, fontSize: 11, color: 'rgba(255,255,255,0.5)' }}>
                        {p.venue} · {p.area} · <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{p.distance}km</span>
                      </div>
                    </div>
                    <div style={{ textAlign: 'right', flexShrink: 0 }}>
                      <div style={{ fontSize: 12, fontWeight: 800, color: '#fff' }}>{window.CP_FORMAT.won(p.price)}</div>
                      <div style={{ fontSize: 9, color: 'rgba(255,255,255,0.45)', marginTop: 2, fontFamily: 'JetBrains Mono, monospace' }}>{(p.time || '').split(' ')[0]}</div>
                    </div>
                  </button>
                ))}
            </div>
            <div style={{ padding: '0 12px 12px', display: 'flex', gap: 8 }}>
              <button onClick={() => { setSpotsOpen(false); onTab && onTab('party'); }} style={{
                all: 'unset', cursor: 'pointer', flex: 1, textAlign: 'center',
                padding: '10px 0', borderRadius: 12,
                background: 'rgba(36,36,46,0.85)', border: '1px solid rgba(255,255,255,0.08)',
                color: '#fff', fontSize: 12, fontWeight: 700,
              }}>전체 파티 보기</button>
              <button onClick={() => { setSpotsOpen(false); onTab && onTab('club'); }} style={{
                all: 'unset', cursor: 'pointer', flex: 1, textAlign: 'center',
                padding: '10px 0', borderRadius: 12,
                background: 'linear-gradient(135deg,#FF1077,#7B49FF)',
                color: '#fff', fontSize: 12, fontWeight: 800,
                boxShadow: '0 6px 16px rgba(255,16,119,0.32)',
              }}>전체 클럽 보기</button>
            </div>
          </div>
        )}
      </div>

      {/* Quick stats — calculator-key buttons */}
      <div style={{ display: 'flex', gap: 8, padding: '14px 16px 0' }}>
        <CalcKey color="magenta" width="100%" height={56} glow style={{ flex: 1 }} onClick={() => onTab('party')}>
          <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 1 }}>
            <span style={{ fontSize: 16, fontWeight: 900, letterSpacing: '-0.02em' }}>{data.parties.length}</span>
            <span style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.10em' }}>PARTIES</span>
          </span>
        </CalcKey>
        <CalcKey color="cyan" width="100%" height={56} style={{ flex: 1 }} onClick={() => onTab('club')}>
          <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 1 }}>
            <span style={{ fontSize: 16, fontWeight: 900, letterSpacing: '-0.02em' }}>{data.clubs.length}</span>
            <span style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.10em' }}>CLUBS</span>
          </span>
        </CalcKey>
        <CalcKey color="violet" width="100%" height={56} style={{ flex: 1 }} onClick={() => onTab('route')}>
          <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 1 }}>
            <span style={{ fontSize: 16, fontWeight: 900, letterSpacing: '-0.02em' }}>1</span>
            <span style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.10em' }}>ROUTE</span>
          </span>
        </CalcKey>
        <CalcKey color="lime" width="100%" height={56} style={{ flex: 1 }} onClick={() => onTab('me')}>
          <span style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 1 }}>
            <span style={{ fontSize: 16, fontWeight: 900, letterSpacing: '-0.02em' }}>{data.user.ticketsAhead}</span>
            <span style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.10em' }}>GOING</span>
          </span>
        </CalcKey>
      </div>

      {/* Featured peak party */}
      <div style={{ padding: '24px 16px 0' }}>
        <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 12 }}>
          <h2 style={{ margin: 0, fontSize: 18, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>오늘 밤의 흐름</h2>
          <span style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.4)' }}>FEATURED</span>
        </div>
        <PartyHeroCard party={featured} onOpen={onOpenParty} />
      </div>

      {/* LIVE NOW strip */}
      <div style={{ padding: '24px 0 0' }}>
        <div style={{ padding: '0 16px', display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 12 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
            <PulseBars count={4} height={14} color="#FF1077" />
            <h2 style={{ margin: 0, fontSize: 18, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>진행중 · Live now</h2>
          </div>
          <button onClick={() => onTab('party')} style={{ all: 'unset', cursor: 'pointer', fontSize: 12, color: 'rgba(255,255,255,0.5)', fontWeight: 600 }}>전체보기 →</button>
        </div>
        <div style={{ display: 'flex', gap: 10, overflowX: 'auto', padding: '0 16px 4px' }}>
          {liveOnes.concat(data.parties).slice(0, 4).map((p, i) =>
          <button key={p.id + i} onClick={() => onOpenParty(p)} style={{
            all: 'unset', cursor: 'pointer', minWidth: 240, flex: '0 0 240px',
            borderRadius: 16, overflow: 'hidden', background: '#15151E',
            border: '1px solid rgba(255,255,255,0.06)',
            boxShadow: '0 4px 12px rgba(0,0,0,0.55)'
          }}>
              <div style={{ position: 'relative', height: 110, background: '#0E0E14', overflow: 'hidden' }}>
                <ClubThumb id={p.id} tint={p.glow} size={240} ratio={0.46} fill />
                <FloorGlow tint={p.glow} intensity={0.35} />
                {p.live && <div style={{ position: 'absolute', top: 10, left: 10 }}>
                  <Pill glass bg="rgba(7,7,10,0.55)" color="#fff" style={{ fontSize: 9, fontWeight: 800, letterSpacing: '0.12em' }}>
                    <PulseBars count={3} height={8} color="#FF1077" /> LIVE
                  </Pill>
                </div>}
              </div>
              <div style={{ padding: 12 }}>
                <div style={{ fontSize: 9, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.5)' }}>{p.venue}</div>
                <div style={{ fontSize: 14, fontWeight: 800, color: '#fff', marginTop: 2, letterSpacing: '-0.01em', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{p.title}</div>
                <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', fontWeight: 600, marginTop: 4, fontFamily: 'JetBrains Mono, monospace' }}>{p.time}</div>
              </div>
            </button>
          )}
        </div>
      </div>

      {/* HOT 클럽 — recommended clubs */}
      <div style={{ padding: '24px 16px 0' }}>
        <div style={{ display: 'flex', alignItems: 'baseline', justifyContent: 'space-between', marginBottom: 12 }}>
          <h2 style={{ margin: 0, fontSize: 18, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>지금 핫한 클럽</h2>
          <button onClick={() => onTab('club')} style={{ all: 'unset', cursor: 'pointer', fontSize: 12, color: 'rgba(255,255,255,0.5)', fontWeight: 600 }}>전체보기 →</button>
        </div>
        <div style={{ display: 'flex', gap: 10, overflowX: 'auto', paddingBottom: 4 }}>
          {data.clubs.slice(0, 5).map((c) =>
          <button key={c.id} onClick={() => onOpenClub(c)} style={{
            all: 'unset', cursor: 'pointer', minWidth: 130, flex: '0 0 130px',
            borderRadius: 14, overflow: 'hidden', background: '#15151E',
            border: '1px solid rgba(255,255,255,0.06)'
          }}>
              <div style={{ position: 'relative', height: 110, background: '#0E0E14' }}>
                <ClubThumb id={c.id} tint={c.glow} size={130} ratio={0.85} fill />
                <FloorGlow tint={c.glow} intensity={0.30} />
                <div style={{ position: 'absolute', top: 8, right: 8 }}>
                  <span style={{ display: 'inline-flex', gap: 3, alignItems: 'center', padding: '3px 7px', borderRadius: 999, background: 'rgba(12,12,18,0.92)', color: '#fff', fontSize: 10, fontWeight: 700 }}>
                    <Icon name="star" size={10} color="#C8FF1A" />{c.rating}
                  </span>
                </div>
              </div>
              <div style={{ padding: '10px 12px 12px' }}>
                <div style={{ fontSize: 13, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>{c.name}</div>
                <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.5)', marginTop: 2 }}>{c.area}</div>
                <div style={{ fontSize: 9, fontWeight: 700, color: 'rgba(255,255,255,0.55)', marginTop: 4, letterSpacing: '0.04em' }}>{c.genres.join(' · ')}</div>
              </div>
            </button>
          )}
        </div>
      </div>

      {/* 실시간 후기 */}
      <div style={{ padding: '24px 16px 0' }}>
        <h2 style={{ margin: '0 0 12px', fontSize: 18, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>실시간 후기</h2>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
          {data.reviews.slice(0, 2).map((r) => {
            const club = data.clubs.find((c) => c.id === r.club);
            return (
              <div key={r.id} style={{
                padding: 14, borderRadius: 14,
                background: '#15151E', border: '1px solid rgba(255,255,255,0.06)',
                display: 'flex', gap: 12
              }}>
                <Avatar color={club?.glow === 'magenta' ? '#FF1077' : club?.glow === 'cyan' ? '#1FD2FF' : '#7B49FF'} size={36} label={r.author[0]} />
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                    <div>
                      <span style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{r.author}</span>
                      <span style={{ fontSize: 11, color: 'rgba(255,255,255,0.4)', fontWeight: 600 }}> · {club?.name}</span>
                    </div>
                    <div style={{ display: 'flex', gap: 1 }}>
                      {[1, 2, 3, 4, 5].map((s) => <Icon key={s} name="star" size={10} color={s <= r.rating ? '#C8FF1A' : 'rgba(255,255,255,0.15)'} />)}
                    </div>
                  </div>
                  <div style={{ fontSize: 12, color: 'rgba(255,255,255,0.7)', fontWeight: 500, marginTop: 6, lineHeight: 1.5 }}>{r.body}</div>
                  <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.35)', marginTop: 6, fontFamily: 'JetBrains Mono, monospace' }}>{r.date}</div>
                </div>
              </div>);

          })}
        </div>
      </div>

      {/* My route quick start — uses cosmic scale */}
      <div style={{ padding: '24px 16px 0' }}>
        <button onClick={() => onTab('route')} style={{
          all: 'unset', cursor: 'pointer', display: 'block', width: '100%',
          borderRadius: 20, overflow: 'hidden', position: 'relative',
          background: 'linear-gradient(135deg, #15151E 0%, #1E1E2A 100%)',
          border: '1px solid rgba(123,73,255,0.3)',
          boxShadow: '0 0 24px rgba(123,73,255,0.18), 0 8px 24px rgba(0,0,0,0.5)'
        }}>
          <div style={{ position: 'relative', padding: '20px 16px', display: 'flex', alignItems: 'center', gap: 16 }}>
            <div style={{ flexShrink: 0 }}>
              <CosmicScale size={120} tint="violet" rings={4} />
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(160,122,255,0.9)', textTransform: 'uppercase' }}>
                MY ROUTE · 청담→한남
              </div>
              <div style={{ fontSize: 18, fontWeight: 900, color: '#fff', marginTop: 6, letterSpacing: '-0.02em', lineHeight: 1.1 }}>
                내 루트로<br />시작하기
              </div>
              <div style={{ display: 'flex', gap: 10, marginTop: 8, fontSize: 11, color: 'rgba(255,255,255,0.6)', fontWeight: 600 }}>
                <span>2 stops</span><span>·</span>
                <span>8.1km</span><span>·</span>
                <span>22:30 → 06:00</span>
              </div>
            </div>
            <Icon name="chevron-right" size={20} color="rgba(255,255,255,0.5)" />
          </div>
        </button>
      </div>

      {/* 친구 going */}
      <div style={{ padding: '24px 16px 0' }}>
        <h2 style={{ margin: '0 0 12px', fontSize: 18, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>친구가 가는 밤</h2>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {data.friends.slice(0, 3).map((f) => {
            const p = data.parties.find((x) => x.id === f.going);
            if (!p) return null;
            return (
              <div key={f.id} style={{
                padding: 12, borderRadius: 14,
                background: '#15151E', border: '1px solid rgba(255,255,255,0.06)',
                display: 'flex', gap: 12, alignItems: 'center'
              }}>
                <Avatar color={f.avatar} size={38} label={f.name[0]} />
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{f.name}</div>
                  <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.5)', fontWeight: 500 }}>
                    가는중 · <span style={{ color: '#1FD2FF', fontWeight: 700 }}>{p.title}</span>
                  </div>
                </div>
                <button onClick={() => onOpenParty(p)} style={{ all: 'unset', cursor: 'pointer', padding: '7px 12px', borderRadius: 999, background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.1)', fontSize: 11, fontWeight: 700, color: '#fff' }}>같이가기</button>
              </div>);

          })}
        </div>
      </div>

      {/* All parties (this weekend) */}
      <div style={{ padding: '24px 16px 0' }}>
        <h2 style={{ margin: '0 0 12px', fontSize: 18, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>이번 주말 더보기</h2>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {data.parties.slice(1, 5).map((p) => <PartyRow key={p.id} party={p} onOpen={onOpenParty} />)}
        </div>
      </div>
    </div>);

}
window.HomeScreen = HomeScreen;