/* global React */

// Shared: ONE global rAF drives ALL loader instances. Each subscriber re-renders
// at most ~12fps. When zero subscribers, the loop is fully stopped (no rAF).
// IntersectionObserver pauses individual loaders that are off-screen.
const __loaderTick = (() => {
  const subs = new Set();
  let raf = null, last = 0, start = performance.now();
  const FRAME = 1000 / 12;
  const tick = (now) => {
    if (now - last >= FRAME) {
      last = now;
      const t = (now - start) / 1000;
      subs.forEach(fn => fn(t));
    }
    raf = subs.size ? requestAnimationFrame(tick) : null;
  };
  return {
    add(fn) {
      subs.add(fn);
      if (!raf) raf = requestAnimationFrame(tick);
    },
    remove(fn) { subs.delete(fn); },
  };
})();

function useAnimatedT(speedMul) {
  const [ref, setRef] = useStateLoader(null);
  const [t, setT] = useStateLoader(0);
  useEffectLoader(() => {
    if (!ref) return;
    let onScreen = false;
    const onTick = (g) => { if (onScreen) setT(g * (speedMul || 1)); };
    const io = new IntersectionObserver(([e]) => {
      onScreen = e.isIntersecting;
      if (onScreen) __loaderTick.add(onTick);
      else          __loaderTick.remove(onTick);
    }, { threshold: 0.01 });
    io.observe(ref);
    return () => { io.disconnect(); __loaderTick.remove(onTick); };
  }, [ref, speedMul]);
  return [t, setRef];
}

const { useState: useStateLoader, useEffect: useEffectLoader, useRef: useRefLoader, useMemo: useMemoLoader } = React;

// ────────────────────────────────────────────────────────────────────────────
// 1. ORGANIC LOADER — gooey morphing blobs (CSS-only, no React rerender).
// ────────────────────────────────────────────────────────────────────────────
function OrganicLoader({ size = 140, tint = 'magenta' }) {
  const palette = {
    magenta: ['#FF1077', '#7B49FF', '#1FD2FF'],
    cyan:    ['#1FD2FF', '#7B49FF', '#FF1077'],
    violet:  ['#7B49FF', '#FF1077', '#1FD2FF'],
    lime:    ['#C8FF1A', '#1FD2FF', '#FF1077'],
  }[tint] || ['#FF1077', '#7B49FF', '#1FD2FF'];

  const blob = (i) => ({
    position: 'absolute', left: '50%', top: '50%',
    width: size * 0.42, height: size * 0.42,
    marginLeft: -size * 0.21, marginTop: -size * 0.21,
    borderRadius: '50%',
    background: palette[i],
    filter: `blur(${size * 0.05}px)`,
    mixBlendMode: 'screen',
    animation: `cp-blob-${i} ${4 + i * 0.7}s ease-in-out infinite`,
    willChange: 'transform',
  });

  return (
    <div style={{ position: 'relative', width: size, height: size }}>
      <style>{`
        @keyframes cp-blob-0 { 0%,100% { transform: translate(${size*0.18}px,${-size*0.10}px) scale(1); } 50% { transform: translate(${-size*0.14}px,${size*0.18}px) scale(1.10); } }
        @keyframes cp-blob-1 { 0%,100% { transform: translate(${-size*0.16}px,${size*0.14}px) scale(0.95); } 50% { transform: translate(${size*0.20}px,${size*0.06}px) scale(1.12); } }
        @keyframes cp-blob-2 { 0%,100% { transform: translate(${size*0.04}px,${size*0.18}px) scale(1.02); } 50% { transform: translate(${-size*0.18}px,${-size*0.16}px) scale(0.92); } }
      `}</style>
      <div style={{
        position: 'absolute', inset: 0, borderRadius: '50%',
        background: `radial-gradient(circle, ${palette[0]}33 0%, transparent 70%)`,
      }} />
      <div style={blob(0)} />
      <div style={blob(1)} />
      <div style={blob(2)} />
      <div style={{
        position: 'absolute', left: '50%', top: '50%',
        width: 5, height: 5, marginLeft: -2.5, marginTop: -2.5,
        borderRadius: '50%', background: '#fff',
      }} />
    </div>
  );
}

