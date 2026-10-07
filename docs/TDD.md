# TECHNICAL DESIGN DOCUMENT

**1. Deskripsi & Peran Arsitektur**
Bertindak sebagai *Single Source of Truth* untuk otentikasi (OAuth2 Server) dan master data identitas sivitas kampus. Sistem ini bertindak sebagai *Publisher* dalam sinkronisasi data asinkron berbasis event.

**2. Tech Stack & Infrastructure**

- **Environment:** Docker (Laravel Sail).
- **Core:** Laravel 13, PHP 8.3.
- **Authentication:** Laravel Passport (OAuth2 *Authorization Code Grant*).
- **Message Broker (Publisher):** Redis (Pub/Sub).
- **Database:** MySQL (Tabel kredensial, relasi RBAC, unit kerja, dan master data akademik).

**3. Skema Database Utama (ERD Terpusat)**

- `users`: Kredensial akun otentikasi. Menggunakan **UUID** sebagai Primary Key. (Kolom: `id`, `email`, `password`, `is_active`, `is_admin`).
- `roles`: Master data peran sivitas & sistem (`super_admin`, `dosen`, `mahasiswa`, `karyawan`).
- `user_roles` (Pivot): Menghubungkan *user* dengan *role* utama sivitasnya.
- `faculties`: Master data fakultas (`id`, `code`, `name`).
- `study_programs`: Master data program studi berelasi ke fakultas (`id`, `faculty_id`, `code`, `nim_code`, `name`).
- `work_units`: Master data unit kerja / lembaga / biro kampus (`id`, `code`, `name`).
- `user_profiles`: Data profil demografi (`id`, `user_id`, `nama_lengkap`, `nomor_induk`, `work_unit_id`, `study_program_id`).

**4. Design Patterns & Logika Inti**

- **Observer Pattern:** Memantau model `UserProfile` via `UserProfileObserver`. Setiap perubahan profil memicu *event* dan mempublikasikan payload JSON terbaru ke *channel* Redis (`university.user.profile_updated`).
- **Unit-Based Authorization:** Hak admin unit ditentukan melalui kombinasi `is_admin = true` pada user dan penempatan `work_unit_id`, sedangkan `super_admin` memiliki akses menyeluruh.

---

# **API Contract & Event Payload**

### 1. GET /api/user (SSO Profile & Authorization Endpoint)

Endpoint ini diakses oleh sistem klien (misal Knowledge Hub) menggunakan *Bearer Token* hasil pertukaran *Authorization Code*.

**Contoh Response - Dosen / Mahasiswa (Akademik):**
```json
{
  "sso_id": "123e4567-e89b-12d3-a456-426614174000",
  "email": "dosen@univ.ac.id",
  "role_global": "dosen",
  "is_superadmin": false,
  "is_admin": false,
  "unit": null,
  "fakultas": "Fakultas Teknik",
  "program_studi": "Teknik Informatika",
  "profil": {
    "nama_lengkap": "Dr. Fahmi R., M.Kom.",
    "nomor_induk": "202610110001"
  }
}
```

**Contoh Response - Karyawan Admin Unit (contoh: Admin LPPM):**
```json
{
  "sso_id": "987e6543-e21b-43d2-b654-426614174999",
  "email": "admin.lppm@univ.ac.id",
  "role_global": "karyawan",
  "is_superadmin": false,
  "is_admin": true,
  "unit": {
    "id": 3,
    "kode": "503",
    "nama": "LPPM"
  },
  "fakultas": null,
  "program_studi": null,
  "profil": {
    "nama_lengkap": "Suryo Utomo, S.T.",
    "nomor_induk": "20265030001"
  }
}
```

---

### 2. Event Payload: Sinkronisasi Redis (Pub/Sub)

Pesan yang dipublikasikan oleh IAM ke *message broker* (Redis) saat data profil diperbarui:

**Nama Channel (Topik di Redis):** `university.user.profile_updated`

**Bentuk Pesan (Event Payload):**
```json
{
  "event": "UserProfileUpdated",
  "timestamp": "2026-10-07T14:10:36Z",
  "data": {
    "sso_id": "987e6543-e21b-43d2-b654-426614174999",
    "nama_lengkap": "Suryo Utomo, S.T.",
    "role_global": "karyawan",
    "is_admin": true,
    "unit": {
      "id": 3,
      "kode": "503",
      "nama": "LPPM"
    }
  }
}
```

---

# RANCANGAN DATABASE

## **1. Tabel Kredensial Inti**

**Tabel: `users`**

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | UUID (Primary Key) | Menggunakan UUID v4 untuk integrasi multi-aplikasi. |
| `email` | String (Unique) | Identitas utama login SSO. |
| `password` | String | Hashed password (Bcrypt). |
| `is_active` | Boolean | Default `true`. Jika `false`, akun dinonaktifkan dari akses SSO. |
| `is_admin` | Boolean | Default `false`. Penanda wewenang administrator di unit kerjanya. |
| `email_verified_at` | Timestamp | Nullable. |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |
| `deleted_at` | Timestamp | Soft Delete pengguna. |

---

## **2. Tabel Hak Akses & Peran**

**Tabel: `roles`**

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `name` | String (Unique) | Nama role: `super_admin`, `dosen`, `mahasiswa`, `karyawan`. |
| `description` | String | Nullable deskripsi peran. |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |

**Tabel: `user_roles` (Pivot)**

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `user_id` | UUID (FK) | Relasi ke `users.id` (Cascade on Delete). |
| `role_id` | Unsigned BigInt (FK) | Relasi ke `roles.id` (Cascade on Delete). |

---

## **3. Tabel Master Data Organisasi & Akademik**

**Tabel: `faculties`**

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `code` | String (Unique) | Kode fakultas (misal: `FT`, `FEB`, `FSRD`). |
| `name` | String | Nama resmi fakultas. |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |

**Tabel: `study_programs`**

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `faculty_id` | Unsigned BigInt (FK) | Relasi ke `faculties.id` (Cascade on Delete). |
| `code` | String (Unique) | Kode prodi (misal: `101`, `102`, `201`). |
| `nim_code` | String | Kode prefix NIM mahasiswa (misal: `1101`, `1201`). |
| `name` | String | Nama program studi. |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |

**Tabel: `work_units`**

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `code` | String (Unique) | Kode unit kerja (misal: `501`, `502`, `503`). |
| `name` | String | Nama unit kerja (UPT TIK, Biro SDM, LPPM). |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |

---

## **4. Tabel Profil Demografi**

**Tabel: `user_profiles`**

| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | Unsigned BigInt (PK) | Auto-increment. |
| `user_id` | UUID (FK, Unique) | Relasi *One-to-One* ke tabel `users` (Cascade on Delete). |
| `nama_lengkap` | String | Nama lengkap dan gelar. |
| `nomor_induk` | String (Unique) | Nullable. Nomor induk terstandar (NIM/NIDN/NIP). |
| `work_unit_id` | Unsigned BigInt (FK) | Nullable. Relasi ke `work_units.id` (untuk Karyawan). |
| `study_program_id` | Unsigned BigInt (FK) | Nullable. Relasi ke `study_programs.id` (untuk Dosen & Mhs). |
| `created_at` | Timestamp |  |
| `updated_at` | Timestamp |  |

---

## **5. Tabel OAuth2 (Laravel Passport)**

Dikelola otomatis oleh migrasi Laravel Passport:
- `oauth_clients`
- `oauth_access_tokens`
- `oauth_auth_codes`
- `oauth_personal_access_clients`
- `oauth_refresh_tokens`
