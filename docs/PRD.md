# PRODUCT REQUIREMENTS DOCUMENT

**1. Tujuan Produk**

Menyediakan pusat sumber kebenaran (*Single Source of Truth*) untuk otentikasi identitas, otorisasi peran global, dan master data sivitas akademika. Sistem ini juga bertindak sebagai *message publisher* yang memberitahukan sistem lain jika terjadi perubahan demografi pengguna.

**2. User Roles**

- **Super Admin / Karyawan SDM:** Mengelola entitas kampus (Fakultas, Prodi) dan data master akun (Dosen, Mahasiswa).
- **Dosen & Mahasiswa:** Pengguna akhir yang menggunakan sistem murni untuk masuk (*login*) dan melompat ke aplikasi operasional melalui Portal.

**3. User Stories & Acceptance Criteria**

**Epic 1: Autentikasi & Portal SSO**

- **Story 1.1:** Sebagai pengguna, saya ingin *login* menggunakan email dan *password* agar bisa mengakses portal kampus.
    - *Acceptance Criteria:* Sistem memvalidasi kredensial. Jika salah, tampilkan pesan *error*. Jika berhasil, arahkan ke halaman Portal Dashboard.
- **Story 1.2:** Sebagai pengguna, saya ingin melihat *grid* ikon aplikasi di Portal agar saya tidak perlu menghafal URL.
    - *Acceptance Criteria:* Halaman Portal menampilkan daftar aplikasi (contoh: "Knowledge Hub") sesuai *role* pengguna. Klik ikon akan mengeksekusi alur OAuth2 (*Authorization Code Grant*) ke aplikasi klien.

**Epic 2: Manajemen Data Master (Admin Only)**

- **Story 2.1:** Sebagai Admin SDM, saya ingin menambah dan mengubah status Dosen (Aktif/Studi Lanjut/Pensiun) agar data kepegawaian selalu *up-to-date*.
    - *Acceptance Criteria:* Terdapat antarmuka CRUD untuk entitas pengguna. Perubahan status berhasil disimpan di basis data lokal.

**Epic 3: Event Broadcasting (Sistem di Latar Belakang)**

- **Story 3.1:** Sebagai sistem, saya harus mempublikasikan pesan ke Redis setiap kali profil atau status pengguna diubah oleh Admin.
    - *Acceptance Criteria:* Menggunakan *Observer* pada model `UserProfile`. Saat *event* `updated` atau `created` terpicu, *payload* JSON (berisi ID, nama, status terbaru) dikirim ke *channel* Redis `university.user.updated`.

**4. Technical Constraints**

- **Framework:** Laravel 13 (API & Blade minimalis untuk Portal).
- **Auth Driver:** Laravel Passport.
- **Message Broker:** Redis Pub/Sub.