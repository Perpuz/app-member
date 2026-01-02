# Dokumentasi Instalasi & Pengembangan Project Perpustakaan

Repository ini berisi sistem perpustakaan digital dengan arsitektur **Microservices (Separated Frontend & Backend)** untuk bagian Admin.

1.  **`app-librarian`**: Sistem Admin/Pustakawan.
    *   **Backend**: CodeIgniter 4 (Headless API Only).
    *   **Frontend**: Static HTML + JS (Client).
2.  **`app-member`**: Aplikasi Portal Mahasiswa (Laravel 10 + GraphQL Hybrid).

---

## Prasyarat
-   **PHP** (Minimal versi 8.1)
-   **Composer**
-   **MySQL** / MariaDB

---

## 1. Setup Database
Buat **dua** database kosong di MySQL:
1.  `perpustakaan_admin` (Untuk aplikasi Librarian)
2.  `perpustakaan_mahasiswa` (Untuk aplikasi Member)

---

## 2. Instalasi & Run Aplikasi Librarian (Admin)
Aplikasi ini terdiri dari dua bagian yang harus dijalankan terpisah.

### A. Jalankan Backend API (Port: 8081)
1.  Masuk ke folder backend: `cd app-librarian/backend`
2.  Install dependencies: `composer install`
3.  Setup `.env` (copy dari `env`):
    ```env
    database.default.hostname = localhost
    database.default.database = perpustakaan_admin
    database.default.username = root
    database.default.password = 
    database.default.DBDriver = MySQLi
    ```
4.  Migrasi Database: `php spark migrate`
5.  **Jalankan Server:**
    ```bash
    php spark serve --port=8081
    ```
    > **Note:** Backend ini tidak menampilkan halaman web (HTML). Jika diakses lewat browser, hanya akan muncul response JSON. Gunakan Frontend di bawah untuk tampilan antarmuka.

### B. Jalankan Frontend UI (Port: 5500)
Agar folder `frontend` berguna sebagai tampilan Admin:

1.  Buka terminal baru.
2.  Masuk ke folder frontend: `cd app-librarian/frontend`
3.  Jalankan server statis menggunakan PHP:
    ```bash
    php -S localhost:5500
    ```
4.  **Akses via Browser:** Buka **http://localhost:5500**
    *   Frontend ini otomatis terhubung ke Backend di port 8081.
    *   Gunakan tampilan ini untuk Login Admin dan kelola buku.

---

## 3. Instalasi & Run Aplikasi Member (Port: 8000)
Aplikasi Portal Mahasiswa (Laravel).

1.  Masuk ke folder: `cd app-member`
2.  Install dependencies: `composer install`
3.  Setup `.env`:
    ```env
    DB_DATABASE=perpustakaan_mahasiswa
    EXTERNAL_API_URL=http://localhost:8081/api
    ```
4.  Generate Key & Migrasi:
    ```bash
    php artisan key:generate
    php artisan jwt:secret
    php artisan migrate --seed
    ```
5.  **Jalankan Server:**
    ```bash
    php artisan serve
    ```
    > Akses: **http://localhost:8000**

---

## 4. Ringkasan Arsitektur
Untuk memenuhi kebutuhan "Backend & Frontend", berikut port yang harus jalan:

| Komponen | Folder | Command | URL Akses |
| :--- | :--- | :--- | :--- |
| **Admin Backend** | `app-librarian/backend` | `php spark serve --port=8081` | (API Only) |
| **Admin Frontend** | `app-librarian/frontend` | `php -S localhost:5500` | **http://localhost:5500** |
| **Member App** | `app-member` | `php artisan serve` | **http://localhost:8000** |

Semua sistem saling terhubung. Admin Frontend memanggil Admin Backend. Member App memanggil Admin Backend untuk sinkronisasi buku.
