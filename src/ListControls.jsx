/* global window, React */
// Lightweight list toolbar — used by Party + Club screens.
// Designed to be CHEAP to render: no animation effects, no SVG filters,
// no requestAnimationFrame. CSS-only transitions for sheet open/close.
//
// Provides: ListBar (4 buttons: 필터/정렬/지도/비교), FilterSheet, SortSheet,
// MapView, CompareTray.

const { useState: useStateLC2, useEffect: useEffectLC2 } = React;

// ──────────────────────────────────────────────────────────────────────
// LIST BAR — 4 segmented buttons under the header.
function ListBar({ active, count, onFilter, onSort, onMap, onCompare }) {
  const Btn = ({ icon, label, on, badge, onClick }) => (
    <button onClick={onClick} style={{
      all: 'unset', cursor: 'pointer', flex: 1,
      display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 5,
      padding: '11px 0', borderRadius: 11,
      background: on ? 'rgba(255,255,255,0.10)' : 'transparent',
      border: on ? '1px solid rgba(255,255,255,0.16)' : '1px solid transparent',
      color: on ? '#fff' : 'rgba(255,255,255,0.7)',
      fontSize: 12, fontWeight: 800, letterSpacing: '0.02em',
      position: 'relative',
    }}>
      <Icon name={icon} size={13} color={on ? '#fff' : 'rgba(255,255,255,0.7)'} />
      <span>{label}</span>
      {badge > 0 && (
        <span style={{
          minWidth: 16, height: 16, padding: '0 4px',
          display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
          borderRadius: 999, background: '#FF1077', color: '#fff',
          fontSize: 9, fontWeight: 900, fontFamily: 'JetBrains Mono, monospace',
          marginLeft: 2,
        }}>{badge}</span>
      )}
    </button>
  );

  return (
    <div style={{
      display: 'flex', gap: 4, padding: 4,
      margin: '0 16px 14px',
      background: '#15151E',
      border: '1px solid rgba(255,255,255,0.08)',
      borderRadius: 14,
    }}>
      <Btn icon="sliders-horizontal" label="필터"   on={active.filter}  badge={count?.filter} onClick={onFilter} />
      <Btn icon="arrow-up-down"      label="정렬"   on={active.sort}                          onClick={onSort} />
      <Btn icon="map"                label="지도"   on={active.map}                           onClick={onMap} />
      <Btn icon="git-compare"        label="비교"   on={active.compare}                       onClick={onCompare} />
    </div>
  );
}
window.ListBar = ListBar;

