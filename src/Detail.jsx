/* global React, Icon, GlowDot, Pill, GenreTag, Eyebrow, FloorGlow, OrganicLoader, GlobeLoader, CosmicScale, CalcKey, PulseBars, Aurora, Avatar, FriendStack, ClubThumb */
const { useState: useStateD, useEffect: useEffectD } = React;

// ─────────────────────────────────────────────────────────────
// Helpers — derive html-style fields from the mock data so the
// React detail screens visually match the Blade templates in
// resources/views/{parties,clubs}/show.blade.php.
// ─────────────────────────────────────────────────────────────
const detailHelpers = {
  partyEventLabel(p) {
    if (p.live) return 'LIVE NOW';
    if (p.date && p.date.includes('오늘')) return '오늘 진행';
    if (p.date && p.date.includes('내일')) return '내일 예정';
    return '예매 가능';
  },
  partyEventVariant(p) {
    if (p.live || (p.date && p.date.includes('오늘'))) return 'green';
    if (p.hot) return 'cyan';
    return 'default';
  },
  partyEventNotice(p) {
    if (p.live) return '지금 진행중. 입장 안내가 빠르게 마감될 수 있어요.';
    if (p.hot) return '문의 시 입장 가능 여부와 정확한 가격 안내를 빠르게 받아볼 수 있어요.';
    return '문의 시 입장 가능 여부와 가격, 운영 안내를 한 번에 받을 수 있어요.';
  },
  contactReady(min) { return (min ?? 999) < 60; },
  responseHint(min) {
    if (min == null) return '오늘 안에 회신';
    if (min < 15) return '실시간 응대중';
    if (min < 30) return '빠르게 응답';
    return '오늘 안에 회신';
  },
  budgetGuide(price) {
    const lo = Math.round(price * 0.9 / 1000) * 1000;
    const hi = Math.round(price * 1.6 / 1000) * 1000;
    return lo.toLocaleString('ko-KR') + '~' + hi.toLocaleString('ko-KR') + '원';
  },
  entryFee(c) {
    if (c.priceLevel >= 3) return '₩30,000~50,000';
    if (c.priceLevel >= 2) return '₩15,000~30,000';
    return '무료~₩15,000';
  },
  monthAbbr(iso) {
    if (!iso) return '';
    const m = parseInt(iso.split('-')[1], 10);
    return ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'][m - 1] || '';
  },
  dayNum(iso) {
    if (!iso) return '';
    return parseInt(iso.split('-')[2], 10);
  },
};

// ─────────────────────────────────────────────────────────────
// Small primitives used by both detail screens.
// ─────────────────────────────────────────────────────────────
function TagPill({ children, variant = 'default', style = {} }) {
  const palette = {
    default: { bg: 'rgba(255,255,255,0.08)', color: '#E5E7EB', border: 'transparent' },
    emerald: { bg: 'rgba(16,185,129,0.15)',  color: '#6EE7B7', border: 'rgba(16,185,129,0.20)' },
    pink:    { bg: 'rgba(236,72,153,0.15)',  color: '#FBCFE8', border: 'rgba(236,72,153,0.20)' },
    cyan:    { bg: 'rgba(6,182,212,0.15)',   color: '#67E8F9', border: 'rgba(6,182,212,0.20)' },
    sky:     { bg: 'rgba(14,165,233,0.15)',  color: '#7DD3FC', border: 'rgba(14,165,233,0.20)' },
    violet:  { bg: 'rgba(139,92,246,0.15)',  color: '#C4B5FD', border: 'rgba(139,92,246,0.20)' },
    amber:   { bg: 'rgba(245,158,11,0.15)',  color: '#FCD34D', border: 'rgba(245,158,11,0.20)' },
  };
  const v = palette[variant] || palette.default;
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 4,
      padding: '4px 10px', borderRadius: 999,
      fontSize: 11, fontWeight: 600, whiteSpace: 'nowrap',
      background: v.bg, color: v.color,
      border: '1px solid ' + v.border,
      ...style,
    }}>{children}</span>
  );
}

function StatTile({ label, value, hint, mono }) {
  return (
    <div style={{
      borderRadius: 16, border: '1px solid rgba(255,255,255,0.06)',
      background: 'rgba(0,0,0,0.20)', padding: '12px 14px',
    }}>
      <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.45)' }}>{label}</div>
      <div style={{
        marginTop: 4, fontSize: 14, fontWeight: 800, color: '#fff',
        fontFamily: mono ? 'JetBrains Mono, monospace' : 'inherit',
      }}>{value}</div>
      {hint && (
        <div style={{ marginTop: 4, fontSize: 11, color: 'rgba(255,255,255,0.45)' }}>{hint}</div>
      )}
    </div>
  );
}

function DetailCard({ children, style = {} }) {
  return (
    <div style={{
      borderRadius: 16, border: '1px solid rgba(255,255,255,0.06)',
      background: '#15151E', padding: 16, ...style,
    }}>{children}</div>
  );
}

