# LOGO GUIDELINE — SI-APOTEK

Pedoman identitas visual dan logo resmi SI-APOTEK.

## Identitas

- **Nama**: SI-APOTEK
- **Kepanjangan**: Sistem Informasi Manajemen Apotek
- **Konsep**: kapsul/pil (farmasi) dipadukan tanda plus (farmasi) dengan sentuhan modern & minimalis.

## Palet Warna

| Nama | HEX | Penggunaan |
|---|---|---|
| Hijau utama | `#16834B` | Kapsul, aksen, judul |
| Biru profesional | `#1976D2` | Gradien kapsul, aksen |
| Putih | `#FFFFFF` | Tanda plus, latar |
| Abu-abu gelap | `#344054` | Teks, subtitle |

## Tiga Alternatif Konsep (evaluasi)

1. **Konsep A — "Kapsul Silang"** (DIPILIH): kapsul vertikal gradien hijau→biru
   dengan tanda plus putih di tengah. Paling mudah dikenali, tegas, konsisten
   dengan identitas farmasi + teknologi.
2. **Konsep B — "Kapsul Miring"**: kapsul diputar 45° dengan belahan hijau/biru
   dan plus putih. Dinamis, namun kurang terbaca pada ukuran kecil.
3. **Konsep C — "Badge Pil"**: badge hijau dengan kapsul putih + plus biru.
   Bergaya ikon aplikasi, namun kurang membedakan dari ikon apotek umum.

**Keputusan**: Konsep A dipilih karena paling jelas di berbagai ukuran (favicon
16px hingga header cetak), simbol plus farmasi tetap terbaca, dan gradien
hijau-biru merepresentasikan farmasi + teknologi informasi.

## File Logo

| File | Keterangan |
|---|---|
| `logo-si-apotek.svg` | Logo utama (ikon), vektor asli |
| `logo-si-apotek.png` | PNG 1024×1024, latar transparan |
| `logo-si-apotek-horizontal.svg` | Ikon + wordmark "SI-APOTEK" |
| `favicon-si-apotek.png` | Favicon 512×512, latar transparan |
| `favicon-si-apotek.svg` | Sumber vektor favicon |

Lokasi: `app/assets/images/`.

## Penggunaan

- **Sidebar**: `logo-si-apotek.png` (34×34) di `.sidebar-brand`.
- **Halaman login**: `logo-si-apotek.png` (76×76) di `.auth-header`.
- **Dashboard**: `logo-si-apotek.png` (46×46) di header halaman.
- **Header laporan (kop)**: `logo-si-apotek.png` (60×60) di `report_header.php`.
- **Halaman cetak**: sama dengan header laporan.
- **Favicon**: `favicon-si-apotek.png` via `<link rel="icon">`.

## Aturan

- Jangan ubah proporsi logo (selalu skala proporsional).
- Jangan ubah warna logo di luar palet resmi.
- Gunakan SVG untuk kebutuhan vektor (cetak/print); PNG untuk tampilan raster.
- Logo pada latar gelap: gunakan versi dengan `drop-shadow` putih bila perlu.