// ──────────────────────────────────────────────────────────────────────
// SHEET — bottom-anchored modal. Pure CSS animation via a class toggle.
function Sheet({ title, eyebrow, eyebrowColor, onClose, children, footer }) {
  // We don't use useEffect+rAF — instead just rely on the entrance animation
  // baked into the keyframes below. Cheap and reliable.
  return (
    <div style={{ position: 'absolute', inset: 0, zIndex: 220 }}>
      <div onClick={onClose} style={{
        position: 'absolute', inset: 0,
        background: 'rgba(0,0,0,0.7)',
        animation: 'cp-fade-in 200ms ease-out',
      }} />
      <div style={{
        position: 'absolute', left: 0, right: 0, bottom: 0,
        background: '#0E0E14',
        borderTopLeftRadius: 28, borderTopRightRadius: 28,
        border: '1px solid rgba(255,255,255,0.10)', borderBottom: 'none',
        maxHeight: '85%', display: 'flex', flexDirection: 'column',
        animation: 'cp-slide-up 280ms cubic-bezier(0.22, 1, 0.36, 1)',
        boxShadow: '0 -20px 60px rgba(0,0,0,0.6)',
      }}>
        <div style={{ display: 'flex', justifyContent: 'center', padding: '10px 0 0' }}>
          <div style={{ width: 40, height: 4, borderRadius: 999, background: 'rgba(255,255,255,0.2)' }} />
        </div>
        <div style={{ padding: '14px 20px 0' }}>
          {eyebrow && <div style={{ fontSize: 10, fontWeight: 700, color: eyebrowColor || 'rgba(255,255,255,0.55)', letterSpacing: '0.16em', textTransform: 'uppercase' }}>{eyebrow}</div>}
          <h2 style={{ margin: '4px 0 0', fontSize: 22, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em' }}>{title}</h2>
        </div>
        <div style={{ padding: '14px 20px 8px', overflowY: 'auto', flex: 1 }}>{children}</div>
        {footer && (
          <div style={{ padding: 14, borderTop: '1px solid rgba(255,255,255,0.06)' }}>
            {footer}
          </div>
        )}
      </div>
    </div>
  );
}
window.Sheet = Sheet;

// ──────────────────────────────────────────────────────────────────────
// FILTER SHEET — area + genre multi-select.
function FilterSheet({ areas, genres, value, onChange, onClear, onClose }) {
  const [draft, setDraft] = useStateLC2(value);

  const toggle = (key, v) => {
    const cur = draft[key] || [];
    setDraft({ ...draft, [key]: cur.includes(v) ? cur.filter(x => x !== v) : [...cur, v] });
  };

  const Chip = ({ on, onClick, color, children }) => (
    <button onClick={onClick} style={{
      all: 'unset', cursor: 'pointer',
      padding: '8px 14px', borderRadius: 999,
      background: on ? color : 'rgba(255,255,255,0.05)',
      border: on ? `1px solid ${color}` : '1px solid rgba(255,255,255,0.08)',
      color: on ? '#07070A' : 'rgba(255,255,255,0.78)',
      fontSize: 12, fontWeight: 800, letterSpacing: '0.02em',
    }}>{children}</button>
  );

  return (
    <Sheet
      eyebrow="FILTER · 필터"
      eyebrowColor="rgba(31,210,255,0.95)"
      title="조건으로 좁히기"
      onClose={onClose}
      footer={
        <div style={{ display: 'flex', gap: 8 }}>
          <button onClick={() => { onClear(); onClose(); }} style={{
            all: 'unset', cursor: 'pointer', flex: 1, height: 50,
            borderRadius: 14, textAlign: 'center',
            background: 'rgba(255,255,255,0.05)',
            border: '1px solid rgba(255,255,255,0.08)',
            color: 'rgba(255,255,255,0.7)', fontSize: 12, fontWeight: 800, letterSpacing: '0.04em',
          }}>전체 초기화</button>
          <button onClick={() => { onChange(draft); onClose(); }} style={{
            all: 'unset', cursor: 'pointer', flex: 2, height: 50,
            borderRadius: 14, textAlign: 'center',
            background: 'linear-gradient(180deg, #5AE3FF 0%, #00B8E6 100%)',
            color: '#07070A', fontSize: 13, fontWeight: 900, letterSpacing: '0.04em',
            boxShadow: '0 0 24px rgba(31,210,255,0.45)',
          }}>적용하기 →</button>
        </div>
      }
    >
      <div style={{ marginBottom: 18, paddingBottom: 14, borderBottom: '1px solid rgba(255,255,255,0.06)' }}>
        <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.45)', textTransform: 'uppercase' }}>AREA</div>
        <div style={{ fontSize: 14, fontWeight: 800, color: '#fff', marginTop: 2, marginBottom: 10 }}>지역</div>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
          {areas.map(a => (
            <Chip key={a} on={(draft.areas || []).includes(a)} color="#1FD2FF" onClick={() => toggle('areas', a)}>{a}</Chip>
          ))}
        </div>
      </div>
      <div>
        <div style={{ fontSize: 10, fontWeight: 700, letterSpacing: '0.12em', color: 'rgba(255,255,255,0.45)', textTransform: 'uppercase' }}>GENRE</div>
        <div style={{ fontSize: 14, fontWeight: 800, color: '#fff', marginTop: 2, marginBottom: 10 }}>장르</div>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
          {genres.map(g => (
            <Chip key={g} on={(draft.genres || []).includes(g)} color="#FF1077" onClick={() => toggle('genres', g)}>{g}</Chip>
          ))}
        </div>
      </div>
    </Sheet>
  );
}
window.FilterSheet = FilterSheet;

// ──────────────────────────────────────────────────────────────────────
// SORT SHEET — radio list.
function SortSheet({ value, onChange, onClose }) {
  const options = [
    { id: 'recent',   label: '최신순',          hint: '최근 업데이트된 순',        icon: 'clock' },
    { id: 'popular',  label: '인기순',          hint: '지금 많이 보고 있는 순',     icon: 'flame' },
    { id: 'priceAsc', label: '가격 낮은 순',     hint: '저렴한 곳 먼저',           icon: 'arrow-down' },
    { id: 'priceDesc',label: '가격 높은 순',     hint: '프리미엄 라인업 먼저',      icon: 'arrow-up' },
    { id: 'response', label: '응답 빠른 순',     hint: '문의 답이 빠른 클럽 먼저',   icon: 'zap' },
  ];

  return (
    <Sheet
      eyebrow="SORT · 정렬"
      eyebrowColor="rgba(255,16,119,0.95)"
      title="어떻게 보여줄까"
      onClose={onClose}
    >
      <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
        {options.map(o => {
          const on = value === o.id;
          return (
            <button key={o.id} onClick={() => { onChange(o.id); onClose(); }} style={{
              all: 'unset', cursor: 'pointer',
              display: 'flex', alignItems: 'center', gap: 12,
              padding: '14px 14px', borderRadius: 14,
              background: on ? 'rgba(255,16,119,0.10)' : 'rgba(255,255,255,0.04)',
              border: on ? '1px solid rgba(255,16,119,0.5)' : '1px solid rgba(255,255,255,0.06)',
            }}>
              <div style={{
                width: 36, height: 36, borderRadius: 10,
                background: on ? '#FF1077' : 'rgba(255,255,255,0.08)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: on ? '#fff' : 'rgba(255,255,255,0.7)',
              }}>
                <Icon name={o.icon} size={16} color={on ? '#fff' : 'rgba(255,255,255,0.7)'} />
              </div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 14, fontWeight: 800, color: '#fff' }}>{o.label}</div>
                <div style={{ fontSize: 11, color: 'rgba(255,255,255,0.55)', marginTop: 2 }}>{o.hint}</div>
              </div>
              {on && <div style={{ width: 22, height: 22, borderRadius: 999, background: '#FF1077', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <Icon name="check" size={13} color="#fff" />
              </div>}
            </button>
          );
        })}
      </div>
    </Sheet>
  );
}
window.SortSheet = SortSheet;

// ──────────────────────────────────────────────────────────────────────
// MAP VIEW — lightweight static "map" with coloured pins.
// No CosmicScale, no SVG filters — just CSS.
function MapView({ items, getPin, onSelect }) {
  // Distribute items in a faux radial layout. Each pin is a colored dot
  // with a tiny label. Click selects the item.
  return (
    <div style={{
      margin: '0 16px 14px',
      borderRadius: 18, height: 280, overflow: 'hidden', position: 'relative',
      background: 'radial-gradient(circle at center, #15151E 0%, #07070A 70%)',
      border: '1px solid rgba(255,255,255,0.08)',
    }}>
      {/* faux grid */}
      <div style={{
        position: 'absolute', inset: 0,
        backgroundImage: 'linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px)',
        backgroundSize: '40px 40px',
      }} />
      {/* you-are-here */}
      <div style={{
        position: 'absolute', left: '50%', top: '50%', transform: 'translate(-50%, -50%)',
        width: 14, height: 14, borderRadius: 999, background: '#1FD2FF',
        boxShadow: '0 0 0 4px rgba(31,210,255,0.25), 0 0 0 10px rgba(31,210,255,0.12)',
      }} />
      <div style={{
        position: 'absolute', left: '50%', top: '50%', transform: 'translate(-50%, calc(-50% + 24px))',
        fontSize: 9, fontWeight: 800, color: 'rgba(31,210,255,0.95)', letterSpacing: '0.16em',
      }}>YOU</div>

      {/* pins */}
      {items.map((it, i) => {
        const ang = (i / Math.max(items.length, 1)) * Math.PI * 2 - Math.PI / 2;
        const r = 60 + (i % 3) * 28;
        const pin = getPin(it);
        return (
          <button key={pin.id} onClick={() => onSelect && onSelect(it)} style={{
            all: 'unset', cursor: 'pointer', position: 'absolute',
            left: `calc(50% + ${Math.cos(ang)*r}px)`, top: `calc(50% + ${Math.sin(ang)*r}px)`,
            transform: 'translate(-50%, -100%)',
            display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2,
          }}>
            <span style={{
              padding: '4px 9px', borderRadius: 999,
              background: 'rgba(12,12,18,0.94)',
              border: `1px solid ${pin.color}`,
              color: '#fff', fontSize: 10, fontWeight: 800, letterSpacing: '0.02em',
              boxShadow: `0 0 10px ${pin.color}55`,
              whiteSpace: 'nowrap',
            }}>{pin.label}</span>
            <span style={{
              width: 8, height: 8, borderRadius: 999, background: pin.color,
              boxShadow: `0 0 6px ${pin.color}cc`,
            }} />
          </button>
        );
      })}
    </div>
  );
}
window.MapView = MapView;

// ──────────────────────────────────────────────────────────────────────
// COMPARE TRAY — bottom strip showing selected items + 비교하기 button.
function CompareTray({ items, max, onRemove, onClear, onCompare, onClose }) {
  return (
    <div style={{
      position: 'absolute', left: 0, right: 0, bottom: 84,
      background: 'rgba(7,7,10,0.96)',
      borderTop: '1px solid rgba(255,255,255,0.10)',
      padding: '12px 16px',
      zIndex: 105,
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 10 }}>
        <span style={{ fontSize: 11, fontWeight: 800, color: 'rgba(255,255,255,0.7)', letterSpacing: '0.08em' }}>
          비교 · {items.length} / {max}
        </span>
        <div style={{ display: 'flex', gap: 10 }}>
          {items.length > 0 && (
            <button onClick={onClear} style={{ all: 'unset', cursor: 'pointer', fontSize: 11, color: 'rgba(255,255,255,0.5)', fontWeight: 700 }}>지우기</button>
          )}
          <button onClick={onClose} style={{ all: 'unset', cursor: 'pointer', fontSize: 11, color: '#fff', fontWeight: 800 }}>닫기</button>
        </div>
      </div>
      <div style={{ display: 'flex', gap: 8, marginBottom: 10 }}>
        {Array.from({ length: max }).map((_, i) => {
          const it = items[i];
          if (!it) return (
            <div key={i} style={{
              flex: 1, height: 56,
              border: '1px dashed rgba(255,255,255,0.16)', borderRadius: 10,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              color: 'rgba(255,255,255,0.3)', fontSize: 11, fontWeight: 700,
            }}>+ 추가</div>
          );
          return (
            <button key={it.id} onClick={() => onRemove(it)} style={{
              all: 'unset', cursor: 'pointer', flex: 1, position: 'relative',
              height: 56, padding: '6px 8px',
              background: 'rgba(255,255,255,0.06)', borderRadius: 10,
              border: '1px solid rgba(255,16,119,0.4)',
              display: 'flex', flexDirection: 'column', justifyContent: 'center',
            }}>
              <div style={{ fontSize: 11, fontWeight: 800, color: '#fff', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{it.name || it.title}</div>
              <div style={{ fontSize: 9, color: 'rgba(255,255,255,0.5)', marginTop: 2, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{it.area || it.venue}</div>
              <div style={{
                position: 'absolute', top: 4, right: 4,
                width: 16, height: 16, borderRadius: 999,
                background: 'rgba(255,16,119,0.95)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: '#fff', fontSize: 11, fontWeight: 800,
              }}>×</div>
            </button>
          );
        })}
      </div>
      <button onClick={onCompare} disabled={items.length < 2} style={{
        all: 'unset', cursor: items.length < 2 ? 'not-allowed' : 'pointer',
        display: 'block', width: '100%', textAlign: 'center',
        padding: '13px 0', borderRadius: 12,
        background: items.length < 2 ? 'rgba(255,255,255,0.06)' : 'linear-gradient(180deg, #FF3D96 0%, #E60068 100%)',
        color: items.length < 2 ? 'rgba(255,255,255,0.4)' : '#fff',
        fontSize: 13, fontWeight: 900, letterSpacing: '0.04em',
        boxSizing: 'border-box',
      }}>
        비교하기 ({items.length} / {max})
      </button>
    </div>
  );
}
window.CompareTray = CompareTray;

// ──────────────────────────────────────────────────────────────────────
// COMPARE SHEET — full-screen comparison table.
function CompareSheet({ items, rows, eyebrow, title, onClose }) {
  return (
    <Sheet eyebrow={eyebrow || 'VS · 비교'} eyebrowColor="rgba(255,16,119,0.95)" title={title || '하나만 골라'} onClose={onClose}>
      <div style={{
        display: 'grid',
        gridTemplateColumns: `90px repeat(${items.length}, 1fr)`,
        gap: 0,
        border: '1px solid rgba(255,255,255,0.08)', borderRadius: 12, overflow: 'hidden',
      }}>
        <div style={{ padding: 10, background: 'rgba(255,255,255,0.04)' }}></div>
        {items.map(it => (
          <div key={it.id} style={{ padding: 10, background: 'rgba(255,255,255,0.04)', borderLeft: '1px solid rgba(255,255,255,0.06)' }}>
            <div style={{ fontSize: 12, fontWeight: 800, color: '#fff', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{it.name || it.title}</div>
            <div style={{ fontSize: 9, color: 'rgba(255,255,255,0.5)', marginTop: 2 }}>{it.area || it.venue}</div>
          </div>
        ))}
        {rows.map((r, i) => (
          <React.Fragment key={i}>
            <div style={{ padding: 10, fontSize: 11, color: 'rgba(255,255,255,0.5)', fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', background: i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent', borderTop: '1px solid rgba(255,255,255,0.06)' }}>{r.label}</div>
            {items.map(it => (
              <div key={it.id} style={{ padding: 10, fontSize: 12, color: '#fff', fontWeight: 700, borderLeft: '1px solid rgba(255,255,255,0.06)', borderTop: '1px solid rgba(255,255,255,0.06)', background: i % 2 ? 'rgba(255,255,255,0.02)' : 'transparent' }}>
                {r.render(it)}
              </div>
            ))}
          </React.Fragment>
        ))}
      </div>
    </Sheet>
  );
}
window.CompareSheet = CompareSheet;
