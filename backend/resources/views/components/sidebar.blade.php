<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        {{-- header menu umum --}}
        @canany([
            'manajemen_profil.read',
            'rekap_absensi_pribadi.read',
            'rekap_evaluasi_pribadi.read',
        ])
            <li class="nav-heading">Menu Umum</li>
        @endcanany

        {{-- dashboard hrd --}}
        @hasanyrole(['staff hrd','kepala hrd','direktur pendidikan','kepala yayasan','superadmin'])
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('hrd.dashboard.page') ? '' : ' collapsed' }}" href="{{ route('hrd.dashboard.page') }}">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endhasanyrole

        {{-- dashboard pegawai --}}
        @hasanyrole(['tenaga pendidik','kepala sekolah','kepala departemen','superadmin'])
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pegawai.dashboard.page') ? '' : ' collapsed' }}" href="{{ route('pegawai.dashboard.page') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        @endhasanyrole

        @can('rekap_absensi_pribadi.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rekap.absensi.pribadi.page') ? '' : ' collapsed' }}" href="{{ route('rekap.absensi.pribadi.page') }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Absensi Pribadi</span>
                </a>
            </li>
        @endcan

        @can('rekap_evaluasi_pribadi.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('evaluasi.rekap.pribadi.page') ? '' : ' collapsed' }}" href="{{ route('evaluasi.rekap.pribadi.page') }}">
                    <i class="bi bi-ui-checks"></i>
                    <span>Evaluasi Pribadi</span>
                </a>
            </li>
        @endcan

        {{-- header hrd --}}
        @canany([
            'manajemen_user.read',
            'manajemen_rekap_absensi_today.read',
            'manajemen_evaluasi.read',
            ])
            <li class="nav-heading">HRD</li>
        @endcanany

        @can('manajemen_rekap_absensi_today.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('rekap.absensi.today.page') ? '' : ' collapsed' }}" href="{{ route('rekap.absensi.today.page') }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Absensi Hari ini</span>
                </a>
            </li>
        @endcan

        @can('manajemen_user.read')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kelola.pegawai.page') ? '' : ' collapsed' }}" href="{{ route('kelola.pegawai.page') }}">
                        <i class="bi bi-people"></i>
                        <span>Pegawai</span>
                    </a>
                </li>
        @endcan

        @can('manajemen_evaluasi.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('evaluasi.pegawai.page') ? '' : ' collapsed' }}" href="{{ route('evaluasi.pegawai.page') }}">
                    <i class="bi bi-ui-checks"></i>
                    <span>Evaluasi</span>
                </a>
            </li>
        @endcan

        {{-- header dirpen --}}
        @canany([
            'verifikasi_cuti_dirpen.read',
            ])
            <li class="nav-heading">Direktur Pendidikan</li>
        @endcanany

        @can('verifikasi_cuti_dirpen.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('verifikasi.cuti.dirpen.page') ? '' : ' collapsed' }}" href="{{ route('verifikasi.cuti.dirpen.page') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Verifikasi Cuti</span>
                </a>
            </li>
        @endcan

        {{-- header kepala hrd --}}
        @canany([
            'pengajuan_cuti_kepala_hrd.read',
            'verifikasi_cuti_kepala_hrd.read',
        ])
            <li class="nav-heading">kepala Hrd</li>
        @endcanany

        @can('pengajuan_cuti_kepala_hrd.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pengajuan.cuti.kepala.hrd.page') ? '' : ' collapsed' }}" href="{{ route('pengajuan.cuti.kepala.hrd.page') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Pengajuan Cuti</span>
                </a>
            </li>
        @endcan

        @can('verifikasi_cuti_kepala_hrd.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('verifikasi.cuti.kepala.hrd.page') ? '' : ' collapsed' }}" href="{{ route('verifikasi.cuti.kepala.hrd.page') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Verifikasi Cuti</span>
                </a>
            </li>
        @endcan

        {{-- header staff hrd --}}
        @canany([
            'pengajuan_cuti_staff_hrd.read',
            'verifikasi_cuti_staff_hrd.read',
        ])
            <li class="nav-heading">Staff Hrd</li>
        @endcanany

        @can('pengajuan_cuti_staff_hrd.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pengajuan.cuti.staff.hrd.page') ? '' : ' collapsed' }}" href="{{ route('pengajuan.cuti.staff.hrd.page') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Pengajuan Cuti</span>
                </a>
            </li>
        @endcan

        @can('verifikasi_cuti_staff_hrd.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('verifikasi.cuti.hrd.page') ? '' : ' collapsed' }}" href="{{ route('verifikasi.cuti.hrd.page') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Verifikasi Cuti</span>
                </a>
            </li>
        @endcan

        {{-- header kadep --}}
        @canany([
            'manajemen_tenaga_pendidik_all.read',
        ])
            <li class="nav-heading">Kepala Departemen</li>
        @endcanany

        @can('manajemen_tenaga_pendidik_all.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kelola.pegawai.page') ? '' : ' collapsed' }}" href="{{ route('kelola.pegawai.page') }}">
                    <i class="bi bi-people"></i>
                    <span>Tenaga Pendidik</span>
                </a>
            </li>
        @endcan

        {{-- header kepsek --}}
        @canany([
            'manajemen_tenaga_pendidik_kepsek.read',
        ])
            <li class="nav-heading">Kepala Sekolah</li>
        @endcanany

        @can('manajemen_tenaga_pendidik_kepsek.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kelola.pegawai.page') ? '' : ' collapsed' }}" href="{{ route('kelola.pegawai.page') }}">
                    <i class="bi bi-people"></i>
                    <span>Tenaga Pendidik</span>
                </a>
            </li>
        @endcan

        {{-- header kepsek dan kadep --}}
        @canany([
            'pengajuan_cuti_kepsek.read',
            'verifikasi_cuti_kepsek.read',
        ])
            <li class="nav-heading">Kepala Sekolah & Kepala Departemen</li>
        @endcanany

        @can('pengajuan_cuti_kepsek.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pengajuan.cuti.kepsek.page') ? '' : ' collapsed' }}" href="{{ route('pengajuan.cuti.kepsek.page') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Pengajuan Cuti</span>
                </a>
            </li>
        @endcan

        @can('verifikasi_cuti_kepsek.read')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('verifikasi.cuti.kepsek.page') ? '' : ' collapsed' }}" href="{{ route('verifikasi.cuti.kepsek.page') }}">
                <i class="bi bi-calendar-event"></i>
                <span>Verifikasi Cuti</span>
            </a>
        </li>
        @endcan

        {{-- header tenaga pendidik --}}
        @canany([
            'pengajuan_cuti_tenaga_pendidik.read',
            ])

            <li class="nav-heading">Tenaga Pendidik</li>
        @endcanany

        @can('pengajuan_cuti_tenaga_pendidik.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('pengajuan.cuti.tendik.page') ? '' : ' collapsed' }}" href="{{ route('pengajuan.cuti.tendik.page') }}">
                    <i class="bi bi-calendar-event"></i>
                    <span>Pengajuan Cuti</span>
                </a>
            </li>
        @endcan

        {{-- header role dan permission --}}
        @canany([
            'manajemen_role.read',
            'manajemen_hak_akses.read',
            'manajemen_hak_akses_user.read',
        ])
            <li class="nav-heading">Roles & Permission</li>
        @endcanany

        @can('manajemen_role.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('role.index') ? '' : ' collapsed' }}" href="{{ route('role.index') }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Roles</span>
                </a>
            </li>
        @endcan

        @can('manajemen_hak_akses.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('permission.index') ? '' : ' collapsed' }}" href="{{ route('permission.index') }}">
                    <i class="bi bi-person-video2"></i>
                    <span>Hak Akses</span>
                </a>
            </li>
        @endcan

        @can('manajemen_hak_akses_user.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user.assign.index') ? '' : ' collapsed' }}" href="{{ route('user.assign.index') }}">
                    <i class="bi bi-person-gear"></i>
                    <span>Hak Akses Pengguna</span>
                </a>
            </li>
        @endcan

        {{-- header other --}}
        @canany([
            'manajemen_jabatan.read',
            'manajemen_departemen.read',
            'manajemen_sosial_media.read',
            'manajemen_tempat_kerja.read',
            ])
            <li class="nav-heading">other</li>
        @endcanany

        @can('manajemen_jabatan.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('jabatan.index') ? '' : ' collapsed' }}" href="{{ route('jabatan.index') }}">
                    <i class="bi bi-diagram-3"></i>
                    <span>Jabatan</span>
                </a>
            </li>
        @endcan

        @can('manajemen_departemen.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('departemen.index') ? '' : ' collapsed' }}" href="{{ route('departemen.index') }}">
                    <i class="bi bi-buildings"></i>
                    <span>Departemen</span>
                </a>
            </li>
        @endcan

        @can('manajemen_tempat_kerja.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kantor.index') ? '' : ' collapsed' }}" href="{{ route('kantor.index') }}">
                    <i class="bi bi-building"></i>
                    <span>Kantor</span>
                </a>
            </li>
        @endcan

        @can('manajemen_sosial_media.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('sosmed.index') ? '' : ' collapsed' }}" href="{{ route('sosmed.index') }}">
                    <i class="bi bi-linkedin"></i>
                    <span>Sosial Media</span>
                </a>
            </li>
        @endcan

        @can('manajemen_tahun_ajaran.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('tahun.ajaran.index') ? '' : ' collapsed' }}" href="{{ route('tahun.ajaran.index') }}">
                    <i class="bi bi-calendar3"></i>
                    <span>Tahun Ajaran</span>
                </a>
            </li>
        @endcan

        @can('manajemen_kategori_evaluasi.read')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('kategori.evaluasi.index') ? '' : ' collapsed' }}" href="{{ route('kategori.evaluasi.index') }}">
                    <i class="bi bi-list-nested"></i>
                    <span>Kategori Evaluasi</span>
                </a>
            </li>
        @endcan
    </ul>
</aside><!-- End Sidebar-->
