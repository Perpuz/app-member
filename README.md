# Dokumentasi Instalasi & Pengembangan Project Perpustakaan

Repository ini berisi sistem perpustakaan digital yang terdiri dari dua aplikasi:

1.  **`app-librarian`**: Backend API Pustakawan (CodeIgniter 4).
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

## 2. Instalasi & Run Aplikasi Librarian (Port: 8081)
Aplikasi ini berjalan sebagai Backend API.

1.  **Masuk ke direktori:**
    ```bash
    cd app-librarian/backend
    ```
2.  **Install Dependencies:**
    ```bash
    composer install
    ```
3.  **Konfigurasi Environment:**
    -   Copy file `env` menjadi `.env`
    -   Edit `.env` (sesuaikan user/password database Anda):
    ```env
    database.default.hostname = localhost
    database.default.database = perpustakaan_admin
    database.default.username = root
    database.default.password = 
    database.default.DBDriver = MySQLi
    ```
4.  **Migrasi Database:**
    ```bash
    php spark migrate
    ```
5.  **Jalankan Server (Port 8081):**
    ```bash
    php spark serve --port=8081
    ```
    > **Penting:** API Admin harus jalan di port **8081** agar bisa diakses oleh aplikasi member.

    *(Catatan: Folder `frontend` di dalam `app-librarian` saat ini belum digunakan secara aktif dalam alur ini)*

---

## 3. Instalasi & Run Aplikasi Member (Port: 8000)
Aplikasi Portal Mahasiswa.

1.  **Masuk ke direktori:**
    ```bash
    cd app-member
    ```
2.  **Install Dependencies:**
    ```bash
    composer install
    ```
3.  **Konfigurasi Environment:**
    -   Copy `.env.example` ke `.env`
    -   Edit `.env`:
    ```env
    DB_DATABASE=perpustakaan_mahasiswa
    DB_USERNAME=root
    DB_PASSWORD=

    # URL API Admin (Arahkan ke Port 8081)
    EXTERNAL_API_URL=http://localhost:8081/api
    ```
4.  **Generate Key:**
    ```bash
    php artisan key:generate
    php artisan jwt:secret
    ```
5.  **Migrasi Database:**
    ```bash
    php artisan migrate --seed
    ```
6.  **Jalankan Server:**
    ```bash
    php artisan serve
    ```
    > Default berjalan di **http://localhost:8000**

---

## 4. Fitur & Penggunaan

### Sinkronisasi Data
Setelah kedua server berjalan (Admin :8081, Member :8000), jalankan perintah ini di terminal `app-member` untuk mengambil data buku dari Admin:
```bash
php artisan books:sync
```

### Akses GraphQL (Member App)
Fitur Dashboard, Profil, dan Peminjaman menggunakan GraphQL.
-   **Dashboard**: Login sebagai member, lihat di `http://localhost:8000/dashboard`
-   **Playground**: Coba query manual di `http://localhost:8000/graphiql`