function InfoGridItem({ icon, label, value, mono }) {
  return (
    <div style={{
      display: 'flex', gap: 10, padding: 14,
      borderRadius: 14, background: '#15151E',
      border: '1px solid rgba(255,255,255,0.06)',
    }}>
      <div style={{
        width: 32, height: 32, borderRadius: 10,
        background: 'rgba(255,255,255,0.04)',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        fontSize: 14,
      }}>{icon}</div>
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.45)', fontWeight: 600 }}>{label}</div>
        <div style={{
          marginTop: 2, fontSize: 13, color: '#fff', fontWeight: 700,
          fontFamily: mono ? 'JetBrains Mono, monospace' : 'inherit',
          overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
        }}>{value}</div>
      </div>
    </div>
  );
}

function HeroIconBtn({ onClick, children }) {
  return (
    <button onClick={onClick} style={{
      all: 'unset', cursor: 'pointer', width: 38, height: 38, borderRadius: 999,
      background: 'rgba(12,12,18,0.92)', border: '1px solid rgba(255,255,255,0.08)',
      display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff',
    }}>{children}</button>
  );
}

function SectionTitle({ children, badge, action }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      marginBottom: 10, gap: 8,
    }}>
      <h2 style={{
        margin: 0, fontSize: 15, fontWeight: 800, color: '#fff',
        letterSpacing: '-0.01em', display: 'inline-flex', alignItems: 'center', gap: 8,
      }}>
        {badge && <span style={{ fontSize: 14 }}>{badge}</span>}
        {children}
      </h2>
      {action}
    </div>
  );
}

