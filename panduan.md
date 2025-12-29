# Panduan Instalasi Project Perpuz
Berikut adalah langkah-langkah untuk menjalankan project.

## Prasyarat
Pastikan sudah terinstall:
- **PHP** (Minimal versi 8.1)
- **Composer**
- **MySQL** / MariaDB
- **Git**

## Langkah-langkah Instalasi

### Clone Repository app-librarian dan app-member(Jika belum)
Jalankan perintah ini di terminal / command prompt:
```bash
git clone <url-app-librarian>
git clone <url-app-member>

```

### Aktivasi CI APP-lIBRARIAN

### 1.  **Install Dependensi**:
masuk pada folder backend dengan 
```
cd app-librarian/backend
composer install
```

### 2.  **Install Dependensi**:
Lalu salin file `env` menjadi `.env`, buka coment dan atur koneksi database Anda:
```ini
database.default.hostname = localhost
database.default.database = perpuz_db
database.default.username = root
database.default.password = ''
```
tambahkan jwt dan integration secret di env app-librarian
```
JWT_SECRET=perpuz_librarian_secret_key_12345
INTEGRATION_SECRET=rahasia-kita-bersama
```
Tambahkan URL App Member:
```
MEMBER_API_URL=http://localhost:8001
```

### 3.  **Setup Database**:
```bash
php spark migrate
php spark db:seed UserSeeder
```

### 4. Menjalankan Server

Gunakan perintah berikut untuk menjalankan server lokal:

```bash
php spark serve --port 8081
```

Akses aplikasi di browser: **[http://localhost:8081/index.html](http://localhost:8081/index.html)**

---


### Aktivasi Member APP-MEMBER

1.  **Konfigurasi Environment**:
Masuk pada folder APP-MEMBER, Lalu Instal Dependensi :
```bash
cd app-member
composer install
```

### 2. Konfigurasi Environment (.env)
Copy file konfigurasi contoh:
```bash
cp .env.example .env
```

Buka file `.env`, lalu sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=perpustakaan_mahasiswa
DB_USERNAME=root
DB_PASSWORD=
```
*Pastikan Anda sudah membuat database kosong bernama `perpustakaan_mahasiswa` di MySQL/phpMyAdmin.*

### 3. Generate Key Aplikasi
Jalankan perintah berikut untuk membuat key enkripsi Laravel dan JWT:
```bash
php artisan key:generate
php artisan jwt:secret
```

### 4. **Setup Integrasi**:
Ubah atau tambah .env sesuai environment berikut : 
```
EXTERNAL_API_URL=http://localhost:8081/api/integration
EXTERNAL_API_TIMEOUT=30
INTEGRATION_SECRET=rahasia-kita-bersama
```

### 5. Migrasi Database & Data Dummy
Jalankan perintah ini untuk membuat tabel dan mengisi data awal (buku & kategori):
```bash
php artisan migrate --seed
```

### 6. Jalankan Server
Jalankan server lokal Laravel:
```bash
php artisan serve --port=8001
```
Akses di browser: **http://localhost:8001**

---

### 7. Sync Data Buku
buka terminal baru, untuk mengambil data buku terbaru dari Admin Portal:
```bash
php artisan books:sync
```
*(Pastikan spark server CI sedang jalan)*