// ────────────────────────────────────────────────────────────────────────────
// 2. GLOBE LOADER — wireframe rotating globe (CSS-only animation).
// ────────────────────────────────────────────────────────────────────────────
function GlobeLoader({ size = 160, speed = 1, cities = true }) {
  const cx = size / 2, cy = size / 2, R = size * 0.42;
  const tilt = 0.35;

  const lats = [];
  for (let i = 1; i < 6; i++) {
    const y = (i / 6) * 2 - 1;
    const r = Math.sqrt(1 - y * y) * R;
    lats.push({ ry: r * tilt, rx: r, cy: cy + y * R });
  }
  // 6 longitude lines that we rotate via CSS group transform
  const longs = [0, 1, 2, 3, 4, 5].map(i => ({ angle: (i / 6) * 180, key: i }));

  const cityPositions = [
    { x: 0.78, y: 0.42, label: '서울' },
    { x: 0.30, y: 0.36, label: 'Berlin' },
    { x: 0.18, y: 0.55, label: 'NYC' },
    { x: 0.62, y: 0.74, label: 'SP' },
    { x: 0.88, y: 0.62, label: 'Tokyo' },
  ];

  const rotateDur = `${20 / (speed || 1)}s`;

  return (
    <div style={{ position: 'relative', width: size, height: size }}>
      <style>{`
        @keyframes cp-globe-spin { from { transform: rotateY(0deg); } to { transform: rotateY(360deg); } }
        @keyframes cp-city-pulse { 0%,100% { transform: scale(1); opacity: 0.85; } 50% { transform: scale(1.6); opacity: 0.3; } }
      `}</style>
      <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`} style={{ display: 'block', overflow: 'visible' }}>
        <defs>
          <radialGradient id="globe-fill" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stopColor="#1FD2FF" stopOpacity="0.04" />
            <stop offset="60%" stopColor="#7B49FF" stopOpacity="0.08" />
            <stop offset="100%" stopColor="#FF1077" stopOpacity="0.16" />
          </radialGradient>
          <radialGradient id="globe-glow" cx="50%" cy="50%" r="50%">
            <stop offset="60%" stopColor="#FF1077" stopOpacity="0" />
            <stop offset="100%" stopColor="#FF1077" stopOpacity="0.35" />
          </radialGradient>
        </defs>
        <circle cx={cx} cy={cy} r={R * 1.35} fill="url(#globe-glow)" />
        <circle cx={cx} cy={cy} r={R} fill="url(#globe-fill)" stroke="rgba(255,255,255,0.18)" strokeWidth="1" />
        {/* Latitude rings (static) */}
        {lats.map((l, i) => (
          <ellipse key={'lat' + i} cx={cx} cy={l.cy} rx={l.rx} ry={1} fill="none"
            stroke="rgba(255,255,255,0.18)" strokeWidth="0.7" />
        ))}
        {/* equator highlight */}
        <ellipse cx={cx} cy={cy} rx={R} ry={R * tilt} fill="none"
          stroke="rgba(31,210,255,0.55)" strokeWidth="1.2" />
      </svg>
      {/* Longitude wireframe — rotated by CSS, no React rerenders */}
      <div style={{
        position: 'absolute', inset: 0,
        transformStyle: 'preserve-3d',
        animation: `cp-globe-spin ${rotateDur} linear infinite`,
      }}>
        <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`} style={{ display: 'block', position: 'absolute', inset: 0 }}>
          {longs.map(l => (
            <ellipse key={'lon' + l.key} cx={cx} cy={cy} rx={R} ry={R}
              fill="none" stroke="rgba(31,210,255,0.6)" strokeWidth="0.8"
              transform={`rotate(${l.angle} ${cx} ${cy}) scale(${Math.abs(Math.cos(l.angle * Math.PI / 180))} 1)`} />
          ))}
        </svg>
      </div>
      {/* Cities — CSS pulse only */}
      {cities && cityPositions.map((c, i) => (
        <div key={i} style={{
          position: 'absolute',
          left: c.x * size, top: c.y * size,
          width: 5, height: 5, marginLeft: -2.5, marginTop: -2.5,
          borderRadius: '50%',
          background: '#FF1077',
          boxShadow: '0 0 6px #FF1077',
        }}>
          <div style={{
            position: 'absolute', inset: -4, borderRadius: '50%',
            border: '1px solid #FF1077',
            animation: `cp-city-pulse ${1.4 + i * 0.18}s ease-out infinite`,
          }} />
        </div>
      ))}
    </div>
  );
}

