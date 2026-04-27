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

// ─────────────────────────────────────────────────────────────
// Shared list-screen primitives — match the Blade x-list-toolbar
// pattern used by parties.index / clubs.index.
// ─────────────────────────────────────────────────────────────
function ToolbarChip({ children, icon, iconColor = 'rgba(255,255,255,0.55)', onClick }) {
  return (
    <button onClick={onClick} style={{
      all: 'unset', cursor: onClick ? 'pointer' : 'default',
      display: 'inline-flex', alignItems: 'center', gap: 8,
      minHeight: 38, flexShrink: 0,
      padding: '8px 14px', borderRadius: 14,
      background: 'rgba(36,36,46,0.85)',
      border: '1px solid rgba(255,255,255,0.08)',
      color: '#E5E7EB', fontSize: 12, fontWeight: 700,
      whiteSpace: 'nowrap',
    }}>
      <span style={{ color: iconColor, display: 'inline-flex' }}>{icon}</span>
      {children}
    </button>
  );
}

function ListPill({ children, variant = 'default' }) {
  const palette = {
    default: { bg: 'rgba(255,255,255,0.06)', color: 'rgba(255,255,255,0.55)', border: 'rgba(255,255,255,0.04)' },
    blue:    { bg: 'rgba(59,130,246,0.10)',  color: '#60A5FA',                border: 'rgba(59,130,246,0.10)' },
    green:   { bg: 'rgba(16,185,129,0.15)',  color: '#6EE7B7',                border: 'rgba(16,185,129,0.20)' },
    cyan:    { bg: 'rgba(6,182,212,0.15)',   color: '#67E8F9',                border: 'rgba(6,182,212,0.20)' },
    pink:    { bg: 'rgba(236,72,153,0.15)',  color: '#F9A8D4',                border: 'rgba(236,72,153,0.20)' },
  };
  const v = palette[variant] || palette.default;
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: 4,
      padding: '2px 8px', borderRadius: 999,
      fontSize: 9, fontWeight: 700, lineHeight: '14px',
      whiteSpace: 'nowrap',
      background: v.bg, color: v.color,
      border: '1px solid ' + v.border,
    }}>{children}</span>
  );
}

function ListStatTile({ label, value }) {
  return (
    <div style={{
      borderRadius: 14, border: '1px solid rgba(255,255,255,0.05)',
      background: 'rgba(255,255,255,0.02)', padding: '8px 12px',
    }}>
      <p style={{ margin: 0, fontSize: 10, color: 'rgba(255,255,255,0.4)' }}>{label}</p>
      <p style={{ margin: '4px 0 0', fontSize: 11, fontWeight: 700, color: '#fff' }}>{value}</p>
    </div>
  );
}

function ListSectionHeader({ title, subtitle, count, suffix }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12 }}>
      <div style={{ minWidth: 0 }}>
        <h1 style={{ margin: 0, fontSize: 20, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em' }}>{title}</h1>
        <p style={{ margin: '4px 0 0', fontSize: 11, color: 'rgba(255,255,255,0.4)', lineHeight: 1.5 }}>{subtitle}</p>
      </div>
      <span style={{
        fontSize: 11, fontWeight: 600, color: 'rgba(255,255,255,0.5)',
        background: 'rgba(36,36,46,0.6)', padding: '4px 10px', borderRadius: 999,
        whiteSpace: 'nowrap', flexShrink: 0,
      }}>{count}{suffix}</span>
    </div>
  );
}

function SavedFilterNotice({ text }) {
  return (
    <div style={{ padding: '12px 16px 0', display: 'flex', flexDirection: 'column', alignItems: 'flex-start', gap: 8 }}>
      <div style={{
        display: 'inline-flex', alignItems: 'center', gap: 8,
        minHeight: 44, padding: '10px 16px', borderRadius: 16,
        border: '1px solid rgba(255,255,255,0.08)',
        background: 'rgba(36,36,46,0.85)',
        color: 'rgba(255,255,255,0.5)', fontSize: 12, fontWeight: 600,
      }}>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><path strokeLinecap="round" strokeLinejoin="round" d="M12 6v12m6-6H6"/></svg>
        저장 필터 준비중
      </div>
      <p style={{ margin: 0, fontSize: 11, color: 'rgba(255,255,255,0.4)', lineHeight: 1.5 }}>{text}</p>
    </div>
  );
}

// Bottom-sheet-style modal anchored center (matches the Blade x-teleport modal).
function CenterModal({ children, onClose, maxWidth = 420 }) {
  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 200 }}>
      <div onClick={onClose} style={{ position: 'absolute', inset: 0, background: 'rgba(0,0,0,0.6)' }} />
      <div style={{
        position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)',
        width: 'calc(100% - 32px)', maxWidth,
        maxHeight: 'calc(100vh - 64px)',
        background: '#0E0E14', borderRadius: 28,
        border: '1px solid rgba(255,255,255,0.06)',
        overflow: 'hidden', display: 'flex', flexDirection: 'column',
        boxShadow: '0 24px 64px rgba(0,0,0,0.7)',
      }}>{children}</div>
    </div>
  );
}

