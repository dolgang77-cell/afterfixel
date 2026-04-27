/* global React */
const { useState, useMemo } = React;

// ─────────────────────────────────────────────────────────────
// Atoms
// ─────────────────────────────────────────────────────────────
function GlowDot({ color = '#FF1077', size = 6 }) {
  return (
    <span style={{
      display: 'inline-block', width: size, height: size, borderRadius: 999,
      background: color, boxShadow: `0 0 8px ${color}`,
      animation: 'cp-pulse 1.6s cubic-bezier(0.34, 1.56, 0.64, 1) infinite',
    }} />
  );
}

function Eyebrow({ children, color = 'rgba(255,255,255,0.5)' }) {
  return <div style={{
    fontSize: 11, fontWeight: 700, letterSpacing: '0.12em',
    textTransform: 'uppercase', color,
  }}>{children}</div>;
}

function Pill({ children, bg = 'rgba(0,0,0,0.55)', color = '#fff', glass = false, style = {} }) {
  return <span style={{
    display: 'inline-flex', alignItems: 'center', gap: 6,
    padding: '5px 10px', borderRadius: 999, background: bg,
    backdropFilter: glass ? 'blur(12px) saturate(140%)' : undefined,
    WebkitBackdropFilter: glass ? 'blur(12px) saturate(140%)' : undefined,
    color, fontSize: 11, fontWeight: 600, letterSpacing: '0.02em',
    border: glass ? '1px solid rgba(255,255,255,0.10)' : 'none',
    ...style,
  }}>{children}</span>;
}

function GenreTag({ children }) {
  return <span style={{
    display: 'inline-flex', padding: '5px 10px', borderRadius: 999,
    background: 'rgba(255,255,255,0.06)', color: 'rgba(255,255,255,0.72)',
    fontSize: 11, fontWeight: 600,
  }}>{children}</span>;
}

// Inline SVG icon library — covers everything used in the app.
const ICON_PATHS = {
  'home':         '<path d="M3 9.5L12 3l9 6.5V20a2 2 0 0 1-2 2h-4v-7h-6v7H5a2 2 0 0 1-2-2V9.5z"/>',
  'sparkles':     '<path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/><path d="M19 14l.7 2.3L22 17l-2.3.7L19 20l-.7-2.3L16 17l2.3-.7L19 14z"/><path d="M5 14l.6 1.9L7.5 16.5l-1.9.6L5 19l-.6-1.9L2.5 16.5l1.9-.6L5 14z"/>',
  'disc-3':       '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2"/><path d="M6.5 6.5l3 3M14.5 14.5l3 3M17.5 6.5l-3 3M9.5 14.5l-3 3"/>',
  'route':        '<circle cx="6" cy="19" r="3"/><circle cx="18" cy="5" r="3"/><path d="M9 19h4a4 4 0 0 0 0-8h-2a4 4 0 0 1 0-8h4"/>',
  'user-round':   '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>',
  'search':       '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.5-4.5"/>',
  'bell':         '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9z"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
  'chevron-down': '<path d="M6 9l6 6 6-6"/>',
  'chevron-up':   '<path d="M18 15l-6-6-6 6"/>',
  'chevron-left': '<path d="M15 6l-6 6 6 6"/>',
  'chevron-right':'<path d="M9 6l6 6-6 6"/>',
  'map-pin':      '<path d="M20 10c0 6-8 13-8 13s-8-7-8-13a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
  'map':          '<path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3z"/><path d="M9 3v15M15 6v15"/>',
  'star':         '<path d="M12 2.5l3 6.5 7 .8-5.2 4.7L18 21l-6-3.4L6 21l1.2-6.5L2 9.8l7-.8z" stroke-linejoin="round"/>',
  'play':         '<path d="M6 4l14 8L6 20z" stroke-linejoin="round"/>',
  'bookmark':     '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
  'share-2':      '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/>',
  'x':            '<path d="M18 6L6 18M6 6l12 12"/>',
  'arrow-down':   '<path d="M12 5v14M6 13l6 6 6-6"/>',
  'arrow-up-left':'<path d="M19 19L5 5M5 13V5h8"/>',
  'check':        '<path d="M5 12l5 5 9-11" stroke-linejoin="round"/>',
  'clock':        '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
  'music-2':      '<circle cx="8" cy="18" r="3"/><circle cx="18" cy="16" r="3"/><path d="M11 18V5l10-2v13"/>',
  'shield-check': '<path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
  'shirt':        '<path d="M16 3l4 2-2 4-2-1v13H8V8L6 9 4 5l4-2 2 2a3 3 0 0 0 4 0z"/>',
  'phone':        '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.4 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/>',
  'log-out':      '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
  'settings-2':   '<path d="M20 7H7M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>',
  'globe':        '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
  'users':        '<circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v1"/><circle cx="17" cy="7" r="3"/><path d="M22 18v-1a4 4 0 0 0-3-3.9"/>',
  'tag':          '<path d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
  'flame':        '<path d="M12 2s4 5 4 9a4 4 0 0 1-8 0c0-1 .5-2 .5-2S6 12 6 14a6 6 0 0 0 12 0c0-5-6-12-6-12z"/>',
  'grid-3x3':     '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>',
  'plus':         '<path d="M12 5v14M5 12h14"/>',
};