// ────────────────────────────────────────────────────────────────────────────
// 3. COSMIC SCALE — concentric expanding rings (CSS-only animation).
// ────────────────────────────────────────────────────────────────────────────
function CosmicScale({ size = 200, tint = 'magenta', label, sublabel, rings = 5 }) {
  const palette = {
    magenta: '#FF1077', cyan: '#1FD2FF', violet: '#7B49FF', lime: '#C8FF1A'
  };
  const color = palette[tint] || palette.magenta;
  const cx = size / 2, cy = size / 2;

  return (
    <div style={{ position: 'relative', width: size, height: size }}>
      <style>{`
        @keyframes cp-cosmic-ring {
          0%   { transform: scale(0.05); opacity: 0.7; }
          100% { transform: scale(1.0); opacity: 0; }
        }
      `}</style>
      <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`} style={{ display: 'block' }}>
        <defs>
          <radialGradient id={`cosmic-${tint}-bg`} cx="50%" cy="50%" r="50%">
            <stop offset="0%" stopColor={color} stopOpacity="0.55" />
            <stop offset="40%" stopColor={color} stopOpacity="0.10" />
            <stop offset="100%" stopColor="#000" stopOpacity="0" />
          </radialGradient>
        </defs>
        <circle cx={cx} cy={cy} r={size * 0.48} fill={`url(#cosmic-${tint}-bg)`} />
        {/* Static reference rings */}
        {[0.18, 0.30, 0.42].map((f, i) =>
          <circle key={'ref' + i} cx={cx} cy={cy} r={size * f} fill="none"
            stroke="rgba(255,255,255,0.10)" strokeWidth="1" strokeDasharray="2 4" />
        )}
        {/* tick marks N/E/S/W */}
        {[0, 90, 180, 270].map((deg) => {
          const rad = deg * Math.PI / 180;
          const x1 = cx + Math.cos(rad) * size * 0.43;
          const y1 = cy + Math.sin(rad) * size * 0.43;
          const x2 = cx + Math.cos(rad) * size * 0.46;
          const y2 = cy + Math.sin(rad) * size * 0.46;
          return <line key={deg} x1={x1} y1={y1} x2={x2} y2={y2}
            stroke="rgba(255,255,255,0.4)" strokeWidth="1.2" />;
        })}
        {/* center dot */}
        <circle cx={cx} cy={cy} r={5} fill={color}
          style={{ filter: `drop-shadow(0 0 8px ${color})` }} />
        <circle cx={cx} cy={cy} r={2} fill="#fff" />
      </svg>
      {/* Animated rings — CSS scale, ZERO React rerenders */}
      {Array.from({ length: rings }).map((_, i) => (
        <div key={i} style={{
          position: 'absolute', left: '50%', top: '50%',
          width: size * 0.96, height: size * 0.96,
          marginLeft: -size * 0.48, marginTop: -size * 0.48,
          borderRadius: '50%',
          border: `1.2px solid ${color}`,
          animation: `cp-cosmic-ring ${2.4}s ease-out infinite`,
          animationDelay: `${(i / rings) * 2.4}s`,
          willChange: 'transform, opacity',
          pointerEvents: 'none',
        }} />
      ))}
      {(label || sublabel) &&
        <div style={{
          position: 'absolute', inset: 0, display: 'flex',
          flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
          pointerEvents: 'none'
        }}>
          <div style={{ marginTop: size * 0.62 }}>
            {label && <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.45)', textAlign: 'center' }}>{label}</div>}
            {sublabel && <div style={{ fontSize: 13, fontWeight: 600, color: '#fff', textAlign: 'center', marginTop: 2 }}>{sublabel}</div>}
          </div>
        </div>
      }
    </div>
  );
}

// ────────────────────────────────────────────────────────────────────────────
// 4. CALC KEY — modular tactile button (calculator construction kit DNA)
//    Stacked layers: depth shadow + face gradient + inner highlight + shine.
//    We use these as the *chrome* for primary CTAs and key counters.
// ────────────────────────────────────────────────────────────────────────────
function CalcKey({
  children, size = 64, color = 'ink', glow = false,
  onClick, style = {}, height = null, width = null,
  pressed: pressedProp
}) {
  const [pressed, setPressed] = useStateLoader(false);
  const isPressed = pressedProp !== undefined ? pressedProp : pressed;
  const palettes = {
    ink: {
      face: 'linear-gradient(180deg, #2A2A38 0%, #15151E 100%)',
      shadow: 'rgba(0,0,0,0.7)',
      text: '#fff',
      shine: 'rgba(255,255,255,0.10)',
      side: '#0E0E14'
    },
    magenta: {
      face: 'linear-gradient(180deg, #FF3D96 0%, #E60068 100%)',
      shadow: 'rgba(255,16,119,0.5)',
      text: '#fff',
      shine: 'rgba(255,255,255,0.30)',
      side: '#7A0036'
    },
    cyan: {
      face: 'linear-gradient(180deg, #5AE3FF 0%, #00B8E6 100%)',
      shadow: 'rgba(31,210,255,0.5)',
      text: '#07070A',
      shine: 'rgba(255,255,255,0.45)',
      side: '#005266'
    },
    violet: {
      face: 'linear-gradient(180deg, #A07AFF 0%, #5E28E6 100%)',
      shadow: 'rgba(123,73,255,0.5)',
      text: '#fff',
      shine: 'rgba(255,255,255,0.30)',
      side: '#2D0F73'
    },
    lime: {
      face: 'linear-gradient(180deg, #DAFF5C 0%, #9FCC00 100%)',
      shadow: 'rgba(200,255,26,0.45)',
      text: '#07070A',
      shine: 'rgba(255,255,255,0.45)',
      side: '#5C7A00'
    },
    glass: {
      face: 'linear-gradient(180deg, rgba(255,255,255,0.10) 0%, rgba(255,255,255,0.04) 100%)',
      shadow: 'rgba(0,0,0,0.5)',
      text: '#fff',
      shine: 'rgba(255,255,255,0.18)',
      side: 'rgba(0,0,0,0.4)'
    }
  };
  const p = palettes[color] || palettes.ink;
  const w = width || size,h = height || size;
  const depth = isPressed ? 1 : 4;
  return (
    <button
      onClick={onClick}
      onMouseDown={() => setPressed(true)}
      onMouseUp={() => setPressed(false)}
      onMouseLeave={() => setPressed(false)}
      onTouchStart={() => setPressed(true)}
      onTouchEnd={() => setPressed(false)}
      style={{
        all: 'unset', cursor: 'pointer',
        display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
        width: w, height: h, borderRadius: typeof w === 'number' && typeof h === 'number' ? Math.min(w, h) * 0.28 : typeof h === 'number' ? h * 0.28 : 14,
        background: p.face, color: p.text,
        position: 'relative', boxSizing: 'border-box',
        transform: `translateY(${isPressed ? 3 : 0}px)`,
        boxShadow: [
        `0 ${depth}px 0 ${p.side}`,
        `0 ${depth + 2}px 12px ${p.shadow}`,
        glow && !isPressed ? `0 0 24px ${p.shadow}` : null,
        'inset 0 1px 0 ' + p.shine,
        'inset 0 -1px 0 rgba(0,0,0,0.25)'].
        filter(Boolean).join(', '),
        transition: 'transform 80ms cubic-bezier(0.16,1,0.3,1), box-shadow 80ms cubic-bezier(0.16,1,0.3,1)',
        fontFamily: 'Pretendard, system-ui',
        fontWeight: 700,
        ...style
      }}>
      
      {/* top shine */}
      <span style={{
        position: 'absolute', top: 1, left: '8%', right: '8%', height: '40%',
        borderRadius: '999px / 100%',
        background: `linear-gradient(180deg, ${p.shine} 0%, transparent 100%)`,
        pointerEvents: 'none', opacity: 0.7
      }} />
      <span style={{ position: 'relative', zIndex: 1 }}>{children}</span>
    </button>);

}

// ────────────────────────────────────────────────────────────────────────────
// 5. PULSE-WAVE — equalizer bars (used in Live Now badges, mini player)
// ────────────────────────────────────────────────────────────────────────────
function PulseBars({ count = 4, height = 14, color = '#FF1077' }) {
  return (
    <span style={{ display: 'inline-flex', alignItems: 'flex-end', gap: 2, height }}>
      {Array.from({ length: count }).map((_, i) =>
      <span key={i} style={{
        width: 2, background: color, borderRadius: 1,
        boxShadow: `0 0 4px ${color}`,
        animation: `cp-eq ${600 + i * 120}ms ease-in-out infinite alternate`,
        animationDelay: `${i * 90}ms`,
        height: '40%'
      }} />
      )}
    </span>);

}

// ────────────────────────────────────────────────────────────────────────────
// 6. AURORA — slow drifting gradient blobs (background atmosphere)
// ────────────────────────────────────────────────────────────────────────────
function Aurora({ intensity = 1 }) {
  return (
    <div aria-hidden style={{
      position: 'absolute', inset: 0, overflow: 'hidden', pointerEvents: 'none'
    }}>
      <div style={{
        position: 'absolute', width: '80%', height: '60%', top: '-15%', left: '-10%',
        background: 'radial-gradient(ellipse at center, rgba(255,16,119,0.45), transparent 60%)',
        filter: 'blur(28px)', opacity: 0.6 * intensity, willChange: 'transform',
        animation: 'cp-drift1 18s ease-in-out infinite alternate'
      }} />
      <div style={{
        position: 'absolute', width: '70%', height: '60%', bottom: '-20%', right: '-15%',
        background: 'radial-gradient(ellipse at center, rgba(31,210,255,0.45), transparent 60%)',
        filter: 'blur(28px)', opacity: 0.5 * intensity, willChange: 'transform',
        animation: 'cp-drift2 22s ease-in-out infinite alternate'
      }} />
      <div style={{
        position: 'absolute', width: '60%', height: '50%', top: '30%', right: '-10%',
        background: 'radial-gradient(ellipse at center, rgba(123,73,255,0.40), transparent 60%)',
        filter: 'blur(28px)', opacity: 0.45 * intensity, willChange: 'transform',
        animation: 'cp-drift3 26s ease-in-out infinite alternate'
      }} />
    </div>);

}

Object.assign(window, {
  OrganicLoader, GlobeLoader, CosmicScale, CalcKey, PulseBars, Aurora
});