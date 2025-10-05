<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Trigger untuk tabel user
        DB::unprepared('
            CREATE TRIGGER user_log_activity_insert
            AFTER INSERT ON user
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("User baru ditambahkan: ", NEW.username),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER user_log_activity_update
            AFTER UPDATE ON user
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("User diupdate: ", NEW.username),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER user_log_activity_delete
            AFTER DELETE ON user
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("User dihapus: ", OLD.username),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel pegawai
        DB::unprepared('
            CREATE TRIGGER pegawai_log_activity_insert
            AFTER INSERT ON pegawai
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Pegawai baru ditambahkan: ", NEW.nama),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER pegawai_log_activity_update
            AFTER UPDATE ON pegawai
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Data pegawai diupdate: ", NEW.nama),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER pegawai_log_activity_delete
            AFTER DELETE ON pegawai
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Pegawai dihapus: ", OLD.nama),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel departemen
        DB::unprepared('
            CREATE TRIGGER departemen_log_activity_insert
            AFTER INSERT ON departemen
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Departemen baru ditambahkan: ", NEW.nama_departemen),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER departemen_log_activity_update
            AFTER UPDATE ON departemen
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Departemen diupdate: ", NEW.nama_departemen),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER departemen_log_activity_delete
            AFTER DELETE ON departemen
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Departemen dihapus: ", OLD.nama_departemen),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel jabatan
        DB::unprepared('
            CREATE TRIGGER jabatan_log_activity_insert
            AFTER INSERT ON jabatan
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jabatan baru ditambahkan: ", NEW.nama_jabatan),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER jabatan_log_activity_update
            AFTER UPDATE ON jabatan
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jabatan diupdate: ", NEW.nama_jabatan),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER jabatan_log_activity_delete
            AFTER DELETE ON jabatan
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jabatan dihapus: ", OLD.nama_jabatan),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel jenis_cuti
        DB::unprepared('
            CREATE TRIGGER jenis_cuti_log_activity_insert
            AFTER INSERT ON jenis_cuti
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jenis cuti baru ditambahkan: ", NEW.nama_jenis_cuti),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER jenis_cuti_log_activity_update
            AFTER UPDATE ON jenis_cuti
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jenis cuti diupdate: ", NEW.nama_jenis_cuti),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER jenis_cuti_log_activity_delete
            AFTER DELETE ON jenis_cuti
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jenis cuti dihapus: ", OLD.nama_jenis_cuti),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel cuti
        DB::unprepared('
            CREATE TRIGGER cuti_log_activity_insert
            AFTER INSERT ON cuti
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Pengajuan cuti baru oleh pegawai ID: ", NEW.id_pegawai),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER cuti_log_activity_update
            AFTER UPDATE ON cuti
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Status cuti diupdate: ", NEW.status_cuti, " untuk pegawai ID: ", NEW.id_pegawai),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER cuti_log_activity_delete
            AFTER DELETE ON cuti
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Pengajuan cuti dihapus untuk pegawai ID: ", OLD.id_pegawai),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel kehadiran
        DB::unprepared('
            CREATE TRIGGER kehadiran_log_activity_insert
            AFTER INSERT ON kehadiran
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Kehadiran baru: ", NEW.status_kehadiran, " untuk pegawai ID: ", NEW.id_pegawai),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER kehadiran_log_activity_update
            AFTER UPDATE ON kehadiran
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Kehadiran diupdate: ", NEW.status_kehadiran, " untuk pegawai ID: ", NEW.id_pegawai),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER kehadiran_log_activity_delete
            AFTER DELETE ON kehadiran
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Data kehadiran dihapus untuk pegawai ID: ", OLD.id_pegawai),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel kuisioner
        DB::unprepared('
            CREATE TRIGGER kuisioner_log_activity_insert
            AFTER INSERT ON kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Kuisioner baru ditambahkan dengan kategori: ", NEW.kategori),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER kuisioner_log_activity_update
            AFTER UPDATE ON kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Kuisioner diupdate dengan kategori: ", NEW.kategori),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER kuisioner_log_activity_delete
            AFTER DELETE ON kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Kuisioner dihapus dengan kategori: ", OLD.kategori),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel jawaban_kuisioner
        DB::unprepared('
            CREATE TRIGGER jawaban_kuisioner_log_activity_insert
            AFTER INSERT ON jawaban_kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jawaban kuisioner baru untuk kuisioner ID: ", NEW.kuisioner_id),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER jawaban_kuisioner_log_activity_update
            AFTER UPDATE ON jawaban_kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jawaban kuisioner diupdate untuk kuisioner ID: ", NEW.kuisioner_id),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER jawaban_kuisioner_log_activity_delete
            AFTER DELETE ON jawaban_kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Jawaban kuisioner dihapus untuk kuisioner ID: ", OLD.kuisioner_id),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel penilaian
        DB::unprepared('
            CREATE TRIGGER penilaian_log_activity_insert
            AFTER INSERT ON penilaian
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Penilaian baru dengan status: ", NEW.status),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER penilaian_log_activity_update
            AFTER UPDATE ON penilaian
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Penilaian diupdate dengan status: ", NEW.status),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER penilaian_log_activity_delete
            AFTER DELETE ON penilaian
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Penilaian dihapus dengan status: ", OLD.status),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel periode_kuisioner
        DB::unprepared('
            CREATE TRIGGER periode_kuisioner_log_activity_insert
            AFTER INSERT ON periode_kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Periode kuisioner baru ditambahkan untuk kuisioner ID: ", NEW.kuisioner_id),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER periode_kuisioner_log_activity_update
            AFTER UPDATE ON periode_kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Periode kuisioner diupdate untuk kuisioner ID: ", NEW.kuisioner_id),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER periode_kuisioner_log_activity_delete
            AFTER DELETE ON periode_kuisioner
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Periode kuisioner dihapus untuk kuisioner ID: ", OLD.kuisioner_id),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel periode_penilaian
        DB::unprepared('
            CREATE TRIGGER periode_penilaian_log_activity_insert
            AFTER INSERT ON periode_penilaian
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Periode penilaian baru: ", NEW.nama_periode, " - Semester: ", NEW.semester),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER periode_penilaian_log_activity_update
            AFTER UPDATE ON periode_penilaian
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Periode penilaian diupdate: ", NEW.nama_periode, " - Status: ", NEW.status),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER periode_penilaian_log_activity_delete
            AFTER DELETE ON periode_penilaian
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Periode penilaian dihapus: ", OLD.nama_periode),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        // Trigger untuk tabel lokasi_kantor
        DB::unprepared('
            CREATE TRIGGER lokasi_kantor_log_activity_insert
            AFTER INSERT ON lokasi_kantor
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Lokasi kantor baru ditambahkan: ", NEW.nama_lokasi),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER lokasi_kantor_log_activity_update
            AFTER UPDATE ON lokasi_kantor
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Lokasi kantor diupdate: ", NEW.nama_lokasi),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER lokasi_kantor_log_activity_delete
            AFTER DELETE ON lokasi_kantor
            FOR EACH ROW
            BEGIN
                IF @current_user_id IS NOT NULL THEN
                    INSERT INTO log_activity (
                        id_user,
                        keterangan,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        @current_user_id,
                        CONCAT("Lokasi kantor dihapus: ", OLD.nama_lokasi),
                        NOW(),
                        NOW()
                    );
                END IF;
            END
        ');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all triggers
        DB::unprepared('DROP TRIGGER IF EXISTS user_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS user_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS user_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS pegawai_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS pegawai_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS pegawai_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS departemen_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS departemen_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS departemen_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS jabatan_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS jabatan_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS jabatan_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS jenis_cuti_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS jenis_cuti_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS jenis_cuti_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS cuti_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS cuti_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS cuti_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS kehadiran_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS kehadiran_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS kehadiran_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS kuisioner_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS kuisioner_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS kuisioner_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS jawaban_kuisioner_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS jawaban_kuisioner_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS jawaban_kuisioner_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS penilaian_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS penilaian_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS penilaian_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS periode_kuisioner_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS periode_kuisioner_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS periode_kuisioner_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS periode_penilaian_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS periode_penilaian_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS periode_penilaian_log_activity_delete');
        
        DB::unprepared('DROP TRIGGER IF EXISTS lokasi_kantor_log_activity_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS lokasi_kantor_log_activity_update');
        DB::unprepared('DROP TRIGGER IF EXISTS lokasi_kantor_log_activity_delete');
    }
};