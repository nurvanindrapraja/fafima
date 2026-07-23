<div align="center">
  <img src="public/icon_fafima_small.png" alt="FAFIMA Logo" width="100" />
  <h1>Family Finance Manager (FAFIMA)</h1>
  <p><strong>Aplikasi Pencatatan dan Pengelolaan Keuangan Keluarga Pintar Berbasis AI</strong></p>
</div>

---

## 📖 Tentang FAFIMA
**Family Finance Manager (FAFIMA)** adalah aplikasi pengelolaan keuangan keluarga modern yang dirancang untuk membantu keluarga, pasangan, maupun individu dalam mencatat, memonitor, dan merencanakan keuangan mereka secara kolaboratif. 

Dibangun sebagai **Progressive Web App (PWA)**, FAFIMA dapat diakses dengan cepat melalui browser maupun diinstal langsung di *smartphone* Anda layaknya aplikasi native.

## ✨ Fitur Utama

- 👨‍👩‍👧‍👦 **Manajemen Keluarga (Single Family):** Kolaborasi keuangan dalam satu payung keluarga. Undang anggota keluarga dengan mudah menggunakan *QR Code* atau Kode Unik.
- 📸 **OCR Struk Cerdas:** Malas mengetik? Cukup foto atau unggah struk belanja Anda, dan AI (OpenAI Vision) akan secara otomatis mengenali dan mencatat pengeluaran Anda.
- 🤖 **Smart Spending Advisor:** Asisten keuangan berbasis AI (Claude / OpenAI) yang siap menganalisis pola pengeluaran Anda, mengingatkan jika boros, dan memberikan saran penghematan.
- 🎯 **Perencanaan Target (Goals):** Rencanakan liburan, dana pendidikan, atau beli kendaraan bersama-sama. Dilengkapi sistem *approval* seluruh anggota keluarga.
- 📊 **Dashboard & Analitik:** Pantau arus kas harian, mingguan, dan bulanan melalui visualisasi grafik yang memanjakan mata.
- 🛡️ **Limit Pengeluaran:** Atur batas pengeluaran maksimum untuk keseluruhan keluarga maupun limit jajan spesifik untuk setiap individu/anak.
- 🔔 **Sistem Notifikasi:** Dapatkan pengingat *real-time* ketika pengeluaran sudah mendekati batas aman.

## 🚀 Teknologi yang Digunakan

FAFIMA mengusung arsitektur *TALL Stack* yang modern dan responsif:
- **Framework:** Laravel 12 (PHP 8.4+)
- **Frontend:** Livewire 3, Alpine.js, Tailwind CSS
- **Database:** MySQL 8.x
- **Artificial Intelligence:** OpenAI / Anthropic Claude API
- **PWA:** Terintegrasi penuh dengan Service Workers dan Web Manifest

## 🛠️ Panduan Instalasi (Lokal)

Jika Anda ingin menjalankan aplikasi ini di komputer lokal untuk pengembangan, ikuti langkah berikut:

1. **Kloning Repository**
   ```bash
   git clone https://github.com/nurvanindrapraja/fafima.git
   cd fafima
   ```

2. **Instalasi Dependensi PHP & Node**
   ```bash
   composer install
   npm install
   ```

3. **Pengaturan Lingkungan (.env)**
   Duplikat file `.env.example` menjadi `.env`, lalu konfigurasikan koneksi *database* dan kunci API AI Anda.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Database**
   ```bash
   php artisan migrate
   ```

5. **Jalankan Aplikasi**
   Jalankan server PHP dan kompilasi aset secara bersamaan:
   ```bash
   php artisan serve
   npm run dev
   ```
   Aplikasi kini dapat diakses melalui `http://127.0.0.1:8000`.

## 👥 Hak Akses (Roles)
- **Kepala Keluarga (Owner):** Menginisiasi grup keluarga, menyetujui anggota, mengatur limit pengeluaran, menghapus histori transaksi, dan mengusulkan target keuangan.
- **Anggota (Member):** Melakukan input transaksi (pemasukan/pengeluaran), melihat analitik (opsional), dan menyetujui target keuangan.
- **Superadmin:** Mengelola seluruh data *Family* secara sistem tanpa melanggar privasi rincian transaksi pengguna.

## 📄 Lisensi
FAFIMA adalah perangkat lunak hak milik (*proprietary*) yang dikembangkan untuk manajemen keuangan tertutup.
