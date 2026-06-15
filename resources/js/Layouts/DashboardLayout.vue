<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

// Props
const page = usePage();
const auth = computed(() => page.props.auth);
const web = computed(() => page.props.web_settings || {});

// State
const isDarkMode = ref(false);
const isSidebarOpen = ref(false);
const currentTheme = computed(() => page.props.theme || 'theme-green');
const sidebarScrollArea = ref(null);

// Initialize Theme
onMounted(() => {
    // Check Local Storage or System Preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDarkMode.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDarkMode.value = false;
        document.documentElement.classList.remove('dark');
    }
    
    // Apply initial dynamic theme
    document.documentElement.classList.add(currentTheme.value);
});

// Watch for theme changes from server
watch(currentTheme, (newTheme, oldTheme) => {
    if (oldTheme) document.documentElement.classList.remove(oldTheme);
    document.documentElement.classList.add(newTheme);
});

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    }
};

// Auto-close sidebar on mobile when navigating to a new page
watch(() => page.url, () => {
    isSidebarOpen.value = false;
});

import { router } from '@inertiajs/vue3';

const changeTheme = (themeName) => {
    router.post(route('settings.theme'), { theme: themeName }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const openDropdowns = ref([]);
const toggleDropdown = (name) => {
    if (openDropdowns.value.includes(name)) {
        openDropdowns.value = openDropdowns.value.filter(n => n !== name);
    } else {
        openDropdowns.value.push(name);
    }
};

// Auto open active dropdowns on mount
onMounted(() => {
    const checkActive = (items) => {
        for (const item of items) {
            if (item.active && item.children) {
                if (!openDropdowns.value.includes(item.name)) openDropdowns.value.push(item.name);
            }
            if (item.children) {
                for (const child of item.children) {
                    if (child.active) {
                        if (!openDropdowns.value.includes(item.name)) openDropdowns.value.push(item.name);
                        return true;
                    }
                }
            }
        }
    };
    checkActive(menuItems.value);

    // Auto scroll to active item (wait for dropdown transition)
    setTimeout(() => {
        if (sidebarScrollArea.value) {
            const activeEl = sidebarScrollArea.value.querySelector('.text-primary-700.font-bold, .text-primary-600.font-bold');
            if (activeEl) {
                const containerTop = sidebarScrollArea.value.scrollTop;
                const containerBottom = containerTop + sidebarScrollArea.value.clientHeight;
                
                // Get absolute offset top relative to the scroll container
                let elTop = activeEl.offsetTop;
                let currentEl = activeEl.offsetParent;
                while (currentEl && currentEl !== sidebarScrollArea.value) {
                    elTop += currentEl.offsetTop;
                    currentEl = currentEl.offsetParent;
                }
                
                const elBottom = elTop + activeEl.clientHeight;

                if (elTop < containerTop || elBottom > containerBottom) {
                    sidebarScrollArea.value.scrollTo({
                        top: Math.max(0, elTop - 100),
                        behavior: 'smooth'
                    });
                }
            }
        }
    }, 350);
});

// Dynamic Menu based on Role
const menuItems = computed(() => {
    const role = auth.value.role;
    // Assuming 'roles' is an array of strings like ['admin', 'kurikulum']
    // If 'roles' isn't available, fallback to checking the active 'role'
    const userRoles = auth.value.roles || [role]; 

    const isSuperAdmin = userRoles.includes('superadmin') || userRoles.includes('kepsek');
    const isAdmin = userRoles.includes('admin');
    const isKurikulum = userRoles.includes('kurikulum');
    const isKesiswaan = userRoles.includes('kesiswaan');
    const isKeuangan = userRoles.includes('bendahara');
    const isSarpras = userRoles.includes('sarpras');
    const isPerpustakaan = userRoles.includes('perpustakaan');
    const isHumas = userRoles.includes('humas'); // Assuming Humas exists, or superadmin
    const isGuru = userRoles.includes('guru') || role === 'guru';
    
    let menus = [
        { name: 'Dashboard', icon: 'fas fa-th-large', route: 'dashboard', active: route().current('dashboard') },
        { name: 'Profil Saya', icon: 'fas fa-user-circle', route: '#', active: false },
    ];

    // --- 1. MENU ADMINISTRATOR ---
    if (isSuperAdmin) {
        menus.push(
            { isHeader: true, name: 'Menu Administrator' },
            { name: 'Master Data', icon: 'fas fa-building', route: '#', active: route().current('admin.master.*'), children: [
                { name: 'Tahun Ajaran', icon: 'fas fa-calendar-alt', route: 'admin.master.tahun-ajaran.index', active: route().current('admin.master.tahun-ajaran.*') },
                { name: 'Jenis Ujian', icon: 'fas fa-file-signature', route: 'admin.master.jenis-ujian.index', active: route().current('admin.master.jenis-ujian.*') },
                { name: 'Data Jurusan', icon: 'fas fa-network-wired', route: 'admin.master.jurusan.index', active: route().current('admin.master.jurusan.*') },
                { name: 'Data Ruangan', icon: 'fas fa-door-open', route: 'admin.master.ruangan.index', active: route().current('admin.master.ruangan.*') },
                { name: 'Data Kelas', icon: 'fas fa-layer-group', route: 'admin.master.kelas.index', active: route().current('admin.master.kelas.*') },
                { name: 'Mata Pelajaran', icon: 'fas fa-book', route: 'admin.master.mapel.index', active: route().current('admin.master.mapel.*') },
                { name: 'Master Ekskul', icon: 'fas fa-futbol', route: 'admin.master.ekskul.index', active: route().current('admin.master.ekskul.*') },
                { name: 'Jam Belajar (Bel)', icon: 'fas fa-clock', route: 'admin.master.jam-belajar.index', active: route().current('admin.master.jam-belajar.*') },
                { name: 'Identitas Sekolah', icon: 'fas fa-school', route: 'admin.master.sekolah.index', active: route().current('admin.master.sekolah.*') },
                { name: 'Integrasi Dapodik', icon: 'fas fa-sync', route: 'admin.master.dapodik.index', active: route().current('admin.master.dapodik.*') },
                { name: 'Backup Database', icon: 'fas fa-database', route: 'admin.master.backup.index', active: route().current('admin.master.backup.*') },
            ]},
            { name: 'Manajemen Pengguna', icon: 'fas fa-users-cog', route: '#', active: route().current('admin.guru.*') || route().current('admin.siswa.*') || route().current('admin.roles.*'), children: [
                { name: 'Data Guru', icon: 'fas fa-user-tie', route: 'admin.guru.index', active: route().current('admin.guru.*') },
                { name: 'Data Siswa', icon: 'fas fa-user-graduate', route: 'admin.siswa.index', active: route().current('admin.siswa.*') },
                { name: 'Hak Akses', icon: 'fas fa-user-lock', route: 'admin.roles.index', active: route().current('admin.roles.*') },
            ]}
        );
    }

    // --- 2. WAKASEK KURIKULUM ---
    if (isSuperAdmin || isKurikulum) {
        menus.push(
            { isHeader: true, name: 'Wakasek Kurikulum' },
            { name: 'Pembagian Tugas', icon: 'fas fa-tasks', route: 'admin.kurikulum.pembagian-tugas.index', active: route().current('admin.kurikulum.pembagian-tugas.*') },
            { name: 'Jadwal Pelajaran', icon: 'fas fa-calendar-day', route: 'admin.kurikulum.jadwal-pelajaran.index', active: route().current('admin.kurikulum.jadwal-pelajaran.*') },
            { name: 'Computer Based Test', icon: 'fas fa-laptop-code', route: '#', active: route().current('admin.cbt.*'), children: [
                { name: 'Bank Soal', icon: 'fas fa-database', route: 'admin.cbt.bank-soal.index', active: route().current('admin.cbt.bank-soal.*') },
                { name: 'Draft Ujian', icon: 'fas fa-clipboard-list', route: 'admin.cbt.draft-ujian.index', active: route().current('admin.cbt.draft-ujian.*') },
                { name: 'Jadwal Ujian', icon: 'fas fa-calendar-check', route: 'admin.cbt.jadwal-ujian.index', active: route().current('admin.cbt.jadwal-ujian.*') },
            { 
                name: 'Overview', 
                icon: 'fas fa-desktop', 
                route: 'admin.cbt.overview.index', 
                active: route().current('admin.cbt.overview.*') 
            },
                { name: 'Monitoring Peserta', icon: 'fas fa-users-viewfinder', route: '#', active: false },
                { name: 'E-Rapor & Monitoring', icon: 'fas fa-file-signature', route: 'admin.monitoring.rapor', active: route().current('admin.monitoring.rapor') },
            ]},
            { name: 'Kelulusan & SKL', icon: 'fas fa-user-graduate', route: '#', active: route().current('admin.kelulusan.*') || route().current('admin.ijazah.*'), children: [
                { name: 'Siswa Kelas 12', icon: 'fas fa-users', route: 'admin.kelulusan.index', active: route().current('admin.kelulusan.index') },
                { name: 'Manajemen Nilai', icon: 'fas fa-star', route: 'admin.kelulusan.nilai', active: route().current('admin.kelulusan.nilai') || route().current('admin.kelulusan.input_nilai') || route().current('admin.kelulusan.detail_nilai') },
                { name: 'Transkrip Ijazah', icon: 'fas fa-graduation-cap', route: 'admin.ijazah.index', active: route().current('admin.ijazah.*') },
                { name: 'Setting Kelulusan', icon: 'fas fa-cogs', route: 'admin.kelulusan.setting', active: route().current('admin.kelulusan.setting') },
            ]},
            { name: 'Manajemen P5', icon: 'fas fa-seedling', route: 'admin.p5.index', active: route().current('admin.p5.*') },
            { name: 'Kokurikuler', icon: 'fas fa-layer-group', route: 'admin.kokurikuler.index', active: route().current('admin.kokurikuler.*') },
            { name: 'UKK & Sertifikasi', icon: 'fas fa-certificate', route: '#', active: route().current('admin.ukk.*'), children: [
                { name: 'Data Master UKK', icon: 'fas fa-database', route: 'admin.ukk.index', active: route().current('admin.ukk.index') },
                { name: 'Kriteria Unjuk Kerja', icon: 'fas fa-list-check', route: 'admin.ukk.kuk', active: route().current('admin.ukk.kuk') },
                { name: 'Input Nilai UKK', icon: 'fas fa-star-half-alt', route: 'admin.ukk.nilai', active: route().current('admin.ukk.nilai') },
                { name: 'Buku Skill Passport', icon: 'fas fa-passport', route: 'admin.ukk.skill_passport', active: route().current('admin.ukk.skill_passport') },
            ]},
            { name: 'Manajemen PKL', icon: 'fas fa-briefcase', route: 'admin.pkl.kelompok', active: route().current('admin.pkl.*') }
        );
    }

    // --- 3. WAKASEK KESISWAAN ---
    if (isSuperAdmin || isKesiswaan) {
        menus.push(
            { isHeader: true, name: 'Wakasek Kesiswaan' },
            { name: 'PPDB Online', icon: 'fas fa-user-plus', route: '#', active: route().current('admin.ppdb.*'), children: [
                { name: 'Dashboard PPDB', icon: 'fas fa-chart-line', route: 'admin.ppdb.dashboard', active: route().current('admin.ppdb.dashboard') },
                { name: 'Data Pendaftar', icon: 'fas fa-users', route: 'admin.ppdb.index', active: route().current('admin.ppdb.index') || route().current('admin.ppdb.show') },
            ]},
            { name: 'Manajemen Kesiswaan', icon: 'fas fa-layer-group', route: '#', active: false, children: [
                { name: 'Buku Induk Siswa', icon: 'fas fa-book-reader', route: '#', active: false },
                { name: 'Manajemen Rombel', icon: 'fas fa-chalkboard', route: '#', active: false },
                { name: 'Data Alumni', icon: 'fas fa-graduation-cap', route: '#', active: false },
                { name: 'Tracer Study', icon: 'fas fa-search-location', route: '#', active: false }
            ]},
            { name: 'Presensi & Disiplin', icon: 'fas fa-fingerprint', route: '#', active: route().current('admin.presensi.*'), children: [
                { name: 'Scanner (QR/RFID)', icon: 'fas fa-qrcode', route: 'admin.presensi.scanner', active: route().current('admin.presensi.scanner') },
                { name: 'Laporan Kehadiran', icon: 'fas fa-clipboard-user', route: 'admin.presensi.laporan', active: route().current('admin.presensi.laporan') },
                { name: 'Data Izin/Sakit', icon: 'fas fa-envelope-open-text', route: 'admin.presensi.izin', active: route().current('admin.presensi.izin') },
                { name: 'Input Manual', icon: 'fas fa-keyboard', route: 'admin.presensi.manual', active: route().current('admin.presensi.manual') },
                { name: 'Rekap Kehadiran', icon: 'fas fa-calendar-check', route: 'admin.presensi.rekap', active: route().current('admin.presensi.rekap') },
                { name: 'Setting Jam & QR', icon: 'fas fa-user-clock', route: 'admin.presensi.setting_jam', active: route().current('admin.presensi.setting_jam') },
                { name: 'Hari Libur', icon: 'fas fa-calendar-times', route: 'admin.presensi.hari_libur.index', active: route().current('admin.presensi.hari_libur.*') },
                { name: 'Kartu Pelajar & Guru', icon: 'fas fa-id-card', route: 'admin.presensi.kartu.index', active: route().current('admin.presensi.kartu.*') },
            ]},
            { name: 'Tugas Piket', icon: 'fas fa-shield-alt', route: '#', active: false, children: [
                { name: 'Monitoring Piket', icon: 'fas fa-eye', route: '#', active: false },
                { name: 'Jurnal Piket', icon: 'fas fa-book-open', route: '#', active: false },
                { name: 'Izin Keluar', icon: 'fas fa-door-open', route: '#', active: false },
            ]},
            { name: 'Bina Ekstrakurikuler', icon: 'fas fa-futbol', route: '#', active: false, children: [
                { name: 'Dashboard Ekskul', icon: 'fas fa-chart-bar', route: '#', active: false },
                { name: 'Master & Pembina', icon: 'fas fa-users-cog', route: '#', active: false },
                { name: 'Ruang Pembina', icon: 'fas fa-chalkboard-teacher', route: '#', active: false },
            ]}
        );
    }

    // --- 4. WAKASEK HUMAS / HUBIN ---
    if (isSuperAdmin || isHumas) {
        menus.push(
            { isHeader: true, name: 'Wakasek Humas & Hubin' },
            { name: 'Website & Publikasi', icon: 'fas fa-globe', route: '#', active: route().current('admin.web.*'), children: [
                { name: 'Konfigurasi Web', icon: 'fas fa-cogs', route: 'admin.web.profil.index', active: route().current('admin.web.profil.*') },
                { name: 'Slider Banner', icon: 'fas fa-images', route: 'admin.web.slider.index', active: route().current('admin.web.slider.*') },
                { name: 'Berita & Artikel', icon: 'fas fa-newspaper', route: 'admin.web.berita.index', active: route().current('admin.web.berita.*') },
                { name: 'Galeri Foto', icon: 'fas fa-camera-retro', route: 'admin.web.galeri.index', active: route().current('admin.web.galeri.*') },
            ]},
            { name: 'Manajemen PKL & BKK', icon: 'fas fa-briefcase', route: '#', active: route().current('admin.pkl.*') || route().current('admin.web.dudi.*'), children: [
                { name: 'Dashboard PKL', icon: 'fas fa-chart-pie', route: '#', active: false },
                { name: 'Master DU/DI & Mitra', icon: 'fas fa-building', route: 'admin.web.dudi.index', active: route().current('admin.web.dudi.*') },
                { name: 'Mapping Siswa & Guru', icon: 'fas fa-project-diagram', route: '#', active: false },
            ]},
            { name: 'Surat & E-Office', icon: 'fas fa-envelope-open-text', route: '#', active: route().current('admin.surat.*'), children: [
                { name: 'Template Surat', icon: 'fas fa-magic', route: 'admin.surat.template.index', active: route().current('admin.surat.template.*') },
                { name: 'Surat Masuk', icon: 'fas fa-inbox', route: 'admin.surat.masuk.index', active: route().current('admin.surat.masuk.*') },
                { name: 'Surat Keluar', icon: 'fas fa-paper-plane', route: 'admin.surat.keluar.index', active: route().current('admin.surat.keluar.*') },
                { name: 'E-Arsip Surat', icon: 'fas fa-archive', route: 'admin.surat.arsip.index', active: route().current('admin.surat.arsip.*') },
            ]},
            { name: 'Buku Tamu', icon: 'fas fa-address-book', route: '#', active: false }
        );
    }

    // --- 5. WAKASEK SARPRAS ---
    if (isSuperAdmin || isSarpras) {
        menus.push(
            { isHeader: true, name: 'Wakasek Sarpras' },
            { name: 'Aset & Fasilitas', icon: 'fas fa-boxes', route: '#', active: false, children: [
                { name: 'Inventaris Barang', icon: 'fas fa-box-open', route: '#', active: false },
                { name: 'Peminjaman Ruangan', icon: 'fas fa-door-open', route: '#', active: false },
                { name: 'Laporan Kerusakan', icon: 'fas fa-tools', route: '#', active: false },
            ]}
        );
    }

    // --- 6. BENDAHARA / KEUANGAN ---
    if (isSuperAdmin || isKeuangan) {
        menus.push(
            { isHeader: true, name: 'Keuangan & Bendahara' },
            { name: 'Keuangan Sekolah', icon: 'fas fa-wallet', route: '#', active: route().current('admin.keuangan.*'), children: [
                { name: 'Pos Bayar', icon: 'fas fa-tags', route: 'admin.keuangan.pos.index', active: route().current('admin.keuangan.pos.*') },
                { name: 'Setting Pembayaran', icon: 'fas fa-file-invoice-dollar', route: 'admin.keuangan.jenis.index', active: route().current('admin.keuangan.jenis.*') || route().current('admin.keuangan.tagihan.*') },
                { name: 'Kasir Pembayaran', icon: 'fas fa-cash-register', route: 'admin.keuangan.pembayaran.index', active: route().current('admin.keuangan.pembayaran.*') },
                { name: 'Pengeluaran Ops', icon: 'fas fa-shopping-cart', route: 'admin.keuangan.pengeluaran.index', active: route().current('admin.keuangan.pengeluaran.*') },
                { name: 'Laporan Keuangan', icon: 'fas fa-chart-line', route: 'admin.keuangan.laporan.index', active: route().current('admin.keuangan.laporan.*') },
                { name: 'Log Aktivitas', icon: 'fas fa-history', route: 'admin.keuangan.log.index', active: route().current('admin.keuangan.log.*') },
                { name: 'Blast Tagihan WA', icon: 'fab fa-whatsapp', route: 'admin.keuangan.notif.index', active: route().current('admin.keuangan.notif.*') },
            ]},
            { name: 'Layanan Perbankan', icon: 'fas fa-university', route: '#', active: false, children: [
                { name: 'Bank Mini (Tabungan)', icon: 'fas fa-piggy-bank', route: '#', active: false },
            ]}
        );
    }

    // --- 7. PERPUSTAKAAN & BK ---
    if (isSuperAdmin || isPerpustakaan || isGuru) {
        menus.push(
            { isHeader: true, name: 'Layanan Pendukung' },
            { name: 'Perpustakaan', icon: 'fas fa-book', route: '#', active: false, children: [
                { name: 'OPAC (Katalog)', icon: 'fas fa-search', route: '#', active: false },
                { name: 'Sirkulasi & Scanner', icon: 'fas fa-barcode', route: '#', active: false },
            ]},
            { name: 'Bimbingan Konseling (BK)', icon: 'fas fa-hands-helping', route: '#', active: false, children: [
                { name: 'Kedisiplinan & Pelanggaran', icon: 'fas fa-user-shield', route: '#', active: false },
                { name: 'Catatan Konseling', icon: 'fas fa-notes-medical', route: '#', active: false },
            ]}
        );
    }

    // --- 8. AREA AKADEMIK GURU ---
    if (isSuperAdmin || isGuru) {
        menus.push(
            { isHeader: true, name: 'Area Akademik Guru' },
            { name: 'Layanan Guru', icon: 'fas fa-user-tie', route: '#', active: route().current('guru.presensi.*'), children: [
                { name: 'Absen Sekarang', icon: 'fas fa-user-clock', route: 'guru.presensi.absen_harian', active: route().current('guru.presensi.absen_harian') },
                { name: 'Absensi Saya', icon: 'fas fa-clipboard-user', route: 'guru.presensi.index', active: route().current('guru.presensi.index') },
                { name: 'Rekap Bulanan', icon: 'fas fa-calendar-check', route: 'guru.presensi.rekap', active: route().current('guru.presensi.rekap') },
                { name: 'Ajukan Izin/Dinas', icon: 'fas fa-envelope-open-text', route: 'guru.presensi.izin', active: route().current('guru.presensi.izin') },
                { name: 'Kotak Disposisi', icon: 'fas fa-inbox', route: '#', active: false },
            ]},
            { name: 'E-Learning (KBM)', icon: 'fas fa-laptop', route: '#', active: false, children: [
                { name: 'Materi Pelajaran', icon: 'fas fa-book-open', route: '#', active: false },
                { name: 'Tugas & Ujian', icon: 'fas fa-tasks', route: '#', active: false },
            ]},
            { name: 'Pembelajaran KBM', icon: 'fas fa-chalkboard-teacher', route: '#', active: false, children: [
                { name: 'Jadwal Mengajar', icon: 'fas fa-calendar-day', route: 'guru.jadwal-mengajar.index', active: route().current('guru.jadwal-mengajar.*') },
                { name: 'Jurnal KBM', icon: 'fas fa-book-open', route: '#', active: false },
                { name: 'Tujuan Pembelajaran', icon: 'fas fa-bullseye', route: 'guru.penilaian.tp', active: route().current('guru.penilaian.tp') },
                { name: 'Input Nilai Formatif', icon: 'fas fa-clipboard-check', route: 'guru.penilaian.formatif', active: route().current('guru.penilaian.formatif') },
                { name: 'Input Nilai Sumatif', icon: 'fas fa-star', route: 'guru.penilaian.sumatif', active: route().current('guru.penilaian.sumatif') },
                { name: 'Input Nilai Sikap K13', icon: 'fas fa-heart', route: 'guru.penilaian.sikap_k13', active: route().current('guru.penilaian.sikap_k13') },
                { name: 'Generate Nilai Akhir', icon: 'fas fa-calculator', route: 'guru.penilaian.halaman_generate_nilai_akhir', active: route().current('guru.penilaian.*generate_nilai*') },
                { name: 'E-Learning Lanjutan', icon: 'fas fa-laptop', route: '#', active: false },
            ]},
            { name: 'Ujian & Evaluasi', icon: 'fas fa-laptop-code', route: '#', active: route().current('admin.cbt.*'), children: [
                { name: 'Bank Soal (Guru)', icon: 'fas fa-database', route: 'admin.cbt.bank-soal.index', active: route().current('admin.cbt.bank-soal.*') },
                { name: 'Draft Ujian (Guru)', icon: 'fas fa-clipboard-list', route: 'admin.cbt.draft-ujian.index', active: route().current('admin.cbt.draft-ujian.*') },
                { name: 'Jadwal Ujian (Guru)', icon: 'fas fa-calendar-alt', route: 'admin.cbt.jadwal-ujian.index', active: route().current('admin.cbt.jadwal-ujian.*') },
                { name: 'Mengawas Ujian', icon: 'fas fa-eye', route: 'admin.cbt.overview.index', active: route().current('admin.cbt.overview.*') },
            ]},
            { name: 'Tugas Tambahan Guru', icon: 'fas fa-id-badge', route: '#', active: route().current('guru.walikelas.*') || route().current('guru.ekskul.*') || route().current('guru.p5.*') || route().current('guru.kokurikuler.*'), children: [
                { name: 'Dashboard Wali Kelas', icon: 'fas fa-home', route: 'guru.walikelas.index', active: route().current('guru.walikelas.index') },
                { name: 'Koordinator P5', icon: 'fas fa-seedling', route: 'guru.p5.index', active: route().current('guru.p5.*') },
                { name: 'Kokurikuler', icon: 'fas fa-layer-group', route: 'guru.kokurikuler.index', active: route().current('guru.kokurikuler.*') },
                { name: 'Pembina Ekskul', icon: 'fas fa-volleyball-ball', route: 'guru.ekskul.index', active: route().current('guru.ekskul.*') },
                { name: 'Pembimbing PKL', icon: 'fas fa-briefcase', route: 'guru.pkl.index', active: route().current('guru.pkl.*') },
                { name: 'Monitoring Piket Guru', icon: 'fas fa-shield-alt', route: '#', active: false },
                { name: '--- MENU WALI KELAS ---', icon: 'fas fa-users', route: '#', active: false },
                { name: 'Input Kehadiran', icon: 'fas fa-calendar-check', route: 'guru.walikelas.kehadiran', active: route().current('guru.walikelas.kehadiran') },
                { name: 'Catatan Wali Kelas', icon: 'fas fa-edit', route: 'guru.walikelas.catatan', active: route().current('guru.walikelas.catatan') },
                { name: 'Data PKL (Umum)', icon: 'fas fa-briefcase', route: 'guru.walikelas.pkl', active: route().current('guru.walikelas.pkl') },
                { name: 'Data PKL (K13)', icon: 'fas fa-briefcase', route: 'guru.walikelas.pkl_k13', active: route().current('guru.walikelas.pkl_k13') },
                { name: 'Deskripsi P3 (K13)', icon: 'fas fa-quote-left', route: 'guru.walikelas.deskripsi_p3', active: route().current('guru.walikelas.deskripsi_p3') },
                { name: 'Deskripsi DPL (K13)', icon: 'fas fa-quote-right', route: 'guru.walikelas.deskripsi_dpl', active: route().current('guru.walikelas.deskripsi_dpl') },
                { name: 'Status Kenaikan', icon: 'fas fa-level-up-alt', route: 'guru.walikelas.kenaikan', active: route().current('guru.walikelas.kenaikan') },
                { name: 'Cetak Rapor', icon: 'fas fa-print', route: 'guru.walikelas.cetak_rapor', active: route().current('guru.walikelas.cetak_rapor') },
                { name: 'Cetak Buku Induk', icon: 'fas fa-book', route: 'guru.walikelas.buku_induk', active: route().current('guru.walikelas.buku_induk') },
                { name: 'Cetak Ijazah', icon: 'fas fa-graduation-cap', route: 'guru.walikelas.ijazah', active: route().current('guru.walikelas.ijazah') },
                { name: 'Kelola Skill Passport', icon: 'fas fa-id-card', route: 'guru.walikelas.skill_passport', active: route().current('guru.walikelas.skill_passport') },
                { name: 'Kelola UKK', icon: 'fas fa-certificate', route: 'guru.walikelas.ukk', active: route().current('guru.walikelas.ukk') },
            ]}
        );
    }

    if (role === 'siswa') {
        return [
            { name: 'Dashboard', icon: 'fas fa-th-large', route: 'dashboard', active: route().current('dashboard') },
            { name: 'Profil Saya', icon: 'fas fa-user-circle', route: '#', active: false },
            { isHeader: true, name: 'Area Akademik Siswa' },
            { name: 'Materi & Bahan Ajar', icon: 'fas fa-book-reader', route: '#', active: false },
            { name: 'Tugas Sekolah', icon: 'fas fa-tasks', route: '#', active: false },
            { name: 'Rapor (Hasil Belajar)', icon: 'fas fa-file-signature', route: 'siswa.rapor.index', active: route().current('siswa.rapor.*') },
            { isHeader: true, name: 'Computer Based Test' },
            { name: 'Masuk Ujian CBT', icon: 'fas fa-laptop-code', route: 'siswa.ujian.index', active: route().current('siswa.ujian.*') },
            { isHeader: true, name: 'Layanan & Administrasi' },
            { name: 'Kedisiplinan Saya', icon: 'fas fa-user-shield', route: '#', active: false },
            { name: 'Keuangan Saya', icon: 'fas fa-wallet', route: '#', active: false },
            { name: 'Buku Tabungan', icon: 'fas fa-piggy-bank', route: '#', active: false },
            { name: 'Presensi', icon: 'fas fa-user-clock', route: '#', active: route().current('siswa.presensi.*'), children: [
                { name: 'Absen Datang/Pulang', route: 'siswa.presensi.absen_harian', active: route().current('siswa.presensi.absen_harian') },
                { name: 'Riwayat Absen', route: 'siswa.presensi.index', active: route().current('siswa.presensi.index') },
                { name: 'Rekap Kehadiran', route: 'siswa.presensi.rekap', active: route().current('siswa.presensi.rekap') },
                { name: 'Ajukan Izin/Sakit', route: 'siswa.presensi.izin', active: route().current('siswa.presensi.izin') },
            ]},
        ];
    }

    return menus;
});
</script>

<template>
    <div class="min-h-screen bg-[#f4f6f8] dark:bg-gray-900 transition-colors duration-300 font-sans flex items-start text-gray-800 dark:text-gray-100 p-4 lg:p-6 gap-6 relative">
        
        <!-- Mobile Overlay -->
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="md:hidden fixed inset-0 bg-gray-900/50 dark:bg-black/50 z-40 backdrop-blur-sm transition-opacity rounded-3xl m-4"></div>

        <!-- Sidebar (Detached & Sticky) -->
        <aside :class="[
            'w-64 bg-white dark:bg-gray-800 rounded-3xl flex-shrink-0 flex-col shadow-sm transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden h-[calc(100vh-2rem)] lg:h-[calc(100vh-3rem)]',
            'fixed md:sticky top-4 lg:top-6 z-50',
            isSidebarOpen ? 'flex left-4 shadow-2xl' : '-left-72 md:left-0 md:flex hidden md:flex'
        ]">
            
            <!-- Logo -->
            <div class="h-24 flex items-center px-8 border-b border-transparent">
                <div class="flex items-center gap-3">
                    <img v-if="web.logo" :src="web.logo.includes('default') ? '/images/' + web.logo : '/uploads/identitas/' + web.logo" class="w-9 h-9 object-contain" alt="Logo">
                    <div v-else class="w-9 h-9 rounded-xl bg-primary-500 text-white flex items-center justify-center font-bold">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span class="font-bold text-sm leading-tight tracking-tight text-gray-900 dark:text-white line-clamp-2">{{ web.nama_sekolah || 'SIAKAD' }}</span>
                </div>
            </div>

            <!-- Scrollable Menu -->
            <div ref="sidebarScrollArea" class="flex-1 overflow-y-auto py-6 px-5 space-y-8 no-scrollbar">
                
                <!-- Dynamic Menu Rendering -->
                <div>
                    <ul class="space-y-1">
                        <template v-for="item in menuItems" :key="item.name">
                            <!-- Header -->
                            <li v-if="item.isHeader" class="px-3 mt-6 mb-2">
                                <h3 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ item.name }}</h3>
                            </li>
                            
                            <!-- Normal Menu or Dropdown -->
                            <li v-else>
                                <!-- No Children -->
                                <Link v-if="!item.children" :href="item.route && item.route !== '#' ? route(item.route) : '#'" 
                                      @click="isSidebarOpen = false"
                                      class="flex items-center gap-3.5 px-4 py-3 rounded-2xl transition-all duration-200 group relative"
                                      :class="item.active ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-200 font-medium'">
                                    
                                    <!-- Active Indicator -->
                                    <div v-if="item.active" class="absolute left-0 w-1.5 h-6 bg-primary-500 rounded-r-full -ml-5"></div>
                                    
                                    <i :class="[item.icon, 'w-5 text-center text-lg shrink-0']"></i>
                                    <span class="truncate">{{ item.name }}</span>
                                </Link>

                                <!-- With Children (Dropdown) -->
                                <div v-else>
                                    <button @click="toggleDropdown(item.name)" 
                                          class="w-full flex items-center justify-between px-4 py-3 rounded-2xl transition-all duration-200 group relative"
                                          :class="item.active || openDropdowns.includes(item.name) ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-200 font-medium'">
                                        
                                        <div class="flex items-center gap-3.5 min-w-0">
                                            <!-- Active Indicator for Parent -->
                                            <div v-if="item.active && !openDropdowns.includes(item.name)" class="absolute left-0 w-1.5 h-6 bg-primary-500 rounded-r-full -ml-5"></div>
                                            
                                            <i :class="[item.icon, 'w-5 text-center text-lg shrink-0']"></i>
                                            <span class="truncate text-left">{{ item.name }}</span>
                                        </div>
                                        <i class="fas fa-chevron-down text-[10px] transition-transform duration-300 shrink-0 ml-2" :class="openDropdowns.includes(item.name) ? 'rotate-180' : ''"></i>
                                    </button>

                                    <!-- Dropdown Content -->
                                    <transition
                                        enter-active-class="transition-all duration-300 ease-in-out overflow-hidden"
                                        enter-from-class="max-h-0 opacity-0"
                                        enter-to-class="max-h-96 opacity-100"
                                        leave-active-class="transition-all duration-300 ease-in-out overflow-hidden"
                                        leave-from-class="max-h-96 opacity-100"
                                        leave-to-class="max-h-0 opacity-0"
                                    >
                                        <ul v-show="openDropdowns.includes(item.name)" class="pl-12 pr-4 py-2 space-y-1">
                                            <li v-for="child in item.children" :key="child.name">
                                                <Link :href="child.route && child.route !== '#' ? route(child.route) : '#'" 
                                                      @click="isSidebarOpen = false"
                                                      class="flex items-center gap-3 py-2 text-sm transition-colors relative group"
                                                      :class="child.active ? 'text-primary-600 dark:text-primary-400 font-bold' : 'text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-300 font-medium'">
                                                    
                                                    <i :class="[child.icon || 'fas fa-circle', child.active ? 'text-primary-500' : 'text-gray-400 group-hover:text-primary-400', child.icon ? 'text-sm' : 'text-[6px]']" class="w-4 text-center transition-colors"></i>
                                                    <span class="truncate">{{ child.name }}</span>
                                                </Link>
                                            </li>
                                        </ul>
                                    </transition>
                                </div>
                            </li>
                        </template>

                        <!-- Logout / General Settings -->
                        <li class="px-3 mt-8 mb-2">
                            <h3 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">System</h3>
                        </li>
                        <li>
                            <Link :href="route('profile.edit')" 
                                  @click="isSidebarOpen = false"
                                  class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-200 font-medium transition-colors">
                                <i class="fas fa-cog w-5 text-center text-lg"></i>
                                <span>Settings</span>
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('logout')" method="post" as="button"
                                  @click="isSidebarOpen = false"
                                  class="w-full flex items-center gap-3.5 px-4 py-3 rounded-2xl text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 font-medium transition-colors">
                                <i class="fas fa-sign-out-alt w-5 text-center text-lg"></i>
                                <span>Logout</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Mobile App Promo -->
            <div class="p-5 mt-auto shrink-0">
                <div class="bg-gradient-to-br from-primary-900 to-primary-950 dark:from-primary-950 dark:to-black rounded-[1.5rem] p-6 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                    <div class="absolute -top-6 -left-6 w-24 h-24 bg-primary-400/10 rounded-full blur-xl"></div>
                    
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div class="flex gap-2 mb-4">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-md border border-white/10">
                                <i class="fab fa-apple text-white text-lg"></i>
                            </div>
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-md border border-white/10">
                                <i class="fab fa-google-play text-white text-sm"></i>
                            </div>
                        </div>
                        <h4 class="font-bold text-sm mb-1 leading-tight">Download our<br>Mobile App</h4>
                        <p class="text-[10px] text-primary-200/60 mb-5">Get easy in another way</p>
                        <button class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 rounded-xl text-xs transition-colors shadow-lg shadow-primary-900/50 border border-primary-500/50">
                            Download
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col gap-6 overflow-hidden sticky top-4 lg:top-6 h-[calc(100vh-2rem)] lg:h-[calc(100vh-3rem)]">
            
            <!-- Navbar (Detached) -->
            <header class="h-20 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 flex items-center justify-between px-6 lg:px-8 shrink-0 shadow-sm">
                
                <!-- Search -->
                <div class="flex-1 max-w-xl hidden md:block">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text" class="w-full bg-gray-50 dark:bg-gray-700/50 border-none rounded-2xl py-3 pl-12 pr-16 text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-primary-500/50 transition-all" placeholder="Search task, student, or class...">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-[10px] font-bold text-gray-400 bg-white dark:bg-gray-800 px-2 py-1 rounded-md shadow-sm border border-gray-100 dark:border-gray-700">⌘F</span>
                        </div>
                    </div>
                </div>

                <div class="md:hidden flex items-center gap-3">
                    <button @click="isSidebarOpen = true" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>

                <!-- Right Nav -->
                <div class="flex items-center gap-3 lg:gap-5">
                    
                    <!-- Theme Color Picker (Admin Only) -->
                    <div v-if="auth.role === 'admin' || auth.role === 'kepsek'" class="hidden lg:flex items-center gap-2 mr-2 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <button @click="changeTheme('theme-green')" :class="currentTheme === 'theme-green' ? 'ring-2 ring-offset-1 ring-green-500 dark:ring-offset-gray-800' : ''" class="w-6 h-6 rounded-full bg-green-500 hover:scale-110 transition-transform" title="Green Theme"></button>
                        <button @click="changeTheme('theme-blue')" :class="currentTheme === 'theme-blue' ? 'ring-2 ring-offset-1 ring-blue-500 dark:ring-offset-gray-800' : ''" class="w-6 h-6 rounded-full bg-blue-500 hover:scale-110 transition-transform" title="Blue Theme"></button>
                        <button @click="changeTheme('theme-purple')" :class="currentTheme === 'theme-purple' ? 'ring-2 ring-offset-1 ring-purple-500 dark:ring-offset-gray-800' : ''" class="w-6 h-6 rounded-full bg-purple-500 hover:scale-110 transition-transform" title="Purple Theme"></button>
                        <button @click="changeTheme('theme-orange')" :class="currentTheme === 'theme-orange' ? 'ring-2 ring-offset-1 ring-orange-500 dark:ring-offset-gray-800' : ''" class="w-6 h-6 rounded-full bg-orange-500 hover:scale-110 transition-transform" title="Orange Theme"></button>
                    </div>

                    <!-- Redis Status Badge -->
                    <div v-if="auth.role === 'admin' || auth.role === 'kepsek' || auth.role === 'superadmin'" class="hidden md:flex items-center gap-2 bg-gray-50 dark:bg-gray-700/50 px-3 py-1.5 rounded-xl border border-gray-100 dark:border-gray-700" title="Status Koneksi Redis">
                        <span class="relative flex h-2.5 w-2.5">
                          <span v-if="$page.props.redis_status" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="$page.props.redis_status ? 'bg-emerald-500' : 'bg-red-500'"></span>
                        </span>
                        <span class="text-xs font-bold" :class="$page.props.redis_status ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                            Redis {{ $page.props.redis_status ? 'Active' : 'Offline' }}
                        </span>
                    </div>

                    <button @click="toggleDarkMode" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                        <i class="fas" :class="isDarkMode ? 'fa-sun text-yellow-500' : 'fa-moon'"></i>
                    </button>

                    <button class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                        <i class="far fa-envelope text-lg"></i>
                    </button>

                    <button class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all relative">
                        <i class="far fa-bell text-lg"></i>
                        <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-red-500 rounded-full border-2 border-gray-50 dark:border-gray-700"></span>
                    </button>

                    <div class="h-8 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block mx-1"></div>

                    <!-- User Profile -->
                    <div class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition-opacity">
                        <img :src="auth.user.avatar_url || `https://ui-avatars.com/api/?name=${auth.user.nama_lengkap || auth.user.username}&background=random`" class="w-10 h-10 rounded-full shadow-sm object-cover" alt="Avatar">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-bold text-gray-900 dark:text-white leading-tight mb-0.5">{{ auth.user.nama_lengkap || auth.user.username }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight font-medium">{{ auth.user.email || (auth.role.toUpperCase()) }}</p>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Page Content (Slot) -->
            <main class="flex-1 overflow-y-auto no-scrollbar">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>
