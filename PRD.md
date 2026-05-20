# PRD: Centric Ecosystem Landing Page

**Status:** Draft v1.0  
**Last Updated:** 20 Mei 2026  
**Owner:** Product Team

---

## 1. Executive Summary

Remake halaman utama `customercentric.id` dari halaman SMM (Social Media Booster) menjadi **brand showcase landing page** yang memperkenalkan seluruh ekosistem produk Centric. Halaman ini bertindak sebagai pintu masuk utama (hub) yang mengarahkan pengunjung ke masing-masing produk sesuai kebutuhan mereka.

## 2. Goals & Objectives

- Menampilkan ke-4 produk Centric dalam satu halaman yang kohesif
- Meningkatkan brand awareness sebagai ekosistem solusi digital
- Memudahkan pengunjung menemukan produk yang relevan
- Memberi kesan profesional, modern, dan terpercaya
- Mengurangi bounce rate dengan navigasi yang jelas menuju setiap produk

## 3. Produk

| Produk | Tagline | URL |
|--------|---------|-----|
| **Centric Meet** | Notulensi Rapat AI Cerdas | https://meet.customercentric.id |
| **Centric Hub** | WhatsApp Gateway & CRM All-in-One Indonesia | https://hub.customercentric.id |
| **Centric Buzz** | Social Media Marketing Platform #1 | https://buzz.customercentric.id |
| **Centric Link** | LinkBackSeed Aggregator untuk Press & Media | https://link.customercentric.id |

## 4. Target Audiens

1. **Pemilik bisnis UKM** — butuh growth tools digital
2. **Digital Marketers & Social Media Manager** — butuh SMM panel & WhatsApp marketing
3. **Agency** — butuh solusi WhatsApp API untuk klien
4. **Tim/Perusahaan** — butuh meeting intelligence & notulensi otomatis
5. **Publisher/Media** — butuh link-building backbone

## 5. Struktur Halaman

```
Navbar (sticky)
├── Logo Centric (kiri)
├── Navigasi: Meet · Hub · Buzz · Link (tengah)
└── CTA "Mulai Sekarang" (kanan)

Hero Section
├── Headline: "Ekosistem Digital untuk
│    Percepatan Bisnis Anda"
├── Subheadline: penjelasan singkat ekosistem Centric
└── CTA Buttons: link ke masing-masing produk

Produk Showcase (4 Cards)
├── Centric Meet — icon + deskripsi + "Pelajari"
├── Centric Hub — icon + deskripsi + "Pelajari"
├── Centric Buzz — icon + deskripsi + "Pelajari"
└── Centric Link — icon + deskripsi + "Pelajari"

Stats Section
├── Total klien aktif
├── Total interaksi dihasilkan
├── Produk aktif
└── Pertumbuhan (persen)

Keunggulan Ekosistem
├── All-in-One Ecosystem
├── Fully Integrated
├── 24/7 Support
└── Security First

How It Works (3 Langkah)
├── Pilih Produk yang Sesuai
├── Mulai Gratis / Daftar
└── Rasakan Growth

Testimonials (via CMS)
├── Quotes dari klien / pengguna
└── Nama + perusahaan

FAQ (via CMS)
├── Accordion pertanyaan umum
└── Expand/collapse

CTA Final
├── Headline ajakan
├── Subheadline
└── Button ke semua produk

Footer
├── Logo Centric + deskripsi
├── Links: Meet · Hub · Buzz · Link
├── Social Media
├── Contact
└── Copyright
```

## 6. Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| **Markup** | HTML5 semantic |
| **CSS** | Tailwind CSS v3 (via CDN) |
| **JavaScript** | Vanilla JS (ES6+) |
| **Animasi** | CSS transitions + Intersection Observer |
| **CMS** | Flat-file JSON (`data/content.json`) |
| **Admin Panel** | PHP (native, minimal) |
| **Deployment** | Static files via nginx |

## 7. Brand Assets

| Asset | Path | Penggunaan |
|-------|------|------------|
| Logo CC Biru | `logo/logo_CC_biru.png` | Navbar & Footer (light bg) |
| Logo CC Putih | `logo/logo_CC_putih.png` | Hero & dark sections |
| Favicon | `favicon/favicon.ico` | Browser tab |
| Android Chrome | `favicon/android-chrome-*.png` | Mobile devices |
| Apple Touch | `favicon/apple-touch-icon.png` | iOS devices |

## 8. CMS Scope (Content yang Bisa Diedit via Admin)

| Section | Field |
|---------|-------|
| Hero | headline, subheadline, CTA text & links |
| Produk | nama, tagline, deskripsi, URL, icon class |
| Stats | angka, label |
| Keunggulan | icon, judul, deskripsi |
| Testimonials | quote, nama, perusahaan, avatar |
| FAQ | pertanyaan, jawaban |
| CTA | headline, subheadline, button text |

## 9. Design Direction

- **Warna**: Mixed dark-light
  - Background hero: navy/dark (`#0f172a` atau `#1e1b4b`)
  - Background content: putih/light (`#ffffff`, `#f8fafc`)
  - Accent colors per produk:
    - Meet: Biru (`#3b82f6`)
    - Hub: Ungu (`#8b5cf6`)
    - Buzz: Oranye (`#f97316`)
    - Link: Hijau (`#10b981`)
  - CTA: Gradasi atau solid accent
- **Tipografi**: Inter atau system-ui, bersih dan modern
- **Layout**: Responsive (mobile-first), max-width container
- **Card Produk**: Hover effect, glassmorphism ringan

## 10. Deployment Plan

1. Build semua file landing page di `landing-page/`
2. Copy logo & favicon dari root ke `landing-page/logo/` & `landing-page/favicon/`
3. Transfer seluruh folder ke VPS: `rsync landing-page/ root@103.245.38.81:/var/www/vhosts/customercentric.id/httpdocs/`
4. **Update nginx config** `customercentric.id.conf`:
   - Ubah dari `proxy_pass http://127.0.0.1:3005` menjadi `root $DOCROOT/httpdocs`
   - Pastikan `buzz.customercentric.id.conf` tetap proxy ke port 3005
5. Restart nginx: `plesk sbin nginxmng --reload`
6. Verifikasi landing page live di `https://customercentric.id/`

## 11. Future Considerations

- Integrasi analytics (Google Analytics / Plausible)
- Multi-language support (EN/ID)
- Dynamic product status (live/beta/coming soon)
- Animation library upgrade (GSAP / Motion One)
- Dark mode toggle

## 12. Success Metrics

- Page load time < 2 detik
- Bounce rate < 40%
- Click-through rate ke masing-masing produk > 15%
- Organic traffic increase 30% dalam 3 bulan
