# Panduan Migrasi Database

## Urutan Migrasi

Migrasi database telah diperbaiki dan disesuaikan dengan struktur database pada file `db/dummyhr.sql`. Urutan migrasi yang benar adalah:

### 1. Tabel Dasar (2024_01_01)
- `create_departemen_table` - Tabel departemen
- `create_jabatan_table` - Tabel jabatan
- `create_pegawai_table` - Tabel pegawai (termasuk kolom golongan)
- `create_lokasi_kantor_table` - Tabel lokasi kantor
- `create_user_table` - Tabel user
- `create_kehadiran_table` - Tabel kehadiran

### 2. Tabel Tambahan (2025)
- `add_jam_kerja_to_kehadiran_table` - Menambahkan kolom jam kerja ke tabel kehadiran
- `create_periode_penilaian_table` - Tabel periode penilaian
- `create_penilaian_table` - Tabel penilaian
- `create_kuisioner` - Tabel kuisioner
- `create_periode_kuisioner` - Tabel periode kuisioner
- `create_jawaban_kuisioner` - Tabel jawaban kuisioner
- `jenis_cuti` - Tabel jenis cuti
- `cuti` - Tabel cuti
- `create_sessions_table` - Tabel sessions
- `create_log_activity_table` - Tabel log activity
- `create_all_trigger` - Trigger database
- `create_personal_access_tokens_table` - Tabel personal access tokens

## Cara Menjalankan Migrasi

### Untuk Development (Local)

1. Pastikan database sudah dikonfigurasi di file `.env`
2. Jalankan migrasi:
   ```bash
   php artisan migrate:fresh
   ```

3. (Opsional) Jalankan seeder untuk mengisi data awal:
   ```bash
   php artisan db:seed
   ```

### Untuk Production (Railway)

Railway akan otomatis menjalankan migrasi saat deploy. Pastikan:

1. Environment variables sudah dikonfigurasi dengan benar
2. `DATABASE_URL` sudah diset (Railway biasanya set otomatis)
3. Periksa log deploy untuk memastikan migrasi berhasil

## Perubahan yang Dilakukan

### Perbaikan Masalah "no such table: pegawai"

Masalah ini terjadi karena:
- Tidak ada migrasi untuk membuat tabel dasar seperti `pegawai`, `departemen`, `jabatan`, dll
- Migrasi `add_golongan_to_pegawai_table` mencoba menambahkan kolom ke tabel yang belum ada

Solusi:
- Membuat migrasi lengkap untuk semua tabel dasar
- Kolom `golongan` langsung ditambahkan saat pembuatan tabel `pegawai`
- Menghapus migrasi `add_golongan_to_pegawai_table` yang tidak diperlukan lagi

### Struktur Tabel yang Diperbaiki

1. **Tabel pegawai**: Menambahkan kolom `golongan` dengan nilai default 'D'
2. **Tabel kehadiran**: Memperbaiki enum `status_jam_kerja` untuk menambahkan 'Setengah Hari'
3. **Tabel cuti**: Menambahkan kolom `disetujui_oleh` yang hilang
4. **Tabel penilaian**: 
   - Memperbaiki foreign key dari `periode_kuisioner` ke `periode_penilaian`
   - Menambahkan kolom `total_nilai`

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error: 1 no such table"

Jika Anda masih mendapat error ini:
1. Hapus semua tabel di database (atau gunakan `migrate:fresh`)
2. Pastikan semua migrasi berjalan dalam urutan yang benar
3. Periksa log untuk melihat migrasi mana yang gagal

### Error: "Foreign key constraint"

Pastikan urutan migrasi benar. Tabel yang direferensikan harus dibuat terlebih dahulu.

### Error pada Railway

Periksa environment variables:
- `DB_CONNECTION` harus sesuai (mysql atau pgsql)
- `DATABASE_URL` harus valid
- Pastikan Railway database sudah aktif