// Sticky bottom action bar — mirrors the html `fixed inset-x-0 bottom-[72px]` footer.
function StickyDetailFooter({ favored, onFavorite, onPrimary, primaryLabel = '문의하기' }) {
  const [notice, setNotice] = useStateD('');
  const showNotice = (msg) => {
    setNotice(msg);
    setTimeout(() => setNotice(''), 1800);
  };
  const handleShare = async () => {
    try {
      if (navigator.share) {
        await navigator.share({ title: 'ClubParty', url: window.location.href });
        return;
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(window.location.href);
        showNotice('링크가 복사되었습니다');
        return;
      }
    } catch (e) { /* user cancelled or blocked */ }
  };
  return (
    <div style={{
      position: 'sticky', bottom: 0, padding: 12,
      background: 'linear-gradient(180deg, rgba(7,7,10,0) 0%, #07070A 35%)',
      zIndex: 30,
    }}>
      <div style={{ position: 'relative', maxWidth: 600, margin: '0 auto' }}>
        {notice && (
          <div style={{
            position: 'absolute', top: -36, left: '50%', transform: 'translateX(-50%)',
            background: 'rgba(7,7,10,0.95)', border: '1px solid rgba(255,255,255,0.08)',
            color: '#fff', fontSize: 11, fontWeight: 600,
            padding: '6px 12px', borderRadius: 999, whiteSpace: 'nowrap',
          }}>{notice}</div>
        )}
        <div style={{
          background: 'rgba(14,14,20,0.92)', backdropFilter: 'blur(18px)',
          border: '1px solid rgba(255,255,255,0.08)', borderRadius: 22,
          padding: 10, display: 'grid', gridTemplateColumns: '48px 48px 1fr', gap: 8,
        }}>
          <button onClick={onFavorite} style={{
            all: 'unset', cursor: 'pointer', height: 48, borderRadius: 14,
            background: 'rgba(36,36,46,0.85)', border: '1px solid rgba(255,255,255,0.06)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            color: favored ? '#FF1077' : 'rgba(255,255,255,0.7)',
          }}>
            <Icon name="heart" size={18} color={favored ? '#FF1077' : 'currentColor'} />
          </button>
          <button onClick={handleShare} style={{
            all: 'unset', cursor: 'pointer', height: 48, borderRadius: 14,
            background: 'rgba(36,36,46,0.85)', border: '1px solid rgba(255,255,255,0.06)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            color: 'rgba(255,255,255,0.7)',
          }}>
            <Icon name="share-2" size={18} />
          </button>
          <button onClick={onPrimary} style={{
            all: 'unset', cursor: 'pointer', height: 48, borderRadius: 14,
            background: 'linear-gradient(135deg, #FF1077 0%, #7B49FF 100%)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            color: '#fff', fontWeight: 800, fontSize: 13,
            boxShadow: '0 8px 24px rgba(255,16,119,0.32)',
          }}>{primaryLabel}</button>
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// PARTY DETAIL — visual parity with resources/views/parties/show.blade.php.
// Sections: hero + summary card + inquiry snapshot + info grid +
// lineup + description + friends + reviews + tags + tour CTA + sticky footer.
// ─────────────────────────────────────────────────────────────
function PartyDetail({ party, onBack, onOpenClub }) {
  if (!party) return null;
  const data = window.CP_DATA;
  const club = data.clubs.find(c => c.name === party.venue);
  const friendsGoing = data.friends.filter(f => f.going === party.id);
  const reviews = club ? data.reviews.filter(r => r.club === club.id) : [];
  const [favored, setFavored] = useStateD(false);

  const eventLabel = detailHelpers.partyEventLabel(party);
  const eventVariant = detailHelpers.partyEventVariant(party);
  const eventNotice = detailHelpers.partyEventNotice(party);
  const responseMin = club?.responseMin ?? 18;
  const contactReady = detailHelpers.contactReady(responseMin);
  const responseText = responseMin + '분 내';
  const responseHint = detailHelpers.responseHint(responseMin);
  const budget = detailHelpers.budgetGuide(party.price);
  const fillPct = Math.round(party.going / party.capacity * 100);
  const dateShort = party.date
    ? party.date.replace('· TONIGHT', '').replace('· SAT', '').replace('· SUN', '').replace('· FRI', '').trim()
    : '';
  const eventAccent = eventVariant === 'green' ? '#6EE7B7'
    : eventVariant === 'cyan' ? '#67E8F9'
    : 'rgba(255,255,255,0.7)';

  return (
    <div style={{
      position: 'absolute', top: 0, left: 0, right: 0,
      bottom: 'calc(env(safe-area-inset-bottom, 0px) + 70px)',
      background: '#07070A', overflowY: 'auto', zIndex: 100,
      display: 'flex', flexDirection: 'column',
    }}>
      {/* Hero */}
      <div style={{ position: 'relative', height: 200, overflow: 'hidden', flexShrink: 0 }}>
        <FloorGlow tint={party.glow} intensity={0.85} />
        <ClubThumb id={party.id} tint={party.glow} size={420} ratio={0.86} fill />
        <div style={{
          position: 'absolute', inset: 0,
          background: 'linear-gradient(180deg, rgba(7,7,10,0.5) 0%, rgba(7,7,10,0) 30%, rgba(7,7,10,0) 60%, rgba(7,7,10,0.95) 100%)',
        }} />

        {/* Top-bar buttons */}
        <div style={{
          position: 'absolute', top: 0, left: 0, right: 0, padding: '14px 16px',
          display: 'flex', justifyContent: 'space-between', alignItems: 'center', zIndex: 3,
        }}>
          <HeroIconBtn onClick={onBack}><Icon name="chevron-left" size={18} /></HeroIconBtn>
          <div style={{ display: 'flex', gap: 8 }}>
            <HeroIconBtn onClick={() => setFavored(!favored)}>
              <Icon name="heart" size={16} color={favored ? '#FF1077' : '#fff'} />
            </HeroIconBtn>
            <HeroIconBtn><Icon name="share-2" size={16} /></HeroIconBtn>
          </div>
        </div>

        {/* TONIGHT badge — top right (offset below the icon row) */}
        {party.date && party.date.includes('오늘') && (
          <div style={{ position: 'absolute', top: 58, right: 16, zIndex: 3 }}>
            <span style={{
              display: 'inline-flex', alignItems: 'center', gap: 6,
              fontSize: 10, fontWeight: 900, color: '#fff',
              padding: '6px 12px', borderRadius: 999,
              background: 'linear-gradient(135deg,#FF1077 0%,#7B49FF 50%,#1FD2FF 100%)',
              boxShadow: '0 6px 20px rgba(255,16,119,0.55)',
              letterSpacing: '0.10em',
            }}>
              {party.live && <PulseBars count={3} height={9} color="#fff" />}
              TONIGHT
            </span>
          </div>
        )}

        {/* Genre badge — bottom left */}
        <div style={{ position: 'absolute', bottom: 16, left: 16, zIndex: 2 }}>
          <span style={{
            fontSize: 10, fontWeight: 800, color: '#fff', letterSpacing: '0.08em',
            background: 'linear-gradient(135deg,#7B49FF,#FF1077)',
            padding: '6px 12px', borderRadius: 999,
            boxShadow: '0 6px 20px rgba(123,73,255,0.42)',
          }}>{(party.genres || []).join(' · ')}</span>
        </div>
      </div>

      {/* Body */}
      <div style={{
        padding: '0 16px 16px',
        display: 'flex', flexDirection: 'column', gap: 18,
        marginTop: -16, position: 'relative', zIndex: 5,
      }}>
        {/* Hero summary card */}
        <div style={{
          borderRadius: 18, border: '1px solid rgba(255,255,255,0.06)',
          background: 'radial-gradient(circle at top right, rgba(236,72,153,0.18), transparent 40%), linear-gradient(180deg,#171727 0%,#11111b 100%)',
          padding: 16,
        }}>
          {/* Pills row */}
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            {(party.genres || []).map(g => <TagPill key={g}>{g}</TagPill>)}
            <TagPill>{party.area}</TagPill>
            <TagPill variant={eventVariant === 'green' ? 'emerald' : eventVariant === 'cyan' ? 'cyan' : 'default'}>{eventLabel}</TagPill>
            <TagPill variant={contactReady ? 'emerald' : 'amber'}>{contactReady ? '문의 가능' : '문의 확인 필요'}</TagPill>
            {party.date && party.date.includes('오늘') && <TagPill variant="pink">오늘 진행</TagPill>}
          </div>

          {/* Title row + actions */}
          <div style={{
            marginTop: 14, display: 'flex',
            alignItems: 'flex-start', justifyContent: 'space-between', gap: 12,
          }}>
            <div style={{ flex: 1, minWidth: 0 }}>
              <h1 style={{
                margin: 0, fontSize: 22, fontWeight: 900, color: '#fff',
                letterSpacing: '-0.02em', lineHeight: 1.18,
              }}>{party.title}</h1>
              {club && (
                <button onClick={() => onOpenClub && onOpenClub(club)} style={{
                  all: 'unset', cursor: 'pointer', marginTop: 6,
                  display: 'inline-flex', alignItems: 'center', gap: 4,
                  fontSize: 13, fontWeight: 600, color: '#FF7AB8',
                }}>
                  <Icon name="map-pin" size={12} color="#FF7AB8" />
                  {club.name} · {club.area}
                </button>
              )}
              <p style={{ margin: '8px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.55)' }}>
                {party.date} · <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{party.time}</span>
              </p>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 6, flexShrink: 0 }}>
              <button onClick={() => setFavored(!favored)} style={{
                all: 'unset', cursor: 'pointer', width: 40, height: 40, borderRadius: 999,
                background: 'rgba(36,36,46,0.6)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: favored ? '#FF1077' : 'rgba(255,255,255,0.55)',
              }}>
                <Icon name="heart" size={18} color={favored ? '#FF1077' : 'currentColor'} />
              </button>
              <button style={{
                all: 'unset', cursor: 'pointer',
                padding: '6px 10px', borderRadius: 999,
                fontSize: 10, fontWeight: 700, color: 'rgba(255,255,255,0.7)',
                border: '1px solid rgba(255,255,255,0.08)',
                background: 'rgba(36,36,46,0.6)', textAlign: 'center',
              }}>비교</button>
            </div>
          </div>

          {/* 2x2 stats grid */}
          <div style={{
            marginTop: 16, display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8,
          }}>
            <StatTile label="티켓 가격" value={window.CP_FORMAT.won(party.price)} hint="행사 기준 안내" />
            <StatTile label="평균 응답" value={responseText} hint={responseHint} />
            <StatTile label="진행 일정" value={dateShort || party.date} hint={party.time} mono />
            <StatTile label="문의 흐름" value={contactReady ? '실시간 응대' : '확인 필요'} hint={party.live ? '지금 진행중' : '오늘 입장 가능'} />
          </div>

          {/* Signal pills */}
          <div style={{ marginTop: 14, display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            <TagPill variant="emerald">입장 가능</TagPill>
            <TagPill variant="violet">{fillPct > 80 ? '거의 매진' : fillPct > 50 ? '활기찬 분위기' : '여유 있음'}</TagPill>
            <TagPill variant="sky">{dateShort || party.date} · {(party.time || '').split(' ')[0]}</TagPill>
          </div>

          {/* Event notice */}
          <div style={{
            marginTop: 14, padding: '12px 14px', borderRadius: 16,
            border: '1px solid rgba(255,255,255,0.06)', background: 'rgba(0,0,0,0.20)',
          }}>
            <div style={{ fontSize: 11, fontWeight: 700, color: eventAccent }}>{eventLabel}</div>
            <p style={{ margin: '4px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.55)', lineHeight: 1.55 }}>{eventNotice}</p>
          </div>
        </div>

        {/* Inquiry snapshot */}
        <DetailCard>
          <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 12 }}>
            <div style={{ minWidth: 0 }}>
              <p style={{ margin: 0, fontSize: 14, fontWeight: 800, color: '#fff' }}>문의 전 빠른 확인</p>
              <p style={{ margin: '4px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.5)', lineHeight: 1.55 }}>
                오늘 입장 가능 여부, 예산, 답변 속도를 먼저 확인하고 바로 문의할 수 있게 정리했어.
              </p>
            </div>
            <span style={{
              padding: '4px 10px', borderRadius: 999,
              background: 'rgba(255,16,119,0.15)', color: '#FFB8DA',
              fontSize: 11, fontWeight: 700, whiteSpace: 'nowrap', flexShrink: 0,
            }}>2명 응대중</span>
          </div>
          <div style={{ marginTop: 12, display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 8 }}>
            <StatTile label="평균 응답시간" value={responseText} hint={responseHint} />
            <StatTile label="최근 확정률" value="92%" hint="지난 7일 기준" />
            <StatTile label="예상 가격대" value={budget} hint="입장 + 1인 기본" />
          </div>
        </DetailCard>

        {/* Info grid */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
          <InfoGridItem icon="📅" label="날짜" value={party.date} />
          <InfoGridItem icon="🕐" label="시간" value={party.time} mono />
          <InfoGridItem icon="🎫" label="티켓 가격" value={window.CP_FORMAT.won(party.price)} />
          <InfoGridItem icon="👔" label="드레스코드" value={(club && club.dress) || '자유'} />
        </div>

        {/* Lineup */}
        <div>
          <SectionTitle badge="🎧">라인업</SectionTitle>
          <DetailCard style={{ padding: 0, overflow: 'hidden' }}>
            {(party.lineup || []).map((dj, i, a) => (
              <div key={dj.name} style={{
                display: 'flex', alignItems: 'center', gap: 12, padding: '12px 14px',
                borderBottom: i < a.length - 1 ? '1px solid rgba(255,255,255,0.06)' : 'none',
              }}>
                <Avatar color={['#FF1077','#1FD2FF','#7B49FF','#C8FF1A'][i % 4]} size={40} label={dj.name[0]} />
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', gap: 6, alignItems: 'center', flexWrap: 'wrap' }}>
                    <span style={{ fontSize: 14, fontWeight: 800, color: '#fff' }}>{dj.name}</span>
                    {dj.resident && (
                      <span style={{
                        fontSize: 9, fontWeight: 800, color: '#C8FF1A', letterSpacing: '0.10em',
                        padding: '2px 6px', borderRadius: 4, background: 'rgba(200,255,26,0.12)',
                      }}>RESIDENT</span>
                    )}
                  </div>
                  <div style={{
                    fontSize: 12, color: 'rgba(255,255,255,0.55)',
                    fontFamily: 'JetBrains Mono, monospace', marginTop: 2,
                  }}>{dj.set}</div>
                </div>
                <button style={{
                  all: 'unset', cursor: 'pointer', width: 32, height: 32, borderRadius: 999,
                  background: 'rgba(255,255,255,0.06)',
                  display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff',
                }}><Icon name="play" size={12} /></button>
              </div>
            ))}
          </DetailCard>
        </div>

        {/* Description (소개) */}
        <DetailCard>
          <div style={{ fontSize: 13, fontWeight: 700, color: 'rgba(255,255,255,0.6)', marginBottom: 8 }}>소개</div>
          {party.subtitle && (
            <p style={{ margin: 0, fontSize: 14, color: 'rgba(255,255,255,0.85)', fontWeight: 600, lineHeight: 1.55 }}>
              {party.subtitle}
            </p>
          )}
          {party.description && (
            <p style={{ margin: '8px 0 0', fontSize: 13, color: 'rgba(255,255,255,0.55)', lineHeight: 1.7 }}>
              {party.description}
            </p>
          )}
        </DetailCard>

        {/* Friends going */}
        {friendsGoing.length > 0 && (
          <div>
            <SectionTitle badge="👯">친구 {friendsGoing.length}명 가는중</SectionTitle>
            <div style={{ display: 'flex', gap: 10, overflowX: 'auto', paddingBottom: 4 }}>
              {friendsGoing.map(f => (
                <div key={f.id} style={{
                  minWidth: 110, padding: 12, borderRadius: 14,
                  background: '#15151E', border: '1px solid rgba(255,255,255,0.06)',
                  textAlign: 'center', flexShrink: 0,
                }}>
                  <Avatar color={f.avatar} size={48} label={f.name[0]} />
                  <div style={{ fontSize: 12, fontWeight: 800, color: '#fff', marginTop: 8 }}>{f.name}</div>
                  <div style={{ fontSize: 10, color: 'rgba(255,255,255,0.5)' }}>{f.handle}</div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Reviews */}
        <div>
          <SectionTitle
            badge="⭐"
            action={
              <button style={{
                all: 'unset', cursor: 'pointer', fontSize: 11,
                color: '#1FD2FF', fontWeight: 700,
              }}>리뷰 쓰기 →</button>
            }
          >후기 · {reviews.length}</SectionTitle>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            {reviews.length > 0 ? reviews.map(r => (
              <DetailCard key={r.id}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                  <span style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{r.author}</span>
                  <div style={{ display: 'flex', gap: 1 }}>
                    {[1,2,3,4,5].map(s => (
                      <Icon key={s} name="star" size={10}
                        color={s <= r.rating ? '#C8FF1A' : 'rgba(255,255,255,0.15)'} />
                    ))}
                  </div>
                </div>
                <p style={{
                  margin: '6px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.7)',
                  lineHeight: 1.55, fontWeight: 500,
                }}>{r.body}</p>
                <div style={{
                  marginTop: 8, fontSize: 10, color: 'rgba(255,255,255,0.35)',
                  fontFamily: 'JetBrains Mono, monospace',
                }}>{r.date}</div>
              </DetailCard>
            )) : (
              <div style={{
                padding: 24, textAlign: 'center',
                color: 'rgba(255,255,255,0.4)', fontSize: 12,
                borderRadius: 14, border: '1px dashed rgba(255,255,255,0.08)',
              }}>아직 후기가 없어요</div>
            )}
          </div>
        </div>

        {/* Tags */}
        {(party.tags || []).length > 0 && (
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            {party.tags.map(t => (
              <span key={t} style={{
                padding: '5px 10px', borderRadius: 999,
                background: 'rgba(255,255,255,0.06)',
                color: 'rgba(255,255,255,0.7)',
                fontSize: 10, fontWeight: 700, letterSpacing: '0.10em',
              }}>#{t}</span>
            ))}
          </div>
        )}

        {/* CTA — add to tour */}
        <button style={{
          all: 'unset', cursor: 'pointer',
          width: '100%', padding: '14px 0',
          borderRadius: 16, textAlign: 'center', boxSizing: 'border-box',
          fontSize: 13, fontWeight: 800, color: '#fff',
          background: 'rgba(36,36,46,0.85)',
          border: '1px solid rgba(255,255,255,0.08)',
        }}>투어에 추가하기</button>

        <div style={{ height: 12 }} />
      </div>

      <StickyDetailFooter
        favored={favored}
        onFavorite={() => setFavored(!favored)}
        primaryLabel="문의하기"
      />
    </div>
  );
}
window.PartyDetail = PartyDetail;

// ─────────────────────────────────────────────────────────────
// CLUB DETAIL — visual parity with resources/views/clubs/show.blade.php.
// Sections: hero + summary card + inquiry snapshot + info grid +
// description + reviews + address + upcoming + tags + tour CTA + sticky footer.
// ─────────────────────────────────────────────────────────────
function ClubDetail({ club, onBack, onOpenParty }) {
  if (!club) return null;
  const data = window.CP_DATA;
  const partiesAt = data.parties.filter(p => p.venue === club.name);
  const reviews = data.reviews.filter(r => r.club === club.id);
  const [favored, setFavored] = useStateD(false);

  const responseMin = club.responseMin ?? 18;
  const contactReady = detailHelpers.contactReady(responseMin);
  const responseText = responseMin + '분 내';
  const responseHint = detailHelpers.responseHint(responseMin);
  const entryFee = detailHelpers.entryFee(club);
  const budget = club.priceLevel >= 3 ? '50,000~150,000원'
    : club.priceLevel >= 2 ? '30,000~80,000원'
    : '20,000~50,000원';

  return (
    <div style={{
      position: 'absolute', top: 0, left: 0, right: 0,
      bottom: 'calc(env(safe-area-inset-bottom, 0px) + 70px)',
      background: '#07070A', overflowY: 'auto', zIndex: 100,
      display: 'flex', flexDirection: 'column',
    }}>
      {/* Hero */}
      <div style={{ position: 'relative', height: 200, overflow: 'hidden', flexShrink: 0 }}>
        <ClubThumb id={club.id} tint={club.glow} size={420} ratio={0.76} fill />
        <FloorGlow tint={club.glow} intensity={0.45} />
        <div style={{
          position: 'absolute', inset: 0,
          background: 'linear-gradient(180deg, rgba(7,7,10,0.5) 0%, transparent 30%, transparent 60%, rgba(7,7,10,0.95) 100%)',
        }} />

        <div style={{
          position: 'absolute', top: 0, left: 0, right: 0, padding: '14px 16px',
          display: 'flex', justifyContent: 'space-between', alignItems: 'center', zIndex: 3,
        }}>
          <HeroIconBtn onClick={onBack}><Icon name="chevron-left" size={18} /></HeroIconBtn>
          <HeroIconBtn onClick={() => setFavored(!favored)}>
            <Icon name="heart" size={16} color={favored ? '#FF1077' : '#fff'} />
          </HeroIconBtn>
        </div>

        {/* Bottom badges */}
        <div style={{
          position: 'absolute', bottom: 16, left: 16, right: 16,
          display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap', zIndex: 2,
        }}>
          <span style={{
            display: 'inline-flex', gap: 4,
            fontSize: 10, fontWeight: 700, color: '#7DD3FC',
            padding: '5px 11px', borderRadius: 999,
            background: 'rgba(14,165,233,0.20)',
            border: '1px solid rgba(14,165,233,0.20)',
            backdropFilter: 'blur(8px)',
          }}>🌍 외국인 OK</span>
          <span style={{
            display: 'inline-flex', gap: 4,
            fontSize: 10, fontWeight: 700, color: 'rgba(255,255,255,0.85)',
            padding: '5px 11px', borderRadius: 999,
            background: 'rgba(0,0,0,0.30)',
            border: '1px solid rgba(255,255,255,0.10)',
            backdropFilter: 'blur(8px)',
          }}>{(club.genres || []).join(' · ')}</span>
        </div>
      </div>

      {/* Body */}
      <div style={{
        padding: '0 16px 16px',
        display: 'flex', flexDirection: 'column', gap: 18,
        marginTop: -16, position: 'relative', zIndex: 5,
      }}>
        {/* Hero summary */}
        <div style={{
          borderRadius: 18, border: '1px solid rgba(255,255,255,0.06)',
          background: 'radial-gradient(circle at top right, rgba(168,85,247,0.24), transparent 42%), linear-gradient(180deg,#171727 0%,#11111b 100%)',
          padding: 16,
        }}>
          {/* Pills row */}
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            <TagPill>{club.area}</TagPill>
            <TagPill>{(club.genres || []).join(' / ')}</TagPill>
            <TagPill variant={contactReady ? 'emerald' : 'amber'}>{contactReady ? '문의 가능' : '문의 확인 필요'}</TagPill>
            <TagPill variant="sky">외국인 OK</TagPill>
          </div>

          {/* Title row + actions */}
          <div style={{
            marginTop: 14, display: 'flex',
            alignItems: 'flex-start', justifyContent: 'space-between', gap: 12,
          }}>
            <div style={{ flex: 1, minWidth: 0 }}>
              <h1 style={{
                margin: 0, fontSize: 24, fontWeight: 900, color: '#fff',
                letterSpacing: '-0.02em', lineHeight: 1.15,
              }}>{club.name}</h1>
              <p style={{ margin: '4px 0 0', fontSize: 13, color: 'rgba(255,255,255,0.55)' }}>{club.addr}</p>
              <div style={{
                marginTop: 12, display: 'flex', flexWrap: 'wrap',
                alignItems: 'center', gap: 12,
              }}>
                <span style={{ display: 'inline-flex', gap: 3, alignItems: 'center', fontSize: 12, color: '#fff', fontWeight: 700 }}>
                  <Icon name="star" size={12} color="#C8FF1A" /> {club.rating}
                  <span style={{ color: 'rgba(255,255,255,0.4)', fontWeight: 500, marginLeft: 4 }}>· {club.reviews.toLocaleString()}</span>
                </span>
                <span style={{ fontSize: 11, fontWeight: 600, color: 'rgba(255,255,255,0.55)' }}>cap. {club.cap}</span>
              </div>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 6, flexShrink: 0 }}>
              <button onClick={() => setFavored(!favored)} style={{
                all: 'unset', cursor: 'pointer', width: 40, height: 40, borderRadius: 999,
                background: 'rgba(36,36,46,0.6)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: favored ? '#FF1077' : 'rgba(255,255,255,0.55)',
              }}>
                <Icon name="heart" size={18} color={favored ? '#FF1077' : 'currentColor'} />
              </button>
              <button style={{
                all: 'unset', cursor: 'pointer',
                padding: '6px 10px', borderRadius: 999,
                fontSize: 10, fontWeight: 700, color: 'rgba(255,255,255,0.7)',
                border: '1px solid rgba(255,255,255,0.08)',
                background: 'rgba(36,36,46,0.6)', textAlign: 'center',
              }}>비교</button>
            </div>
          </div>

          {/* 2x2 stats grid */}
          <div style={{ marginTop: 16, display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
            <StatTile label="입장 가격" value={entryFee} hint="현장 기준 안내" />
            <StatTile label="평균 응답" value={responseText} hint={responseHint} />
            <StatTile label="운영 시간" value={club.openHours} hint="오늘 방문 기준" mono />
            <StatTile label="문의 흐름" value={contactReady ? '실시간 응대' : '확인 필요'} hint="입장 안내 즉시" />
          </div>

          {/* Signal pills */}
          <div style={{ marginTop: 14, display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            <TagPill variant="emerald">입장 가능</TagPill>
            <TagPill variant="violet">{(club.popularity || 0) > 85 ? '활기찬 분위기' : '여유 있음'}</TagPill>
            <TagPill variant="sky">{club.openHours}</TagPill>
          </div>
        </div>

        {/* Inquiry snapshot */}
        <DetailCard>
          <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 12 }}>
            <div style={{ minWidth: 0 }}>
              <p style={{ margin: 0, fontSize: 14, fontWeight: 800, color: '#fff' }}>문의 전 빠른 확인</p>
              <p style={{ margin: '4px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.5)', lineHeight: 1.55 }}>
                가격, 응답 속도, 최근 확정 흐름을 먼저 보고 바로 문의할 수 있게 정리했어.
              </p>
            </div>
            <span style={{
              padding: '4px 10px', borderRadius: 999,
              background: 'rgba(255,16,119,0.15)', color: '#FFB8DA',
              fontSize: 11, fontWeight: 700, whiteSpace: 'nowrap', flexShrink: 0,
            }}>3명 응대중</span>
          </div>
          <div style={{ marginTop: 12, display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 8 }}>
            <StatTile label="평균 응답시간" value={responseText} hint={responseHint} />
            <StatTile label="최근 확정률" value="89%" hint="지난 30일 기준" />
            <StatTile label="예상 가격대" value={budget} hint="입장 + 1인 기본" />
          </div>
        </DetailCard>

        {/* Info grid */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
          <InfoGridItem icon="🕐" label="운영시간" value={club.openHours} mono />
          <InfoGridItem icon="💰" label="입장료" value={entryFee} />
          <InfoGridItem icon="👔" label="드레스코드" value={club.dress || '자유'} />
          <InfoGridItem icon="📍" label="위치" value={club.area} />
        </div>

        {/* Description */}
        <DetailCard>
          <div style={{ fontSize: 13, fontWeight: 700, color: 'rgba(255,255,255,0.6)', marginBottom: 8 }}>소개</div>
          <p style={{ margin: 0, fontSize: 14, color: 'rgba(255,255,255,0.85)', fontWeight: 600, lineHeight: 1.55 }}>
            {club.area}에서 만나는 {(club.genres || []).join(' · ')} 셀렉션
          </p>
          <p style={{ margin: '8px 0 0', fontSize: 13, color: 'rgba(255,255,255,0.55)', lineHeight: 1.7 }}>
            수용 {club.cap}석. 운영 {club.openHours}. 외국인 친화 입장. 드레스 코드 {club.dress || '자유'}. 입장과 분위기에 대한 자세한 안내는 문의 시 바로 받을 수 있어.
          </p>
        </DetailCard>

        {/* Reviews */}
        <div>
          <SectionTitle
            badge="⭐"
            action={
              <button style={{
                all: 'unset', cursor: 'pointer', fontSize: 11,
                color: '#1FD2FF', fontWeight: 700,
              }}>리뷰 쓰기 →</button>
            }
          >후기 · {club.reviews.toLocaleString()}</SectionTitle>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            {reviews.length > 0 ? reviews.map(r => (
              <DetailCard key={r.id}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
                  <span style={{ fontSize: 13, fontWeight: 800, color: '#fff' }}>{r.author}</span>
                  <div style={{ display: 'flex', gap: 1 }}>
                    {[1,2,3,4,5].map(s => (
                      <Icon key={s} name="star" size={10}
                        color={s <= r.rating ? '#C8FF1A' : 'rgba(255,255,255,0.15)'} />
                    ))}
                  </div>
                </div>
                <p style={{
                  margin: '6px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.7)',
                  lineHeight: 1.55, fontWeight: 500,
                }}>{r.body}</p>
                <div style={{
                  marginTop: 8, fontSize: 10, color: 'rgba(255,255,255,0.35)',
                  fontFamily: 'JetBrains Mono, monospace',
                }}>{r.date}</div>
              </DetailCard>
            )) : (
              <div style={{
                padding: 24, textAlign: 'center',
                color: 'rgba(255,255,255,0.4)', fontSize: 12,
                borderRadius: 14, border: '1px dashed rgba(255,255,255,0.08)',
              }}>아직 후기가 없어요</div>
            )}
          </div>
        </div>

        {/* Address */}
        <DetailCard>
          <div style={{ display: 'flex', gap: 12, alignItems: 'flex-start' }}>
            <div style={{
              width: 32, height: 32, borderRadius: 10, flexShrink: 0,
              background: 'rgba(255,255,255,0.04)',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
            }}>
              <Icon name="map-pin" size={15} color="rgba(255,255,255,0.45)" />
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
              <p style={{ margin: 0, fontSize: 13, color: '#fff', fontWeight: 600 }}>{club.addr}</p>
              <p style={{ margin: '4px 0 0', fontSize: 12, color: '#FF7AB8', fontWeight: 600 }}>
                @{club.name.toLowerCase().replace(/[^a-z0-9]/g, '')}
              </p>
            </div>
          </div>
        </DetailCard>

        {/* Upcoming parties at this club */}
        {partiesAt.length > 0 && (
          <div>
            <SectionTitle badge="🎉">다가오는 파티</SectionTitle>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
              {partiesAt.map(p => (
                <button key={p.id} onClick={() => onOpenParty && onOpenParty(p)} style={{
                  all: 'unset', cursor: 'pointer', display: 'flex', gap: 12, padding: 14,
                  borderRadius: 14, background: '#15151E',
                  border: '1px solid rgba(255,255,255,0.06)', alignItems: 'center',
                }}>
                  <div style={{
                    width: 44, height: 44, borderRadius: 14, flexShrink: 0,
                    background: 'linear-gradient(135deg,#FF1077,#7B49FF)',
                    display: 'flex', flexDirection: 'column',
                    alignItems: 'center', justifyContent: 'center',
                    boxShadow: '0 8px 24px rgba(255,16,119,0.32)',
                  }}>
                    <span style={{ fontSize: 14, fontWeight: 900, color: '#fff', lineHeight: 1 }}>
                      {detailHelpers.dayNum(p.dateISO) || ''}
                    </span>
                    <span style={{
                      fontSize: 8, fontWeight: 700, color: 'rgba(255,255,255,0.7)',
                      letterSpacing: '0.05em', marginTop: 2,
                    }}>{detailHelpers.monthAbbr(p.dateISO)}</span>
                  </div>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <p style={{
                      margin: 0, fontSize: 13, fontWeight: 800, color: '#fff',
                      overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                    }}>{p.title}</p>
                    <p style={{ margin: '2px 0 0', fontSize: 11, color: 'rgba(255,255,255,0.5)' }}>
                      <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{p.time}</span> · {window.CP_FORMAT.won(p.price)}
                    </p>
                  </div>
                  <Icon name="chevron-right" size={14} color="rgba(255,255,255,0.4)" />
                </button>
              ))}
            </div>
          </div>
        )}

        {/* Tags */}
        {(club.genres || []).length > 0 && (
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            {club.genres.map(t => (
              <span key={t} style={{
                padding: '5px 10px', borderRadius: 999,
                background: 'rgba(255,255,255,0.06)',
                color: 'rgba(255,255,255,0.7)',
                fontSize: 10, fontWeight: 700, letterSpacing: '0.10em',
              }}>#{t}</span>
            ))}
          </div>
        )}

        {/* CTA — start a tour */}
        <button style={{
          all: 'unset', cursor: 'pointer',
          width: '100%', padding: '14px 0',
          borderRadius: 16, textAlign: 'center', boxSizing: 'border-box',
          fontSize: 14, fontWeight: 800, color: '#fff',
          background: 'linear-gradient(135deg,#FF1077 0%,#7B49FF 100%)',
          boxShadow: '0 8px 24px rgba(255,16,119,0.32)',
        }}>이 클럽으로 투어 시작</button>

        <div style={{ height: 12 }} />
      </div>

      <StickyDetailFooter
        favored={favored}
        onFavorite={() => setFavored(!favored)}
        primaryLabel="문의하기"
      />
    </div>
  );
}
window.ClubDetail = ClubDetail;

// ─────────────────────────────────────────────────────────────
// SEARCH MODAL — unchanged. Looks up parties + clubs by query.
// ─────────────────────────────────────────────────────────────
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
