# DEPLOYMENT — SI-APOTEK ke InfinityFree

Target: `http://si-apotek.gt.tc/` (hosting InfinityFree, PHP + MySQL).

## 1. Siapkan Database di Panel

1. Masuk ke **Control Panel InfinityFree** → **MySQL Databases**.
2. Buat database baru (mis. `if0_43397489_si_apotek`) → catat:
   - **MySQL Hostname** (contoh `sqlXXX.infinityfree.com` — **BUKAN** localhost)
   - **Username** & **Password** database
   - **Nama database**
3. Buka **phpMyAdmin** (dari panel) → pilih database tersebut → **Import**
   `database/schema.sql` lalu `database/seed.sql`.

## 2. Konfigurasi Database Produksi

Edit `app/config/database.php` sesuai data panel:

```php
return [
    'host'    => 'sqlXXX.infinityfree.com',   // hostname dari panel
    'name'    => 'if0_43397489_si_apotek',    // nama database
    'user'    => 'if0_43397489_xxxx',         // username DB
    'pass'    => 'password-dari-panel',       // password DB
    'charset' => 'utf8mb4',
];
```

> ⚠️ **Jangan commit** file ini dengan kredensial produksi ke GitHub.
> Gunakan file `.gitignore` (sudah disiapkan) atau edit langsung di server
> setelah upload.

## 3. Upload File

1. Di panel → **File Manager** (atau FTP), masuk ke folder **`htdocs`**.
2. Upload **seluruh isi folder `app/`** (bukan folder `app`-nya, melainkan
   isinya: `index.php`, `config/`, `core/`, `controllers/`, `views/`, `assets/`).
3. Pastikan `index.php` berada langsung di `htdocs/`.

> Struktur di server harus:
> ```
> htdocs/
> ├── index.php
> ├── config/
> ├── core/
> ├── controllers/
> ├── views/
> └── assets/
> ```

## 4. Nonaktifkan Tampilan Error (produksi)

Edit `app/config/config.php`:

```php
ini_set('display_errors', '0');
```

## 5. Aktifkan HTTPS (SSL)

Di panel InfinityFree → **SSL Certificates** → pilih domain `si-apotek.gt.tc` →
**Request Free SSL**. Setelah aktif, buka `https://si-apotek.gt.tc/`.

Jika SSL aktif, aktifkan juga cookie `secure` di `config/config.php`:
```php
'secure' => true,
```

## 6. Uji

1. Buka `https://si-apotek.gt.tc/` → halaman login muncul.
2. Login dengan akun default, lalu **segera ganti password**.
3. Coba satu transaksi penjualan dan cek stok berkurang.

## Catatan

- Aplikasi **tidak butuh Composer** atau perintah terminal di server.
- Dependency (Bootstrap, Chart.js) dimuat via CDN — tidak perlu di-upload.
- File `.sql`, `.md`, log, dan folder `config`/`core`/`database` **diblokir**
  dari akses browser oleh `.htaccess`.
