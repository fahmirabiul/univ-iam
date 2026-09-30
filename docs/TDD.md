# TECHNICAL DESIGN DOCUMENT

**1. Deskripsi & Peran Arsitektur**
Bertindak sebagai *Single Source of Truth* untuk otentikasi (OAuth2 Server) dan demografi pengguna kampus. Sistem ini adalah *Publisher* dalam komunikasi data asinkron.

**2. Tech Stack & Infrastructure**

- **Environment:** Docker (Laravel Sail).
- **Core:** Laravel 13, PHP 8.3.
- **Authentication:** Laravel Passport (OAuth2 *Authorization Code Grant*).
- **Message Broker (Publisher):** Redis (Pub/Sub).
- **Database:** MySQL (Fokus pada tabel kredensial dan master data).

**3. Skema Database Utama (ERD Terpusat)**

- `users`: Menyimpan kredensial otentikasi. Menggunakan **UUID** sebagai Primary Key. (Kolom: `id`, `email`, `password`, `is_active`).
- `roles`: Master data peran (Kolom: `id`, `name` -> Dosen, Mahasiswa, Karyawan).
- `user_roles` (Pivot): Menghubungkan *user* dengan *role* globalnya.
- `user_profiles`: Data demografi (Kolom: `user_id`, `nama_lengkap`, `nomor_induk`, `status_akademik`, `fakultas`).

**4. Design Patterns & Logika Inti**

- **Observer Pattern:** Memantau model `UserProfile`. Jika admin mengubah status dosen (misal dari "Aktif" menjadi "Studi Lanjut"), Observer akan memicu *event* dan mengirim *payload* JSON berisi data terbaru ke *channel* Redis (`university.user.updated`).
- **Repository Pattern:** Digunakan untuk memisahkan logika *query* master data agar *controller* untuk Portal API tetap bersih.

---

# **API Contract & Event Payload**

Definisikan format URL, *method*, dan respons JSON untuk *endpoint* SSO. Selain itu, catat format JSON (*payload*) yang akan dikirimkan IAM ke Redis saat ada perubahan profil. Ini memastikan Sistem 2 tahu persis bentuk data yang akan diterima.

1. GET http://sso-kampus.test/api/user

```json
{
  "sso_id": "123e4567-e89b-12d3-a456-426614174000",
  "email": "dosen@kampus.ac.id",
  "role_global": "dosen",
  "profil": {
    "nama_lengkap": "Fahmi R.",
    "nomor_induk": "198001012005011001",
    "fakultas": "FTI",
    "program_studi": "Informatika",
    "status_akademik": "aktif",
  }
}
```

1. Event Payload: Sinkronisasi Redis (Pub/Sub)

Ini adalah pesan (paket) yang diteriakkan oleh Sistem 1 ke *message broker* (Redis) ketika ada perubahan data, agar sistem lain tahu tanpa harus selalu bertanya (*polling*).

**Nama Channel (Topik di Redis):** `university.user.profile_updated`

**Bentuk Pesan (Event Payload):**
Saat Admin SDM mengubah status seorang dosen dari "Aktif" menjadi "Studi Lanjut" lalu menekan tombol *Save* di IAM, Sistem 1 akan mengirim *string* JSON ini ke saluran Redis:

```json
{
  "event": "UserProfileUpdated",
  "timestamp": "2026-09-29T14:10:36Z",
  "data": {
    "sso_id": "123e4567-e89b-12d3-a456-426614174000",
    "nama_lengkap": "Fahmi R.",
    "status_akademik": "studi_lanjut",
    "role_global": "dosen"
  }
}
```

---

# RANCANGAN DATABASE

## **1. Tabel Kredensial Inti**

**Tabel: `users`**

Fokus murni untuk otentikasi (login) dan status akun. Tidak ada data demografi di sini.

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | UUID (Primary Key) | Menggunakan UUID v4 agar aman untuk arsitektur terdistribusi. |
| `email` | String (Unique) | Digunakan sebagai identitas utama saat login. |
| `password` | String | Hashed password. |
| `is_active` | Boolean | Default true. Jika false, user tidak bisa otentikasi (pengganti blokir/banned). |
| `email_verified_at` | Timestamp | Nullable. Standar Laravel. |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |
| `deleted_at` | Timestamp | Soft Delete, standar enterprise untuk data pengguna. |

## 2. Tabel Hak Akses (Role-Based Access Control)

Tabel: `roles`

Master data untuk peran yang diakui di seluruh ekosistem kampus.

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `name` | String (Unique) | Contoh: dosen, karyawan, mahasiswa. Huruf kecil/slug. |
| `description` | String | Nullable. Contoh: "Dosen Pengajar Aktif". |
| `created\_at` | Timestamp |  |
| `updated\_at` | Timestamp |  |

**Tabel: `user_roles` (Pivot)**

Menghubungkan *user* dengan *role* mereka. *Many-to-Many* agar satu UUID *user* bisa menjadi Dosen sekaligus Admin LPPM jika diperlukan.

| **Nama Kolom** | **Tipe Data** | **Keterangan** |
| --- | --- | --- |
| `user_id` | UUID (FK) | Relasi ke tabel `users.id` (Cascade on Delete). |
| `role_id` | Unsigned BigInt (FK) | Relasi ke tabel `roles.id` (Cascade on Delete). |

(Catatan: Tabel pivot ini sebaiknya memiliki Composite Primary Key atas `user_id` dan `role_id` untuk mencegah duplikasi data).

## **3. Tabel Data Master Demografi**

**Tabel: `user_profiles`**
Ini adalah tabel yang akan diawasi oleh *Observer*. Jika ada perubahan di tabel ini, sistem akan mempublikasikannya ke Redis.

| **Nama Kolom** | **Tipe Data** | **Keterangan** |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `user_id` | UUID (FK, Unique) | Relasi *One-to-One* ke tabel `users`. |
| `nama_lengkap` | String | Wajib diisi. |
| `nomor_induk` | String (Unique) | Nullable. (NIP/NIDN/NIM). |
| `unit_kerja` | String | Nullable. Diisi untuk Karyawan (Biro/Lembaga). |
| `fakultas` | String | Nullable. Diisi untuk Dosen/Mahasiswa. |
| `program_studi` | String | Nullable. Diisi untuk Dosen/Mahasiswa. |
| `status_akademik` | Enum / String | Nullable. (Aktif, Cuti, Studi Lanjut). |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |

## **4. Tabel OAuth2 (Laravel Passport)**

Ini adalah pembuktian bahwa Anda bekerja secara efisien. Anda **tidak perlu dan tidak boleh** merancang tabel ini secara manual. Saat Anda menjalankan `php artisan passport:install`, Laravel otomatis membuat tabel standar industri keamanan:

- `oauth_clients`
- `oauth_access_tokens`
- `oauth_auth_codes`
- `oauth_personal_access_clients`
- `oauth_refresh_tokens`

**Catatan Arsitektur untuk Anda Evaluasi:**
Desain ini memisahkan secara tegas antara data untuk *Login* (`users`), otorisasi (`roles`), dan profil yang disinkronisasi (`user_profiles`). Jika Sistem 2 (Knowledge Hub) membutuhkan data nama atau status dosen, Sistem 2 tidak perlu mengambil data kredensialnya.