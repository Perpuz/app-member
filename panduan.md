# Panduan Instalasi Project Perpustakaan Digital (User Portal)

Berikut adalah langkah-langkah untuk menjalankan project.

## Prasyarat
Pastikan sudah terinstall:
- **PHP** (Minimal versi 8.1)
- **Composer**
- **MySQL** / MariaDB
- **Git**

## Langkah-langkah Instalasi

### 1. Clone Repository (Jika belum)
Jalankan perintah ini di terminal / command prompt:
```bash
git clone <url-repository-anda>
cd laravel-perpuz-user
```

### 2. Install Library PHP (Composer)
Download semua dependensi yang dibutuhkan laravel:
```bash
composer install
```

### 3. Konfigurasi Environment (.env)
Copy file konfigurasi contoh:
```bash
cp .env.example .env
```
*(Di Windows, Anda bisa copy-paste file `.env.example` manual dan rename menjadi `.env`)*

Buka file `.env` dengan text editor, lalu sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=perpustakaan_mahasiswa
DB_USERNAME=root
DB_PASSWORD=
```
*Pastikan Anda sudah membuat database kosong bernama `perpustakaan_mahasiswa` di MySQL/phpMyAdmin.*

### 4. Generate Key Aplikasi
Jalankan perintah berikut untuk membuat key enkripsi Laravel dan JWT:
```bash
php artisan key:generate
php artisan jwt:secret
```

### 5. Konfigurasi Integrasi (Opsional)
Tambahkan konfigurasi berikut di file `.env` (bagian bawah) untuk keperluan integrasi dengan Admin Portal:
```env
# URL API Admin Portal
EXTERNAL_API_URL=http://admin-perpuz.test/api

# Secret Key untuk Admin mengakses data user kita
INTEGRATION_SECRET=rahasia-kita-bersama
```

### 6. Migrasi Database & Data Dummy
Jalankan perintah ini untuk membuat tabel dan mengisi data awal (buku & kategori):
```bash
php artisan migrate --seed
```

### 7. Jalankan Server
Jalankan server lokal Laravel:
```bash
php artisan serve --port=8001
```
Akses di browser: **http://localhost:8001**

---

## Fitur Integrasi

### Sync Data Buku
Untuk mengambil data buku terbaru dari Admin Portal:
```bash
php artisan books:sync
```
*(Pastikan `EXTERNAL_API_URL` sudah benar dan server ci sedang jalan)*

### Endpoint Data User
Admin Portal bisa mengambil data user dari kita melalui endpoint:
- **URL**: `GET /api/integration/users`
- **Header**: `X-INTEGRATION-SECRET: rahasia-kita-bersama` (sesuai `.env`)
