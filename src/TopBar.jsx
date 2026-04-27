/* global React, Icon */
function TopBar({ title, location = '서울 · 강남구', dark = true }) {
  return (
    <div style={{
      position: 'sticky', top: 0, zIndex: 5,
      padding: '8px 16px 12px',
      background: 'rgba(12,12,18,0.92)',
      borderBottom: '1px solid rgba(255,255,255,0.06)',
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
    }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
        <Icon name="map-pin" size={16} color="#FF7AB8" />
        <div style={{ display: 'flex', flexDirection: 'column' }}>
          <span style={{
            fontSize: 10, fontWeight: 700, color: 'rgba(255,255,255,0.5)',
            letterSpacing: '0.12em', textTransform: 'uppercase',
          }}>{title || '오늘 밤'}</span>
          <span style={{ fontSize: 14, fontWeight: 700, color: '#fff' }}>{location}</span>
        </div>
        <Icon name="chevron-down" size={14} color="rgba(255,255,255,0.5)" />
      </div>
      <div style={{ display: 'flex', gap: 14, alignItems: 'center' }}>
        <button style={{ all: 'unset', cursor: 'pointer', position: 'relative', color: '#fff' }}>
          <Icon name="search" size={20} />
        </button>
        <button style={{ all: 'unset', cursor: 'pointer', position: 'relative', color: '#fff' }}>
          <Icon name="bell" size={20} />
          <span style={{
            position: 'absolute', top: -2, right: -2, width: 7, height: 7,
            borderRadius: 999, background: '#FF1077', boxShadow: '0 0 6px #FF1077',
          }} />
        </button>
      </div>
    </div>
  );
}
window.TopBar = TopBar;