function Icon({ name, size = 20, color = 'currentColor' }) {
  const path = ICON_PATHS[name] || ICON_PATHS['x'];
  return (
    <svg
      width={size} height={size} viewBox="0 0 24 24"
      fill="none" stroke={color} strokeWidth="2"
      strokeLinecap="round" strokeLinejoin="round"
      style={{ display: 'inline-block', flexShrink: 0, verticalAlign: 'middle' }}
      dangerouslySetInnerHTML={{ __html: path }}
    />
  );
}

function FloorGlow({ tint = 'magenta', intensity = 0.35 }) {
  const colors = {
    magenta: 'rgba(255,16,119,' + intensity + ')',
    cyan:    'rgba(31,210,255,' + intensity + ')',
    violet:  'rgba(123,73,255,' + intensity + ')',
    lime:    'rgba(200,255,26,' + (intensity * 0.6) + ')',
  };
  return <div style={{
    position: 'absolute', inset: 0,
    background: `radial-gradient(ellipse 90% 60% at 50% 100%, ${colors[tint]} 0%, rgba(123,73,255,0.15) 40%, transparent 75%)`,
    pointerEvents: 'none', zIndex: 0,
  }} />;
}

function ProtectionScrim({ from = 'bottom' }) {
  const dir = from === 'bottom' ? 0 : 180;
  return <div style={{
    position: 'absolute', inset: 0,
    background: `linear-gradient(${dir}deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.85) 100%)`,
    pointerEvents: 'none',
  }} />;
}

// ─────────────────────────────────────────────────────────────
// ClubThumb — stylized "photo" of a club floor.
//   Built from SVG: deep gradient sky, light beams from a DJ
//   booth silhouette, bokeh haze, crowd silhouettes. Seeded by
//   id so each club/party gets a unique-but-consistent look.
// ─────────────────────────────────────────────────────────────
// 클럽 사진 풀 (Unsplash 무료, 자유 사용). id 해시로 결정적으로 한 장 선택 →
// 같은 클럽은 항상 같은 사진. 다른 클럽은 다양한 사진.
const CLUB_PHOTOS = [
  'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1581974944026-5d6ed762f617?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1571266028243-e4733b0f0bb0?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1545128485-c400e7702796?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1485872299829-c673f5194813?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1551836022-deb4988cc6c0?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1574391884720-bbc049ec09ad?auto=format&fit=crop&w=600&q=75',
  'https://images.unsplash.com/photo-1565035010268-a3816f98589a?auto=format&fit=crop&w=600&q=75',
];
function clubPhotoFor(id) {
  let h = 5381;
  for (let i = 0; i < id.length; i++) h = ((h << 5) + h + id.charCodeAt(i)) | 0;
  return CLUB_PHOTOS[Math.abs(h) % CLUB_PHOTOS.length];
}

function ClubThumb({ id = 'c1', tint = 'magenta', size = 200, ratio = 1, fill = false, style = {} }) {
  // 실제 클럽 사진 + 톤 오버레이로 클럽파티 룩 유지 (이전 CSS-only 플레이스홀더 → 진짜 사진)
  const w = size, h = Math.round(size * ratio);
  const photo = clubPhotoFor(id);
  const tintColor = tint === 'magenta' ? 'rgba(255,16,119,0.35)'
                  : tint === 'cyan'    ? 'rgba(31,210,255,0.30)'
                  :                      'rgba(123,73,255,0.32)';

  const wrapStyle = fill
    ? { position: 'absolute', inset: 0 }
    : { width: w, height: h, position: 'relative' };

  return (
    <div style={{
      ...wrapStyle,
      borderRadius: 'inherit',
      overflow: 'hidden',
      background: '#0E0E14',
      ...style,
    }}>
      {/* 클럽 사진 */}
      <img
        src={photo}
        alt=""
        loading="lazy"
        decoding="async"
        style={{
          position: 'absolute', inset: 0,
          width: '100%', height: '100%',
          objectFit: 'cover',
          filter: 'saturate(1.05) contrast(1.05)',
          display: 'block',
        }}
      />
      {/* 색조 오버레이 — 각 클럽 톤(magenta/cyan/violet) 살짝 입힘 */}
      <div style={{
        position: 'absolute', inset: 0,
        background: `linear-gradient(180deg, ${tintColor} 0%, transparent 50%, rgba(7,7,10,0.45) 100%)`,
        mixBlendMode: 'multiply',
      }} />
      {/* 하단 비네트 — 텍스트 가독성 */}
      <div style={{
        position: 'absolute', left: 0, right: 0, bottom: 0, height: '40%',
        background: 'linear-gradient(0deg, rgba(7,7,10,0.7) 0%, transparent 100%)',
      }} />
    </div>
  );
}
const MemoClubThumb = React.memo(ClubThumb);

Object.assign(window, { GlowDot, Eyebrow, Pill, GenreTag, Icon, FloorGlow, ProtectionScrim, ClubThumb: MemoClubThumb });
