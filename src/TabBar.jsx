/* global React, Icon, GlowDot */
const { useState: useState_tab } = React;

// Per-tab tinted background — bottom menu shifts color with the active page.
const TAB_TINTS = {
  home:  { bg: 'linear-gradient(180deg, rgba(255,16,119,0.22) 0%, rgba(7,7,10,0.92) 100%)', border: 'rgba(255,16,119,0.35)', glow: 'rgba(255,16,119,0.25)' },
  party: { bg: 'linear-gradient(180deg, rgba(31,210,255,0.20) 0%, rgba(7,7,10,0.92) 100%)', border: 'rgba(31,210,255,0.35)', glow: 'rgba(31,210,255,0.25)' },
  club:  { bg: 'linear-gradient(180deg, rgba(123,73,255,0.22) 0%, rgba(7,7,10,0.92) 100%)', border: 'rgba(123,73,255,0.35)', glow: 'rgba(123,73,255,0.25)' },
  route: { bg: 'linear-gradient(180deg, rgba(200,255,26,0.18) 0%, rgba(7,7,10,0.92) 100%)', border: 'rgba(200,255,26,0.30)', glow: 'rgba(200,255,26,0.20)' },
  me:    { bg: 'linear-gradient(180deg, rgba(255,255,255,0.10) 0%, rgba(7,7,10,0.92) 100%)', border: 'rgba(255,255,255,0.16)', glow: 'rgba(255,255,255,0.10)' },
};
window.TAB_TINTS = TAB_TINTS;

function TabBar({ active, onChange }) {
  const tabs = [
    { id: 'home',  label: '홈',   icon: 'home',         tint: '#FF1077' },
    { id: 'party', label: '파티', icon: 'sparkles',     tint: '#1FD2FF' },
    { id: 'club',  label: '클럽', icon: 'disc-3',       tint: '#7B49FF' },
    { id: 'route', label: '루트', icon: 'route',        tint: '#C8FF1A' },
    { id: 'me',    label: '마이', icon: 'user-round',   tint: '#FFFFFF' },
  ];
  const skin = TAB_TINTS[active] || TAB_TINTS.home;
  return (
    <div style={{
      position: 'absolute', left: 0, right: 0, bottom: 0,
      paddingTop: 8,
      paddingBottom: 'calc(env(safe-area-inset-bottom, 0px) + 14px)',
      background: skin.bg,
      borderTop: `1px solid ${skin.border}`,
      boxShadow: `0 -12px 32px ${skin.glow}`,
      transition: 'background 320ms cubic-bezier(0.16,1,0.3,1), border-color 320ms, box-shadow 320ms',
      display: 'flex', zIndex: 110,
    }}>
      {tabs.map(t => {
        const sel = active === t.id;
        return (
          <button key={t.id} onClick={() => onChange(t.id)} style={{
            all: 'unset', cursor: 'pointer', flex: 1,
            display: 'flex', flexDirection: 'column', alignItems: 'center',
            gap: 4, padding: '6px 0 4px', position: 'relative',
            color: sel ? t.tint : 'rgba(255,255,255,0.5)',
            transition: 'color 220ms cubic-bezier(0.16,1,0.3,1)',
          }}>
            <Icon name={t.icon} size={22} />
            <span style={{ fontSize: 10, fontWeight: sel ? 700 : 600, letterSpacing: '0.02em' }}>{t.label}</span>
            {sel && <span style={{
              position: 'absolute', bottom: 0, width: 28, height: 3,
              borderRadius: 2, background: t.tint,
              boxShadow: `0 0 10px ${t.tint}`,
            }}/>}
          </button>
        );
      })}
    </div>
  );
}
window.TabBar = TabBar;
