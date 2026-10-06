"""Generate original placeholder covers and a quiet audio tone for the demo catalog."""

from html import escape
from math import pi, sin
from pathlib import Path
import struct
import wave

root = Path(__file__).resolve().parents[1] / 'public'
covers = root / 'images' / 'demo'
audio = root / 'audio'
covers.mkdir(parents=True, exist_ok=True)
audio.mkdir(parents=True, exist_ok=True)

releases = [
    ('AFTERIMAGE', 'MIRA SOL', '#cae4a3', '#304d38', 'circle'),
    ('NOTHING STAYS STILL', 'THE NIGHT INDEX', '#8a96af', '#202630', 'bars'),
    ('BLUE HOUR NOTES', 'NALA JUNE', '#e7b99b', '#403332', 'orb'),
    ('FORMS IN MOTION', 'SOFT GEOMETRY', '#d9d0f2', '#38324c', 'grid'),
    ('SHORELINE RADIO', 'LOW TIDE ASSEMBLY', '#b5d3d0', '#233d45', 'waves'),
    ('THE LAST LIGHT', 'ARLO VALE', '#e1cf93', '#413927', 'sun'),
    ('SMALL HOURS', 'MIRA SOL', '#b3badc', '#292d51', 'arc'),
    ('NO FIXED ADDRESS', 'THE NIGHT INDEX', '#d3ada6', '#4d312f', 'steps'),
    ('33½ STUDIO TEE', 'MERCHANDISE', '#ddd9cf', '#252525', 'tee'),
    ('LISTENING ROOM TOTE', 'MERCHANDISE', '#d4d8c0', '#293529', 'tote'),
    ('SIDE B POSTER', 'MERCHANDISE', '#f2d5a6', '#513f32', 'poster'),
]

art = {
    'circle': '<circle cx="300" cy="285" r="205" fill="none" stroke="{dark}" stroke-width="70"/><circle cx="300" cy="285" r="85" fill="{dark}"/>',
    'bars': ''.join(f'<rect x="{80+i*57}" y="{100+i%3*48}" width="32" height="{350-i%3*76}" fill="{{dark}}"/>' for i in range(9)),
    'orb': '<circle cx="300" cy="270" r="205" fill="{dark}"/><circle cx="390" cy="200" r="160" fill="{bg}"/><circle cx="260" cy="315" r="55" fill="{bg}"/>',
    'grid': ''.join(f'<line x1="{x}" y1="80" x2="{x+150}" y2="480" stroke="{{dark}}" stroke-width="10"/>' for x in range(-100, 600, 65)),
    'waves': ''.join(f'<path d="M-30 {y} Q120 {y-100} 300 {y} T630 {y}" fill="none" stroke="{{dark}}" stroke-width="17"/>' for y in range(130, 510, 55)),
    'sun': '<circle cx="300" cy="275" r="170" fill="{dark}"/><rect x="0" y="320" width="600" height="210" fill="{bg}"/><path d="M0 320H600" stroke="{dark}" stroke-width="18"/>',
    'arc': ''.join(f'<circle cx="300" cy="470" r="{r}" fill="none" stroke="{{dark}}" stroke-width="14"/>' for r in range(100, 450, 47)),
    'steps': ''.join(f'<rect x="{80+i*58}" y="{110+i*37}" width="70" height="{340-i*37}" fill="{{dark}}"/>' for i in range(8)),
    'tee': '<path d="M175 130 L240 95 Q300 155 360 95 L425 130 L500 215 L435 265 L400 230 L400 475 L200 475 L200 230 L165 265 L100 215 Z" fill="{dark}"/><text x="300" y="340" fill="{bg}" font-size="72" font-weight="bold" text-anchor="middle">33½</text>',
    'tote': '<rect x="165" y="205" width="270" height="265" rx="12" fill="{dark}"/><path d="M225 220V160 Q225 95 300 95 Q375 95 375 160V220" fill="none" stroke="{dark}" stroke-width="24"/><text x="300" y="355" fill="{bg}" font-size="62" font-weight="bold" text-anchor="middle">33½</text>',
    'poster': '<rect x="150" y="70" width="300" height="430" fill="{dark}"/><circle cx="300" cy="260" r="110" fill="none" stroke="{bg}" stroke-width="38"/><text x="300" y="440" fill="{bg}" font-size="54" font-weight="bold" text-anchor="middle">SIDE B</text>',
}

for number, (title, artist, bg, dark, style) in enumerate(releases, 1):
    graphic = art[style].format(bg=bg, dark=dark)
    svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" role="img" aria-label="{escape(title)} demo artwork">
<rect width="600" height="600" fill="{bg}"/>
{graphic}
<rect y="510" width="600" height="90" fill="{dark}"/>
<text x="34" y="548" fill="{bg}" font-family="Arial,sans-serif" font-size="22" font-weight="bold" letter-spacing="2">{escape(title)}</text>
<text x="34" y="577" fill="{bg}" font-family="Arial,sans-serif" font-size="13" letter-spacing="2">{escape(artist)} · 33½</text>
</svg>'''
    (covers / f'cover-{number}.svg').write_text(svg, encoding='utf-8')

(covers / 'placeholder.svg').write_text('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600"><rect width="600" height="600" fill="#deded6"/><text x="300" y="320" fill="#20201d" text-anchor="middle" font-family="Arial" font-size="100" font-weight="bold">33½</text></svg>', encoding='utf-8')

rate = 16000
notes = [220, 261.63, 293.66, 329.63, 293.66, 261.63, 220, 196]
with wave.open(str(audio / 'demo-preview.wav'), 'wb') as sound:
    sound.setnchannels(1)
    sound.setsampwidth(2)
    sound.setframerate(rate)
    for sample in range(rate * 8):
        second = sample / rate
        note = notes[min(int(second), len(notes) - 1)]
        envelope = min(1, (second % 1) * 8, (1 - second % 1) * 5) * 0.11
        value = sin(2 * pi * note * second) * envelope + sin(2 * pi * note * 2 * second) * envelope * 0.15
        sound.writeframesraw(struct.pack('<h', int(value * 32767)))