function FilterModal({
  title, sections, onReset, onClose,
  resetLabel = '초기화', closeLabel = '닫기', applyLabel = '결과 보기',
}) {
  return (
    <CenterModal onClose={onClose} maxWidth={420}>
      <div style={{ padding: '20px 16px 12px' }}>
        <div style={{ width: 48, height: 6, borderRadius: 999, background: 'rgba(255,255,255,0.10)', margin: '0 auto 16px' }} />
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div>
            <p style={{ margin: 0, fontSize: 11, fontWeight: 700, letterSpacing: '0.18em', color: 'rgba(255,255,255,0.4)', textTransform: 'uppercase' }}>빠른 압축</p>
            <h3 style={{ margin: '4px 0 0', fontSize: 17, fontWeight: 800, color: '#fff' }}>{title}</h3>
          </div>
          <button onClick={onClose} style={{
            all: 'unset', cursor: 'pointer', width: 40, height: 40, borderRadius: 999,
            border: '1px solid rgba(255,255,255,0.08)',
            display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'rgba(255,255,255,0.6)',
          }}><Icon name="x" size={16} /></button>
        </div>
      </div>
      <div style={{ flex: 1, overflowY: 'auto', padding: '0 16px 16px' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
          {sections.map(sec => (
            <div key={sec.label}>
              <p style={{ margin: '0 0 8px', fontSize: 11, fontWeight: 700, letterSpacing: '0.16em', color: 'rgba(255,255,255,0.4)', textTransform: 'uppercase' }}>{sec.label}</p>
              <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 4 }}>
                <button onClick={() => sec.onPick(null)} style={{
                  all: 'unset', cursor: 'pointer', flexShrink: 0,
                  padding: '7px 14px', borderRadius: 999,
                  fontSize: 11, fontWeight: 700,
                  background: sec.value == null ? 'linear-gradient(135deg,#FF1077,#7B49FF)' : 'rgba(36,36,46,0.6)',
                  color: sec.value == null ? '#fff' : 'rgba(255,255,255,0.4)',
                  border: '1px solid ' + (sec.value == null ? 'transparent' : 'rgba(255,255,255,0.04)'),
                  boxShadow: sec.value == null ? '0 4px 12px rgba(255,16,119,0.32)' : 'none',
                }}>{sec.allLabel || '전체'}</button>
                {sec.options.map(opt => (
                  <button key={opt} onClick={() => sec.onPick(opt)} style={{
                    all: 'unset', cursor: 'pointer', flexShrink: 0,
                    padding: '7px 14px', borderRadius: 999,
                    fontSize: 11, fontWeight: 700,
                    background: sec.value === opt ? 'rgba(236,72,153,0.15)' : 'rgba(36,36,46,0.6)',
                    color: sec.value === opt ? '#F9A8D4' : 'rgba(255,255,255,0.4)',
                    border: '1px solid ' + (sec.value === opt ? 'rgba(236,72,153,0.20)' : 'rgba(255,255,255,0.04)'),
                  }}>{opt}</button>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
      <div style={{
        borderTop: '1px solid rgba(255,255,255,0.06)',
        background: 'rgba(14,14,20,0.95)', padding: '16px',
      }}>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 8 }}>
          <button onClick={onReset} style={{
            all: 'unset', cursor: 'pointer', textAlign: 'center',
            padding: '12px 0', borderRadius: 14,
            border: '1px solid rgba(255,255,255,0.08)',
            background: 'rgba(36,36,46,0.6)',
            color: 'rgba(255,255,255,0.7)', fontSize: 12, fontWeight: 700,
          }}>{resetLabel}</button>
          <button onClick={onClose} style={{
            all: 'unset', cursor: 'pointer', textAlign: 'center',
            padding: '12px 0', borderRadius: 14,
            border: '1px solid rgba(255,255,255,0.08)',
            background: 'rgba(36,36,46,0.6)',
            color: 'rgba(255,255,255,0.7)', fontSize: 12, fontWeight: 700,
          }}>{closeLabel}</button>
          <button onClick={onClose} style={{
            all: 'unset', cursor: 'pointer', textAlign: 'center',
            padding: '12px 0', borderRadius: 14,
            background: 'linear-gradient(135deg,#FF1077,#7B49FF)',
            color: '#fff', fontSize: 12, fontWeight: 800,
            boxShadow: '0 8px 20px rgba(255,16,119,0.32)',
          }}>{applyLabel}</button>
        </div>
      </div>
    </CenterModal>
  );
}

function SortModal({ options, active, onPick, onClose }) {
  const entries = Object.entries(options);
  return (
    <CenterModal onClose={onClose} maxWidth={360}>
      <div style={{ padding: '20px 16px 12px' }}>
        <div style={{ width: 48, height: 6, borderRadius: 999, background: 'rgba(255,255,255,0.10)', margin: '0 auto 16px' }} />
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <div>
            <p style={{ margin: 0, fontSize: 11, fontWeight: 700, letterSpacing: '0.18em', color: 'rgba(255,255,255,0.4)', textTransform: 'uppercase' }}>리스트 정렬</p>
            <h3 style={{ margin: '4px 0 0', fontSize: 17, fontWeight: 800, color: '#fff' }}>정렬 선택</h3>
          </div>
          <button onClick={onClose} style={{
            all: 'unset', cursor: 'pointer', width: 40, height: 40, borderRadius: 999,
            border: '1px solid rgba(255,255,255,0.08)',
            display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'rgba(255,255,255,0.6)',
          }}><Icon name="x" size={16} /></button>
        </div>
      </div>
      <div style={{ padding: '0 16px 16px' }}>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {entries.map(([val, label]) => {
            const selected = active === val;
            return (
              <button key={val} onClick={() => onPick(val)} style={{
                all: 'unset', cursor: 'pointer',
                display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                padding: '12px 16px', borderRadius: 14,
                border: '1px solid ' + (selected ? '#FF1077' : 'rgba(255,255,255,0.08)'),
                background: selected ? 'rgba(255,16,119,0.10)' : 'rgba(36,36,46,0.6)',
                color: selected ? '#fff' : 'rgba(255,255,255,0.6)',
                fontSize: 13, fontWeight: 700,
              }}>
                <span>{label}</span>
                {selected && <Icon name="check" size={14} color="#FF7AB8" />}
              </button>
            );
          })}
        </div>
      </div>
      <div style={{
        borderTop: '1px solid rgba(255,255,255,0.06)',
        background: 'rgba(14,14,20,0.95)', padding: 16,
      }}>
        <button onClick={onClose} style={{
          all: 'unset', cursor: 'pointer', display: 'block', width: '100%',
          textAlign: 'center', padding: '12px 0', borderRadius: 14,
          border: '1px solid rgba(255,255,255,0.08)',
          background: 'rgba(36,36,46,0.6)',
          color: 'rgba(255,255,255,0.7)', fontSize: 12, fontWeight: 700,
          boxSizing: 'border-box',
        }}>닫기</button>
      </div>
    </CenterModal>
  );
}

// ─────────────────────────────────────────────────────────────
// MapPanel — matches partials/list-map-panel.blade.php.
// 좌표가 없어 id 시드로 의사 위치를 만들어 핀을 배치.
// ─────────────────────────────────────────────────────────────
function MapPanel({ title, subtitle, items, type, onOpen, onClose }) {
  const points = items.map((it, i) => {
    let s = 0;
    for (let k = 0; k < it.id.length; k++) s = (s * 31 + it.id.charCodeAt(k)) % 9973;
    const x = 12 + (s % 76);
    const y = 14 + ((s >> 3) % 72);
    return { item: it, index: i + 1, x, y };
  });
  const open = (it) => onOpen && onOpen(it);
  return (
    <div style={{
      borderRadius: 20, padding: 16, marginTop: 12,
      border: '1px solid rgba(255,255,255,0.06)',
      background: 'radial-gradient(circle at top right, rgba(34,211,238,0.14), transparent 38%), linear-gradient(180deg,#171727 0%,#10101a 100%)',
    }}>
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 12 }}>
        <div style={{ minWidth: 0 }}>
          <p style={{ margin: 0, fontSize: 11, fontWeight: 700, letterSpacing: '0.18em', color: '#67E8F9', textTransform: 'uppercase' }}>MAP MODE</p>
          <h2 style={{ margin: '4px 0 0', fontSize: 16, fontWeight: 800, color: '#fff', letterSpacing: '-0.01em' }}>{title}</h2>
          {subtitle && <p style={{ margin: '4px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.4)', lineHeight: 1.5 }}>{subtitle}</p>}
        </div>
        <div style={{ flexShrink: 0, display: 'flex', alignItems: 'center', gap: 8 }}>
          <div style={{
            borderRadius: 14, border: '1px solid rgba(255,255,255,0.06)',
            background: 'rgba(0,0,0,0.20)', padding: '6px 10px', textAlign: 'right',
          }}>
            <p style={{ margin: 0, fontSize: 10, color: 'rgba(255,255,255,0.4)' }}>좌표 표시</p>
            <p style={{ margin: '2px 0 0', fontSize: 14, fontWeight: 800, color: '#fff' }}>{points.length}</p>
          </div>
          {onClose && (
            <button onClick={onClose} style={{
              all: 'unset', cursor: 'pointer', width: 32, height: 32, borderRadius: 999,
              background: 'rgba(36,36,46,0.6)',
              border: '1px solid rgba(255,255,255,0.08)',
              display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'rgba(255,255,255,0.6)',
            }}><Icon name="x" size={14} /></button>
          )}
        </div>
      </div>

      {points.length > 0 ? (
        <>
          <div style={{
            marginTop: 16, padding: 12, borderRadius: 22,
            border: '1px solid rgba(255,255,255,0.06)',
            background: 'linear-gradient(180deg, rgba(12,18,28,0.95) 0%, rgba(18,28,42,0.92) 100%)',
          }}>
            <div style={{
              position: 'relative', aspectRatio: '0.96',
              borderRadius: 18, overflow: 'hidden',
              border: '1px solid rgba(34,211,238,0.10)',
              background: 'radial-gradient(circle at 20% 20%, rgba(34,211,238,0.22), transparent 24%), radial-gradient(circle at 80% 30%, rgba(59,130,246,0.18), transparent 24%), linear-gradient(180deg, rgba(12,18,28,0.96) 0%, rgba(13,22,38,0.96) 100%)',
            }}>
              <div style={{
                position: 'absolute', inset: 0, opacity: 0.4,
                backgroundImage: 'linear-gradient(to right, rgba(255,255,255,0.06) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.06) 1px, transparent 1px)',
                backgroundSize: '28px 28px',
              }} />
              <div style={{
                position: 'absolute', left: 14, top: 14,
                padding: '4px 10px', borderRadius: 999,
                background: 'rgba(255,255,255,0.06)',
                color: 'rgba(255,255,255,0.7)', fontSize: 10, fontWeight: 700,
              }}>{points.length}곳 표시중</div>
              {points.map(p => (
                <button key={p.item.id} onClick={() => open(p.item)} style={{
                  all: 'unset', cursor: 'pointer',
                  position: 'absolute',
                  left: `${p.x}%`, top: `${100 - p.y}%`,
                  transform: 'translate(-50%, -50%)',
                }}>
                  <div style={{
                    minWidth: 36, padding: '4px 8px', borderRadius: 999,
                    background: '#22D3EE',
                    border: '1px solid rgba(165,243,252,0.30)',
                    color: '#0F172A', fontSize: 11, fontWeight: 900,
                    boxShadow: '0 10px 24px rgba(34,211,238,0.30)',
                    display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                  }}>{p.index}</div>
                </button>
              ))}
            </div>
          </div>
          <div style={{ marginTop: 14, display: 'flex', flexDirection: 'column', gap: 8 }}>
            {points.slice(0, 6).map(p => (
              <button key={p.item.id} onClick={() => open(p.item)} style={{
                all: 'unset', cursor: 'pointer',
                display: 'flex', alignItems: 'center', gap: 12,
                padding: 12, borderRadius: 16,
                background: 'rgba(36,36,46,0.85)',
                border: '1px solid rgba(255,255,255,0.06)',
              }}>
                <div style={{
                  width: 40, height: 40, borderRadius: 14, flexShrink: 0,
                  background: 'rgba(34,211,238,0.12)',
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  color: '#A5F3FC', fontSize: 12, fontWeight: 900,
                }}>{p.index}</div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <p style={{
                    margin: 0, fontSize: 13, fontWeight: 700, color: '#fff',
                    overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                  }}>{type === 'party' ? p.item.title : p.item.name}</p>
                  <p style={{
                    margin: '2px 0 0', fontSize: 11, color: 'rgba(255,255,255,0.4)',
                    overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                  }}>{type === 'party' ? `${p.item.venue} · ${p.item.area}` : p.item.area}</p>
                </div>
                <div style={{ flexShrink: 0, textAlign: 'right' }}>
                  <p style={{ margin: 0, fontSize: 11, fontWeight: 700, color: 'rgba(255,255,255,0.7)' }}>
                    {type === 'party' ? window.CP_FORMAT.won(p.item.price) : `★ ${p.item.rating}`}
                  </p>
                </div>
              </button>
            ))}
          </div>
        </>
      ) : (
        <div style={{
          marginTop: 16, padding: '20px 16px', borderRadius: 22,
          border: '1px dashed rgba(255,255,255,0.08)',
          background: 'rgba(36,36,46,0.6)',
          fontSize: 12, color: 'rgba(255,255,255,0.4)', lineHeight: 1.7,
        }}>현재 필터 결과에는 좌표가 준비된 항목이 없어요. 필터를 바꾸거나 리스트 보기에서 상세를 먼저 확인해 주세요.</div>
      )}
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// CompareView — matches compare/index.blade.php.
// 2개 이상 선택 시 같은 라벨로 나란히 비교, 1개 이하면 안내.
// ─────────────────────────────────────────────────────────────
function CompareView({ type, items, labels, getFacts, getHeadline, getSubline, onRemove, onClear, onClose, onOpen }) {
  return (
    <div style={{
      position: 'fixed', inset: 0, zIndex: 200,
      background: 'rgba(0,0,0,0.6)',
      display: 'flex', alignItems: 'center', justifyContent: 'center',
      padding: 16,
    }}>
      <div onClick={onClose} style={{ position: 'absolute', inset: 0 }} />
      <div style={{
        position: 'relative',
        width: '100%', maxWidth: 480,
        maxHeight: 'calc(100vh - 32px)',
        background: '#0E0E14', borderRadius: 24,
        border: '1px solid rgba(255,255,255,0.06)',
        display: 'flex', flexDirection: 'column',
        overflow: 'hidden',
        boxShadow: '0 24px 64px rgba(0,0,0,0.7)',
      }}>
        <div style={{
          padding: '18px 16px 12px',
          borderBottom: '1px solid rgba(255,255,255,0.06)',
          display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 12,
        }}>
          <div style={{ minWidth: 0 }}>
            <h1 style={{
              margin: 0, fontSize: 20, fontWeight: 900, color: '#fff', letterSpacing: '-0.02em',
            }}>{type === 'club' ? '클럽 비교' : '파티 비교'}</h1>
            <p style={{ margin: '4px 0 0', fontSize: 11, color: 'rgba(255,255,255,0.4)' }}>
              최대 4개까지 같은 기준으로 한 번에 비교합니다.
            </p>
          </div>
          <div style={{ display: 'flex', gap: 8, flexShrink: 0 }}>
            {items.length > 0 && (
              <button onClick={onClear} style={{
                all: 'unset', cursor: 'pointer',
                padding: '6px 12px', borderRadius: 14,
                border: '1px solid rgba(255,255,255,0.08)',
                background: 'rgba(36,36,46,0.6)',
                color: 'rgba(255,255,255,0.6)', fontSize: 11, fontWeight: 700,
              }}>비우기</button>
            )}
            <button onClick={onClose} style={{
              all: 'unset', cursor: 'pointer', width: 32, height: 32, borderRadius: 999,
              background: 'rgba(36,36,46,0.6)',
              border: '1px solid rgba(255,255,255,0.08)',
              display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'rgba(255,255,255,0.6)',
            }}><Icon name="x" size={14} /></button>
          </div>
        </div>
        <div style={{ flex: 1, overflowY: 'auto', padding: 16 }}>
          {items.length < 2 ? (
            <div style={{
              padding: '28px 16px', textAlign: 'center',
              borderRadius: 16, background: '#15151E',
              border: '1px solid rgba(255,255,255,0.06)',
            }}>
              <p style={{ margin: 0, fontSize: 15, fontWeight: 800, color: '#fff' }}>비교 후보가 아직 부족해요</p>
              <p style={{ margin: '6px 0 0', fontSize: 12, color: 'rgba(255,255,255,0.5)' }}>
                리스트에서 2개 이상 담아야 차이를 바로 볼 수 있어요.
              </p>
              <button onClick={onClose} style={{
                all: 'unset', cursor: 'pointer', display: 'inline-block',
                marginTop: 14, padding: '12px 24px', borderRadius: 14,
                background: 'linear-gradient(135deg,#FF1077,#7B49FF)',
                color: '#fff', fontSize: 13, fontWeight: 800,
                boxShadow: '0 8px 20px rgba(255,16,119,0.32)',
              }}>후보 더 담기</button>
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
              {items.map(it => {
                const facts = getFacts(it);
                return (
                  <div key={it.id} style={{
                    borderRadius: 16, overflow: 'hidden',
                    background: '#15151E', border: '1px solid rgba(255,255,255,0.06)',
                  }}>
                    <div style={{ position: 'relative', height: 128 }}>
                      <ClubThumb id={it.id} tint={it.glow} size={420} ratio={0.30} fill />
                      <div style={{
                        position: 'absolute', inset: 0,
                        background: 'linear-gradient(0deg, #07070A 0%, rgba(7,7,10,0.45) 50%, transparent 100%)',
                      }} />
                      <div style={{
                        position: 'absolute', bottom: 12, left: 12, right: 12,
                        display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', gap: 12,
                      }}>
                        <div style={{ minWidth: 0 }}>
                          <p style={{
                            margin: 0, fontSize: 15, fontWeight: 800, color: '#fff',
                            overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                          }}>{getHeadline(it)}</p>
                          <p style={{
                            margin: '4px 0 0', fontSize: 11, color: 'rgba(255,255,255,0.7)',
                            overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                          }}>{getSubline(it)}</p>
                        </div>
                        <button onClick={() => onRemove(it.id)} style={{
                          all: 'unset', cursor: 'pointer',
                          padding: 8, borderRadius: 999,
                          background: 'rgba(0,0,0,0.45)', backdropFilter: 'blur(6px)',
                          color: '#fff', flexShrink: 0,
                          display: 'flex', alignItems: 'center', justifyContent: 'center',
                        }}><Icon name="x" size={14} /></button>
                      </div>
                    </div>
                    <div style={{ padding: 14, display: 'flex', flexDirection: 'column', gap: 6 }}>
                      {labels.map(label => (
                        <div key={label} style={{
                          display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12,
                          padding: '10px 12px', borderRadius: 14,
                          border: '1px solid rgba(255,255,255,0.05)',
                          background: 'rgba(255,255,255,0.02)',
                        }}>
                          <span style={{ fontSize: 11, color: 'rgba(255,255,255,0.4)' }}>{label}</span>
                          <span style={{
                            fontSize: 12, fontWeight: 700, color: '#fff',
                            textAlign: 'right', maxWidth: '60%',
                            overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                          }}>{facts[label] || '-'}</span>
                        </div>
                      ))}
                    </div>
                    <div style={{ padding: '0 14px 14px' }}>
                      <button onClick={() => { onOpen(it); onClose(); }} style={{
                        all: 'unset', cursor: 'pointer',
                        width: '100%', padding: '12px 0', borderRadius: 14,
                        textAlign: 'center', boxSizing: 'border-box',
                        background: 'linear-gradient(135deg,#FF1077,#7B49FF)',
                        color: '#fff', fontSize: 12, fontWeight: 800,
                        boxShadow: '0 8px 20px rgba(255,16,119,0.32)',
                      }}>상세 보기</button>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// PARTY SCREEN — list view matching parties/index.blade.php.
// ─────────────────────────────────────────────────────────────
function PartyScreen({ onOpenParty }) {
  const data = window.CP_DATA;
  const allParties = data && data.parties ? data.parties : [];
  const [filterOpen, setFilterOpen] = useStateP(false);
  const [sortOpen, setSortOpen] = useStateP(false);
  const [activeDate, setActiveDate] = useStateP(null);
  const [activeArea, setActiveArea] = useStateP(null);
  const [activeGenre, setActiveGenre] = useStateP(null);
  const [activeSort, setActiveSort] = useStateP('recommended');
  const [view, setView] = useStateP('list');
  const [compareIds, setCompareIds] = useStateP([]);
  const [compareOpen, setCompareOpen] = useStateP(false);
  const toggleCompare = (id) => setCompareIds(ids =>
    ids.includes(id) ? ids.filter(x => x !== id) : (ids.length >= 4 ? ids : [...ids, id])
  );

  const sortLabels = { recommended: '추천순', popular: '인기순', price_low: '가격 낮은순', soonest: '오늘 빠른순' };
  const dateOptions = ['오늘', '내일', '주말'];
  const areaOptions = ['청담동', '한남동', '이태원', '합정동', '강남', '홍대', '성수'];
  const genreOptions = ['TECHNO', 'HOUSE', 'DEEP', 'INDUSTRIAL', 'DISCO', 'MINIMAL', 'AMBIENT'];

  let items = allParties.slice();
  if (activeDate === '오늘') items = items.filter(p => p.date && p.date.includes('오늘'));
  if (activeDate === '내일') items = items.filter(p => p.date && p.date.includes('내일'));
  if (activeDate === '주말') items = items.filter(p => p.date && (p.date.includes('SAT') || p.date.includes('SUN')));
  if (activeArea) items = items.filter(p => p.area && p.area.includes(activeArea));
  if (activeGenre) items = items.filter(p => (p.genres || []).some(g => g.toUpperCase().includes(activeGenre)));
  if (activeSort === 'popular') items.sort((a, b) => (b.going || 0) - (a.going || 0));
  else if (activeSort === 'price_low') items.sort((a, b) => (a.price || 0) - (b.price || 0));
  else if (activeSort === 'soonest') items.sort((a, b) => (a.dateISO || '').localeCompare(b.dateISO || ''));

  const open = (it) => onOpenParty && onOpenParty(it);
  const activeFilterCount = (activeDate ? 1 : 0) + (activeArea ? 1 : 0) + (activeGenre ? 1 : 0);

  return (
    <div style={{ paddingBottom: 32 }}>
      <div style={{ padding: '20px 16px 0' }}>
        <ListSectionHeader
          title="파티"
          subtitle="날짜와 지역을 먼저 좁히고 빠르게 비교하세요."
          count={items.length}
          suffix="개"
        />
      </div>

      <div style={{
        position: 'sticky', top: 0, zIndex: 30,
        marginTop: 16, padding: '12px 16px',
        background: 'rgba(7,7,10,0.92)', backdropFilter: 'blur(12px)',
      }}>
        <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 4 }}>
          <ToolbarChip onClick={() => setFilterOpen(true)} icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 4.5h18M6.75 12h10.5M10.5 19.5h3"/></svg>}>
            필터{activeFilterCount > 0 && <span style={{ marginLeft: 4, padding: '0 6px', borderRadius: 999, background: 'rgba(255,16,119,0.20)', color: '#FFB8DA', fontSize: 10, fontWeight: 800 }}>{activeFilterCount}</span>}
          </ToolbarChip>
          <ToolbarChip onClick={() => setSortOpen(true)} icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5m-12 5.25h12m-8.25 5.25h8.25"/></svg>}>
            정렬 <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 11 }}>{sortLabels[activeSort]}</span>
          </ToolbarChip>
          <ToolbarChip onClick={() => setView(view === 'map' ? 'list' : 'map')} iconColor={view === 'map' ? '#22D3EE' : '#67E8F9'} icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>}>{view === 'map' ? '리스트' : '지도'}</ToolbarChip>
          <ToolbarChip onClick={() => setCompareOpen(true)} iconColor="#C4B5FD" icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M7.5 5.25v13.5m9-13.5v13.5M3.75 8.25h7.5m-7.5 7.5h7.5m1.5-7.5h7.5m-7.5 7.5h7.5"/></svg>}>비교 <span style={{ marginLeft: 4, padding: '1px 6px', borderRadius: 999, background: 'rgba(139,92,246,0.15)', color: '#C4B5FD', fontSize: 10, fontWeight: 800 }}>{compareIds.length}</span></ToolbarChip>
          <ToolbarChip iconColor="#F472B6" icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3.75 12h16.5m-16.5 0l3.75-3.75m-3.75 3.75l3.75 3.75"/></svg>}>{items.length} <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 11 }}>결과</span></ToolbarChip>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 8, padding: '0 4px', fontSize: 11, color: 'rgba(255,255,255,0.4)' }}>
          <p style={{ margin: 0 }}>필터와 정렬을 유지한 채 지도와 리스트를 전환할 수 있습니다.</p>
          <p style={{ margin: 0 }}>{sortLabels[activeSort]}</p>
        </div>
      </div>

      {view === 'map' && (
        <div style={{ padding: '0 16px' }}>
          <MapPanel
            title="파티 지도 보기"
            subtitle="현재 필터 결과를 좌표 기준으로 먼저 보고, 아래 리스트로 바로 비교할 수 있어요."
            items={items}
            type="party"
            onOpen={open}
            onClose={() => setView('list')}
          />
        </div>
      )}

      <SavedFilterNotice text="현재 조건을 저장해 두면 새 파티나 장소가 맞춰 등록될 때 알림으로 다시 받을 수 있습니다." />

      <div style={{ padding: '12px 16px 0', display: 'flex', flexDirection: 'column', gap: 10 }}>
        {items.length === 0 ? (
          <div style={{
            padding: '40px 16px', textAlign: 'center', color: 'rgba(255,255,255,0.4)',
            fontSize: 13, border: '1px dashed rgba(255,255,255,0.08)', borderRadius: 14,
          }}>조건에 맞는 파티가 없어요</div>
        ) : items.map(p => (
          <div key={p.id} style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            <button onClick={() => open(p)} style={{
              all: 'unset', cursor: 'pointer', display: 'block',
              borderRadius: 16, overflow: 'hidden',
              background: '#15151E', border: '1px solid rgba(255,255,255,0.06)',
            }}>
              <div style={{ display: 'flex', alignItems: 'stretch', gap: 14 }}>
                <div style={{ width: 112, flexShrink: 0, position: 'relative', minHeight: 92, background: '#0E0E14', overflow: 'hidden' }}>
                  <ClubThumb id={p.id} tint={p.glow} size={300} ratio={0.45} fill />
                  <FloorGlow tint={p.glow} intensity={0.5} />
                  {p.live && (
                    <div style={{
                      position: 'absolute', top: 8, left: 8,
                      padding: '3px 8px', borderRadius: 999,
                      background: 'rgba(255,16,119,0.92)',
                      color: '#fff', fontSize: 9, fontWeight: 800, letterSpacing: '0.10em',
                    }}>LIVE</div>
                  )}
                </div>
                <div style={{ flex: 1, padding: '12px 14px 12px 0', minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 8 }}>
                    <div style={{ minWidth: 0 }}>
                      <h3 style={{
                        margin: 0, fontSize: 13, fontWeight: 800, color: '#fff',
                        overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                      }}>{p.title}</h3>
                      <p style={{ margin: '4px 0 0', fontSize: 11, color: 'rgba(255,255,255,0.5)' }}>
                        {p.venue} · {p.area} · {(p.genres || []).join(' / ')}
                      </p>
                    </div>
                    <div style={{ flexShrink: 0, textAlign: 'right' }}>
                      <div style={{ fontSize: 12, fontWeight: 800, color: '#fff' }}>{window.CP_FORMAT.won(p.price)}</div>
                    </div>
                  </div>
                  <div style={{ marginTop: 8, display: 'flex', flexWrap: 'wrap', gap: 5 }}>
                    {p.date && p.date.includes('오늘') && <ListPill variant="green">오늘 진행</ListPill>}
                    {p.hot && <ListPill variant="cyan">PEAK</ListPill>}
                    <ListPill>문의 가능</ListPill>
                    <ListPill variant="blue">외국인 OK</ListPill>
                  </div>
                </div>
              </div>
              <div style={{ padding: '4px 14px 12px' }}>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
                  <ListStatTile label="가격" value={window.CP_FORMAT.won(p.price)} />
                  <ListStatTile label="플로어" value={`${Math.round((p.going || 0) / (p.capacity || 1) * 100)}% 채움`} />
                </div>
                <div style={{
                  marginTop: 8, display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                  fontSize: 10, color: 'rgba(255,255,255,0.4)',
                }}>
                  <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{p.time}</span>
                  <span style={{ color: '#FF7AB8' }}>{p.date}</span>
                </div>
              </div>
            </button>
            {(() => {
              const inCompare = compareIds.includes(p.id);
              return (
                <button onClick={() => toggleCompare(p.id)} style={{
                  all: 'unset', cursor: 'pointer', textAlign: 'center',
                  padding: '10px 0', borderRadius: 14,
                  border: '1px solid ' + (inCompare ? 'rgba(139,92,246,0.20)' : 'rgba(255,255,255,0.06)'),
                  background: inCompare ? 'rgba(139,92,246,0.10)' : 'rgba(36,36,46,0.6)',
                  color: inCompare ? '#C4B5FD' : 'rgba(255,255,255,0.7)',
                  fontSize: 12, fontWeight: 700,
                  display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
                }}>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><path strokeLinecap="round" strokeLinejoin="round" d="M7.5 5.25v13.5m9-13.5v13.5M3.75 8.25h7.5m-7.5 7.5h7.5m1.5-7.5h7.5m-7.5 7.5h7.5"/></svg>
                  {inCompare ? '비교함에서 제거' : '비교 추가'}
                </button>
              );
            })()}
          </div>
        ))}
      </div>

      {compareOpen && (
        <CompareView
          type="party"
          items={allParties.filter(p => compareIds.includes(p.id))}
          labels={['일정', '시간', '장소', '가격', '장르', '플로어']}
          getHeadline={(p) => p.title}
          getSubline={(p) => `${p.venue} · ${p.area}`}
          getFacts={(p) => ({
            '일정': p.date,
            '시간': p.time,
            '장소': `${p.venue} · ${p.area}`,
            '가격': window.CP_FORMAT.won(p.price),
            '장르': (p.genres || []).join(' · '),
            '플로어': `${Math.round((p.going || 0) / (p.capacity || 1) * 100)}% 채움`,
          })}
          onRemove={(id) => toggleCompare(id)}
          onClear={() => setCompareIds([])}
          onClose={() => setCompareOpen(false)}
          onOpen={open}
        />
      )}

      {filterOpen && (
        <FilterModal
          title="파티 필터"
          sections={[
            { label: '날짜', allLabel: '전체', value: activeDate, options: dateOptions, onPick: setActiveDate },
            { label: '지역', allLabel: '전체 지역', value: activeArea, options: areaOptions, onPick: setActiveArea },
            { label: '장르', allLabel: '전체 장르', value: activeGenre, options: genreOptions, onPick: setActiveGenre },
          ]}
          onReset={() => { setActiveDate(null); setActiveArea(null); setActiveGenre(null); }}
          onClose={() => setFilterOpen(false)}
        />
      )}
      {sortOpen && (
        <SortModal options={sortLabels} active={activeSort} onPick={(v) => { setActiveSort(v); setSortOpen(false); }} onClose={() => setSortOpen(false)} />
      )}
    </div>
  );
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

// ─────────────────────────────────────────────────────────────
// CLUB SCREEN — list view matching clubs/index.blade.php.
// ─────────────────────────────────────────────────────────────
function ClubScreen({ onOpenClub }) {
  const data = window.CP_DATA;
  const allClubs = data && data.clubs ? data.clubs : [];
  const [filterOpen, setFilterOpen] = useStateP(false);
  const [sortOpen, setSortOpen] = useStateP(false);
  const [activeArea, setActiveArea] = useStateP(null);
  const [activeGenre, setActiveGenre] = useStateP(null);
  const [foreignerOnly, setForeignerOnly] = useStateP(false);
  const [activeSort, setActiveSort] = useStateP('recommended');
  const [view, setView] = useStateP('list');
  const [compareIds, setCompareIds] = useStateP([]);
  const [compareOpen, setCompareOpen] = useStateP(false);
  const toggleCompare = (id) => setCompareIds(ids =>
    ids.includes(id) ? ids.filter(x => x !== id) : (ids.length >= 4 ? ids : [...ids, id])
  );

  const sortLabels = {
    recommended: '추천순',
    popular: '인기순',
    price_low: '가격 낮은순',
    response_fast: '응답 빠른순',
  };
  const areaOptions = ['홍대', '이태원', '강남', '청담', '한남', '합정', '신사', '성수', '신촌', '건대'];
  const genreOptions = ['Techno', 'House', 'Deep', 'Industrial', 'Disco', 'Minimal', 'R&B', 'Hip-hop'];

  const priceRange = (lvl) => {
    if (lvl >= 3) return '30,000~50,000원';
    if (lvl >= 2) return '15,000~30,000원';
    return '무료~15,000원';
  };

  let items = allClubs.slice();
  if (activeArea) items = items.filter(c => c.area && c.area.includes(activeArea));
  if (activeGenre) items = items.filter(c => (c.genres || []).some(g => g.toLowerCase().includes(activeGenre.toLowerCase())));
  if (activeSort === 'popular') items.sort((a, b) => (b.popularity || 0) - (a.popularity || 0));
  else if (activeSort === 'price_low') items.sort((a, b) => (a.priceLevel || 0) - (b.priceLevel || 0));
  else if (activeSort === 'response_fast') items.sort((a, b) => (a.responseMin || 999) - (b.responseMin || 999));

  const open = (c) => onOpenClub && onOpenClub(c);
  const activeFilterCount = (activeArea ? 1 : 0) + (activeGenre ? 1 : 0) + (foreignerOnly ? 1 : 0);

  return (
    <div style={{ paddingBottom: 32 }}>
      <div style={{ padding: '20px 16px 0' }}>
        <ListSectionHeader
          title="클럽"
          subtitle="지역, 장르, 응답 속도로 빠르게 압축하세요."
          count={items.length}
          suffix="곳"
        />
      </div>

      <div style={{
        position: 'sticky', top: 0, zIndex: 30,
        marginTop: 16, padding: '12px 16px',
        background: 'rgba(7,7,10,0.92)', backdropFilter: 'blur(12px)',
      }}>
        <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 4 }}>
          <ToolbarChip onClick={() => setFilterOpen(true)} icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3 4.5h18M6.75 12h10.5M10.5 19.5h3"/></svg>}>
            필터{activeFilterCount > 0 && <span style={{ marginLeft: 4, padding: '0 6px', borderRadius: 999, background: 'rgba(255,16,119,0.20)', color: '#FFB8DA', fontSize: 10, fontWeight: 800 }}>{activeFilterCount}</span>}
          </ToolbarChip>
          <ToolbarChip onClick={() => setSortOpen(true)} icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5m-12 5.25h12m-8.25 5.25h8.25"/></svg>}>
            정렬 <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 11 }}>{sortLabels[activeSort]}</span>
          </ToolbarChip>
          <ToolbarChip onClick={() => setView(view === 'map' ? 'list' : 'map')} iconColor={view === 'map' ? '#22D3EE' : '#67E8F9'} icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>}>{view === 'map' ? '리스트' : '지도'}</ToolbarChip>
          <ToolbarChip onClick={() => setCompareOpen(true)} iconColor="#C4B5FD" icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M7.5 5.25v13.5m9-13.5v13.5M3.75 8.25h7.5m-7.5 7.5h7.5m1.5-7.5h7.5m-7.5 7.5h7.5"/></svg>}>비교 <span style={{ marginLeft: 4, padding: '1px 6px', borderRadius: 999, background: 'rgba(139,92,246,0.15)', color: '#C4B5FD', fontSize: 10, fontWeight: 800 }}>{compareIds.length}</span></ToolbarChip>
          <ToolbarChip iconColor="#F472B6" icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M3.75 12h16.5m-16.5 0l3.75-3.75m-3.75 3.75l3.75 3.75"/></svg>}>{items.length} <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 11 }}>결과</span></ToolbarChip>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 8, padding: '0 4px', fontSize: 11, color: 'rgba(255,255,255,0.4)' }}>
          <p style={{ margin: 0 }}>필터와 정렬을 유지한 채 지도와 리스트를 전환할 수 있습니다.</p>
          <p style={{ margin: 0 }}>{sortLabels[activeSort]}</p>
        </div>
      </div>

      {view === 'map' && (
        <div style={{ padding: '0 16px' }}>
          <MapPanel
            title="클럽 지도 보기"
            subtitle="현재 필터 결과를 좌표 기준으로 먼저 보고, 아래 리스트로 바로 비교할 수 있어요."
            items={items}
            type="club"
            onOpen={open}
            onClose={() => setView('list')}
          />
        </div>
      )}

      <SavedFilterNotice text="현재 필터를 저장해 두면 다음 등록 항목이 조건과 맞을 때 알림으로 다시 받을 수 있습니다." />

      <div style={{ padding: '12px 16px 0', display: 'flex', flexDirection: 'column', gap: 10 }}>
        {items.length === 0 ? (
          <div style={{
            padding: '40px 16px', textAlign: 'center', color: 'rgba(255,255,255,0.4)',
            fontSize: 13, border: '1px dashed rgba(255,255,255,0.08)', borderRadius: 14,
          }}>조건에 맞는 클럽이 없어요</div>
        ) : items.map(c => (
          <div key={c.id} style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
            <button onClick={() => open(c)} style={{
              all: 'unset', cursor: 'pointer', display: 'block',
              borderRadius: 16, overflow: 'hidden',
              background: '#15151E', border: '1px solid rgba(255,255,255,0.06)',
            }}>
              <div style={{ display: 'flex', alignItems: 'stretch', gap: 14 }}>
                <div style={{ width: 112, flexShrink: 0, position: 'relative', minHeight: 92, background: '#0E0E14', overflow: 'hidden' }}>
                  <ClubThumb id={c.id} tint={c.glow} size={300} ratio={0.45} fill />
                  <FloorGlow tint={c.glow} intensity={0.45} />
                </div>
                <div style={{ flex: 1, padding: '12px 14px 12px 0', minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 8 }}>
                    <div style={{ minWidth: 0 }}>
                      <h3 style={{
                        margin: 0, fontSize: 13, fontWeight: 800, color: '#fff',
                        overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                      }}>{c.name}</h3>
                      <p style={{
                        margin: '4px 0 0', fontSize: 11, color: 'rgba(255,255,255,0.5)',
                        overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                      }}>{c.area.split(' ').pop()} · {(c.genres || []).join(' / ')}</p>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 1, flexShrink: 0 }}>
                      {[1,2,3,4,5].map(s => (
                        <Icon key={s} name="star" size={11}
                          color={s <= Math.round(c.rating) ? '#FBBF24' : 'rgba(255,255,255,0.15)'} />
                      ))}
                      <span style={{ marginLeft: 4, fontSize: 11, fontWeight: 600, color: 'rgba(255,255,255,0.7)' }}>{c.rating}</span>
                    </div>
                  </div>
                  <div style={{ marginTop: 8, display: 'flex', flexWrap: 'wrap', gap: 5 }}>
                    <ListPill>오늘 방문 추천</ListPill>
                    <ListPill>문의 가능</ListPill>
                    <ListPill variant="blue">외국인 OK</ListPill>
                  </div>
                </div>
              </div>
              <div style={{ padding: '4px 14px 12px' }}>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
                  <ListStatTile label="가격대" value={priceRange(c.priceLevel)} />
                  <ListStatTile label="응답 속도" value={`${c.responseMin}분 내`} />
                </div>
                <div style={{
                  marginTop: 8, display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                  fontSize: 10, color: 'rgba(255,255,255,0.4)',
                }}>
                  <span style={{ fontFamily: 'JetBrains Mono, monospace' }}>{c.openHours}</span>
                  <span style={{ color: '#FF7AB8' }}>00:00 이후 방문 추천</span>
                </div>
              </div>
            </button>
            {(() => {
              const inCompare = compareIds.includes(c.id);
              return (
                <button onClick={() => toggleCompare(c.id)} style={{
                  all: 'unset', cursor: 'pointer', textAlign: 'center',
                  padding: '10px 0', borderRadius: 14,
                  border: '1px solid ' + (inCompare ? 'rgba(139,92,246,0.20)' : 'rgba(255,255,255,0.06)'),
                  background: inCompare ? 'rgba(139,92,246,0.10)' : 'rgba(36,36,46,0.6)',
                  color: inCompare ? '#C4B5FD' : 'rgba(255,255,255,0.7)',
                  fontSize: 12, fontWeight: 700,
                  display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
                }}>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2"><path strokeLinecap="round" strokeLinejoin="round" d="M7.5 5.25v13.5m9-13.5v13.5M3.75 8.25h7.5m-7.5 7.5h7.5m1.5-7.5h7.5m-7.5 7.5h7.5"/></svg>
                  {inCompare ? '비교함에서 제거' : '비교 추가'}
                </button>
              );
            })()}
          </div>
        ))}
      </div>

      {compareOpen && (
        <CompareView
          type="club"
          items={allClubs.filter(c => compareIds.includes(c.id))}
          labels={['지역', '운영시간', '드레스', '가격대', '응답', '평점']}
          getHeadline={(c) => c.name}
          getSubline={(c) => `${c.area} · ${(c.genres || []).join(' · ')}`}
          getFacts={(c) => ({
            '지역': c.area,
            '운영시간': c.openHours,
            '드레스': c.dress || '자유',
            '가격대': c.priceLevel >= 3 ? '30,000~50,000원' : c.priceLevel >= 2 ? '15,000~30,000원' : '무료~15,000원',
            '응답': `${c.responseMin}분 내`,
            '평점': `★ ${c.rating} · ${c.reviews.toLocaleString()}`,
          })}
          onRemove={(id) => toggleCompare(id)}
          onClear={() => setCompareIds([])}
          onClose={() => setCompareOpen(false)}
          onOpen={open}
        />
      )}

      {filterOpen && (
        <FilterModal
          title="클럽 필터"
          sections={[
            { label: '추가 조건', allLabel: '전체', value: foreignerOnly ? '🌍 외국인 OK' : null,
              options: ['🌍 외국인 OK'],
              onPick: (v) => setForeignerOnly(v != null) },
            { label: '지역', allLabel: '전체 지역', value: activeArea, options: areaOptions, onPick: setActiveArea },
            { label: '장르', allLabel: '전체 장르', value: activeGenre, options: genreOptions, onPick: setActiveGenre },
          ]}
          onReset={() => { setActiveArea(null); setActiveGenre(null); setForeignerOnly(false); }}
          onClose={() => setFilterOpen(false)}
        />
      )}
      {sortOpen && (
        <SortModal options={sortLabels} active={activeSort} onPick={(v) => { setActiveSort(v); setSortOpen(false); }} onClose={() => setSortOpen(false)} />
      )}
    </div>
  );
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