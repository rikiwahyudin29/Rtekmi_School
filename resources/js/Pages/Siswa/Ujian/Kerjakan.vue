<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import axios from 'axios';
const props = defineProps({
    sesi: Object,
    soal: Array,
    jadwal: Object,
    draft: Object
});

const isRefreshing = ref(false);
const currentIndex = ref(0);
const stateSoal = ref([]);
const timeRemaining = ref('--:--:--');
let timerInterval = null;

// Violation State
const isWarningActive = ref(false);
const countdownVal = ref(0);
let warningTimer = null;
const showWarningOverlay = ref(false);

const AFK_TIMEOUT = props.jadwal.setting_afk_timeout || 15;
const IS_STRICT = props.jadwal.setting_strict == 1;

const initSoal = () => {
    props.soal.forEach((s, i) => {
        
        let isIsi = false;
        let parsedJawaban = null;
        
        try {
            parsedJawaban = s.jawaban_siswa ? JSON.parse(s.jawaban_siswa) : null;
        } catch (e) {
            parsedJawaban = s.jawaban_siswa;
        }

        if (s.jenis_soal == 1) { // PG
            isIsi = s.jawaban_siswa !== null && s.jawaban_siswa !== '';
        } else if (s.jenis_soal == 6) { // Benar Salah Matrix
            isIsi = typeof parsedJawaban === 'object' && parsedJawaban !== null && Object.keys(parsedJawaban).length > 0;
            s.jawaban_siswa_parsed = typeof parsedJawaban === 'object' && parsedJawaban !== null ? parsedJawaban : {};
        } else if (s.jenis_soal == 3) { // PG Multi
            isIsi = Array.isArray(parsedJawaban) && parsedJawaban.length > 0;
            s.jawaban_siswa_parsed = Array.isArray(parsedJawaban) ? parsedJawaban : [];
        } else if (s.jenis_soal == 4) { // Pasangan
            isIsi = typeof parsedJawaban === 'object' && parsedJawaban !== null && Object.keys(parsedJawaban).length > 0;
            s.jawaban_siswa_parsed = typeof parsedJawaban === 'object' && parsedJawaban !== null ? parsedJawaban : {};
        } else if (s.jenis_soal == 2 || s.jenis_soal == 5) { // Esai / Isian
            isIsi = s.jawaban_siswa !== null && s.jawaban_siswa.trim() !== '';
            s.jawaban_siswa_parsed = s.jawaban_siswa || '';
        }
        
        stateSoal.value.push({
            index: i,
            id: s.soal_id,
            ragu: s.ragu == 1,
            isi: isIsi,
            jenis_soal: s.jenis_soal,
            sync_status: 'synced' // 'synced', 'saving', 'failed'
        });
    });
};
initSoal();

onMounted(() => {

    startTimer();
    
    window.addEventListener('beforeunload', () => { isRefreshing.value = true; });

    if (IS_STRICT) {
        document.addEventListener("visibilitychange", handleVisibilityChange);
        window.addEventListener("blur", handleBlur);
        document.addEventListener('contextmenu', (e) => e.preventDefault());
    }
    
    // Auto Fullscreen on first click
    document.body.addEventListener('click', autoFullscreen, { once: true });
    
    // Auto-Retry Background Worker (Setiap 5 detik)
    setInterval(() => {
        const failedItems = stateSoal.value.filter(s => s.sync_status === 'failed');
        failedItems.forEach(item => {
            const soalData = props.soal.find(s => s.soal_id === item.id);
            if (soalData) {
                // Kirim ulang jawaban secara rahasia di background
                saveAnswerData({ jawaban_siswa: soalData.jawaban_siswa }, item.id, true);
            }
        });
    }, 5000);
});

onBeforeUnmount(() => {
    clearInterval(timerInterval);
    clearInterval(warningTimer);
    document.removeEventListener("visibilitychange", handleVisibilityChange);
    window.removeEventListener("blur", handleBlur);
    document.body.removeEventListener('click', autoFullscreen);
});

const autoFullscreen = () => {
    let elem = document.documentElement;
    if (!document.fullscreenElement) {
        if (elem.requestFullscreen) {
            elem.requestFullscreen().catch(err => console.log("Fullscreen error: ", err));
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    }
};

const handleVisibilityChange = () => {
    if (document.hidden && !isRefreshing.value) triggerWarning();
};

const handleBlur = () => {
    if (!isRefreshing.value) triggerWarning();
};

const triggerWarning = () => {
    if (isWarningActive.value) return;
    isWarningActive.value = true;
    countdownVal.value = AFK_TIMEOUT;
    showWarningOverlay.value = true;

    warningTimer = setInterval(() => {
        countdownVal.value--;
        if (countdownVal.value <= 0) { 
            clearInterval(warningTimer); 
            laporServer('timeout'); 
        }
    }, 1000);
};

const kembaliKeUjian = () => {
    if (countdownVal.value > 0) {
        clearInterval(warningTimer);
        laporServer('violation'); 
        showWarningOverlay.value = false;
        isWarningActive.value = false;
    }
};

const laporServer = (jenis) => {
    axios.post(route('siswa.ujian.pelanggaran'), {
        id_ujian_siswa: props.sesi.id,
        jenis: jenis
    }).then(response => {
        const d = response.data;
        if(d.status === 'locked') {
            window.location.reload();
        } else if(d.status === 'kicked') {
            showWarningOverlay.value = false;
            window.Swal.fire({
                title: 'Diskualifikasi!', 
                text: d.msg, 
                icon: 'error',
                allowOutsideClick: false
            }).then(() => {
                router.visit(route('siswa.ujian.index'));
            });
        } else if(d.status === 'warning') {
            props.sesi.jml_pelanggaran++; 
        }
    });
};

const startTimer = () => {
    // end_at is a UNIX timestamp in seconds
    const endTime = props.sesi.end_at ? props.sesi.end_at * 1000 : new Date(props.jadwal.waktu_selesai).getTime();
    
    timerInterval = setInterval(() => {
        const now = new Date().getTime();
        const dist = endTime - now;
        
        if (dist < 0) { 
            clearInterval(timerInterval); 
            selesaiUjian(true); 
            return; 
        }
        
        const h = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const m = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((dist % (1000 * 60)) / 1000);
        timeRemaining.value = `${h<10?'0'+h:h}:${m<10?'0'+m:m}:${s<10?'0'+s:s}`;
    }, 1000);
};

const currentSoal = computed(() => {
    return props.soal[currentIndex.value];
});

const isFirst = computed(() => currentIndex.value === 0);
const isLast = computed(() => currentIndex.value === props.soal.length - 1);

const nextSoal = () => {
    if (!isLast.value) currentIndex.value++;
};

const prevSoal = () => {
    if (!isFirst.value) currentIndex.value--;
};

const showSoal = (index) => {
    currentIndex.value = index;
    isSidebarOpen.value = false;
};

const isSidebarOpen = ref(false);
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const toggleRagu = (e) => {
    const checked = e.target.checked;
    stateSoal.value[currentIndex.value].ragu = checked;
    
    saveAnswerData({ ragu: checked ? 1 : 0 });
};

const saveAnswerPG = (soalId, opsiId) => {
    stateSoal.value[currentIndex.value].isi = true;
    currentSoal.value.jawaban_siswa = opsiId;
    saveAnswerData({ jawaban_siswa: opsiId });
};

const saveAnswerKompleks = (soalId, opsiId, event) => {
    const checked = event.target.checked;
    let arr = currentSoal.value.jawaban_siswa_parsed || [];
    
    if (checked) {
        if (!arr.includes(opsiId)) arr.push(opsiId);
    } else {
        arr = arr.filter(id => id !== opsiId);
    }
    
    currentSoal.value.jawaban_siswa_parsed = arr;
    stateSoal.value[currentIndex.value].isi = arr.length > 0;
    
    saveAnswerData({ jawaban_siswa: arr });
};

const saveAnswerPasangan = (soalId, opsiKiriId, event) => {
    const opsiKananId = event.target.value;
    let obj = currentSoal.value.jawaban_siswa_parsed || {};
    
    if (opsiKananId) {
        obj[opsiKiriId] = opsiKananId;
    } else {
        delete obj[opsiKiriId];
    }
    
    currentSoal.value.jawaban_siswa_parsed = obj;
    stateSoal.value[currentIndex.value].isi = Object.keys(obj).length > 0;
    
    saveAnswerData({ jawaban_siswa: obj });
};

const saveAnswerEsai = (soalId, event) => {
    const text = event.target.value;
    currentSoal.value.jawaban_siswa_parsed = text;
    stateSoal.value[currentIndex.value].isi = text.trim() !== '';
    
    saveAnswerData({ jawaban_siswa: text });
};

const saveAnswerData = (data, manualSoalId = null, isRetry = false) => {
    data.id_ujian_siswa = props.sesi.id;
    const targetSoalId = manualSoalId || currentSoal.value.soal_id;
    data.soal_id = targetSoalId;
    
    // Update status lokal menjadi saving
    const sIndex = stateSoal.value.findIndex(s => s.id == targetSoalId);
    if (sIndex > -1) {
        stateSoal.value[sIndex].sync_status = 'saving';
    }

    axios.post(route('siswa.ujian.simpan-jawaban'), data).then(res => {
        if (sIndex > -1) stateSoal.value[sIndex].sync_status = 'synced';
    }).catch(err => {
        if (sIndex > -1) stateSoal.value[sIndex].sync_status = 'failed';
        
        if (err.response && err.response.status === 419) {
            window.Swal.fire({
                title: 'Sesi Habis!',
                text: 'Sesi Anda telah berakhir. Silakan muat ulang (refresh) halaman ini.',
                icon: 'error',
                confirmButtonText: 'Tutup'
            });
        } else if (!isRetry) {
            // Kita tidak perlu memunculkan error popup lagi, cukup andalkan warna merah di navigasi.
            console.error('Koneksi terputus. Menunggu auto-retry...');
        }
    });
};

const saveAnswerBenarSalah = (soalId, opsiId, value) => {
    const sIndex = stateSoal.value.findIndex(s => s.id == soalId);
    if (sIndex > -1) {
        stateSoal.value[sIndex].isi = true; 
    }
    
    const soalItem = props.soal.find(s => s.soal_id == soalId);
    if (soalItem) {
        if (!soalItem.jawaban_siswa_parsed || typeof soalItem.jawaban_siswa_parsed !== 'object') {
            soalItem.jawaban_siswa_parsed = {};
        }
        soalItem.jawaban_siswa_parsed[opsiId] = value;
        const text = JSON.stringify(soalItem.jawaban_siswa_parsed);
        soalItem.jawaban_siswa = text;
        saveAnswerData({ jawaban_siswa: text });
    }
};

const cekSelesai = () => {
    const now = new Date().getTime();
    const startTime = new Date(props.sesi.waktu_mulai).getTime();
    const minMenit = props.draft.minFinishTime || 0;
    const batasSelesai = startTime + (minMenit * 60000); 

    if (now < batasSelesai) {
        const sisaMs = batasSelesai - now;
        const sisaMenit = Math.ceil(sisaMs / 60000);
        
        window.Swal.fire({
            title: 'Belum Bisa Dikumpulkan!',
            html: `Sesuai aturan panitia, Anda wajib mengerjakan ujian minimal <b>${minMenit} menit</b>.<br><br>Silakan teliti kembali jawaban Anda dan coba kumpulkan lagi sekitar <b>${sisaMenit} menit</b> lagi.`,
            icon: 'error',
            customClass: { confirmButton: 'bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg m-2' },
            buttonsStyling: false 
        });
        return; 
    }

    // Ping check koneksi internet
    fetch(window.location.origin, { method: 'HEAD', cache: 'no-store' }).then(() => {
        lanjutCekSelesai();
    }).catch(() => {
        window.Swal.fire('Koneksi Terputus!', 'Sistem mendeteksi jaringan Anda sedang terputus. Silakan cari sinyal atau hubungkan kembali internet Anda sebelum mengumpulkan ujian.', 'error');
    });
};

const lanjutCekSelesai = () => {
    let belumDijawab = stateSoal.value.filter(s => !s.isi).length;
    let raguRagu = stateSoal.value.filter(s => s.ragu).length;
    let gagalSimpan = stateSoal.value.filter(s => s.sync_status === 'failed').length;
    let sedangSimpan = stateSoal.value.filter(s => s.sync_status === 'saving').length;
    
    if (gagalSimpan > 0 || sedangSimpan > 0) {
        window.Swal.fire('Menunggu Sinkronisasi!', `Masih ada ${gagalSimpan + sedangSimpan} jawaban yang belum berhasil terkirim ke server (warna merah/kuning). Tunggu sebentar sampai semua berubah hijau sebelum Selesai.`, 'warning');
        return;
    }
    
    let htmlPesan = "Apakah Anda yakin ingin mengakhiri ujian ini? Jawaban yang sudah dikirim tidak dapat diubah lagi.";
    let iconTipe = "question";

    if (belumDijawab > 0 || raguRagu > 0) {
        htmlPesan = `
            <div class="text-left text-sm mt-2">
                <p class="mb-2 text-rose-600 font-bold">Peringatan! Masih ada soal yang belum sempurna:</p>
                <ul class="list-disc pl-5 mb-2">
                    <li><b>${belumDijawab}</b> Soal Belum Dijawab</li>
                    <li><b>${raguRagu}</b> Soal Masih Ragu-ragu</li>
                </ul>
                <p>Apakah Anda benar-benar yakin ingin menyelesaikannya sekarang?</p>
            </div>
        `;
        iconTipe = "warning";
    }

    window.Swal.fire({
        title: 'Selesai Ujian?',
        html: htmlPesan,
        icon: iconTipe,
        showCancelButton: true,
        confirmButtonText: 'Ya, Kumpulkan!',
        cancelButtonText: 'Batal, Cek Lagi',
        customClass: {
            actions: 'flex flex-row-reverse gap-3 mt-4 w-full justify-center', 
            confirmButton: 'bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-6 rounded-xl shadow-md',
            cancelButton: 'bg-rose-500 hover:bg-rose-600 text-white font-bold py-3 px-6 rounded-xl shadow-md'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            selesaiUjian(false); 
        }
    });
};

const selesaiUjian = (isAuto) => {
    if (!isAuto) window.Swal.fire({ title: 'Menyimpan...', didOpen: () => window.Swal.showLoading() });
    
    axios.post(route('siswa.ujian.selesai'), {
        id_ujian_siswa: props.sesi.id,
        is_auto: isAuto ? 1 : 0
    }).then(response => {
        if (response.data.status === 'success') {
            window.Swal.close();
            router.visit(response.data.redirect);
        } else {
            window.Swal.fire('Gagal', response.data.message, 'error');
        }
    }).catch(err => {
        window.Swal.fire('Error', 'Terjadi kesalahan jaringan saat mengumpulkan. Silakan coba lagi.', 'error');
        console.error(err);
    });
};

const getNavClass = (state, index) => {
    let cls = 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600';
    
    // Status Prioritas: Failed (Merah) > Saving (Kuning) > Ragu (Amber) > Terisi (Hijau)
    if (state.sync_status === 'failed') {
        cls = 'bg-rose-500 text-white border-rose-500 dark:bg-rose-600 dark:border-rose-600 shadow-[0_0_10px_rgba(244,63,94,0.5)]';
    } else if (state.sync_status === 'saving') {
        cls = 'bg-amber-300 text-amber-900 border-amber-300 dark:bg-amber-400 dark:border-amber-400 animate-pulse';
    } else if (state.ragu) {
        cls = 'bg-amber-500 text-white border-amber-500 dark:bg-amber-600 dark:border-amber-600';
    } else if (state.isi) {
        cls = 'bg-emerald-500 text-white border-emerald-500 dark:bg-emerald-600 dark:border-emerald-600';
    }
    
    if (index === currentIndex.value) {
        cls += ' ring-4 ring-primary-500/50 scale-110 shadow-lg';
    }
    
    return cls;
};
</script>

<template>
    <Head title="Mengerjakan Ujian" />

    <!-- Overlay Warning Strict Mode -->
    <div v-if="showWarningOverlay" class="fixed inset-0 bg-gray-950/95 z-[9999] flex flex-col justify-center items-center text-center p-4">
        <div class="text-6xl text-rose-500 mb-6"><i class="fas fa-exclamation-triangle animate-pulse"></i></div>
        <h2 class="text-3xl font-bold mb-2 text-white">PELANGGARAN TERDETEKSI!</h2>
        <p class="text-gray-300 mb-8 max-w-lg text-lg">Anda terdeteksi meninggalkan halaman ujian atau keluar dari mode layar penuh.</p>
        
        <div class="bg-rose-900/40 border-2 border-rose-500 p-6 rounded-3xl mb-8 transform scale-110 shadow-2xl shadow-rose-900/50">
            <p class="text-sm uppercase tracking-widest text-rose-300 mb-2 font-bold">Ujian Terkunci Dalam</p>
            <div class="text-7xl font-mono font-bold text-white tracking-tighter">{{ countdownVal < 10 ? '0' + countdownVal : countdownVal }}</div>
            <p class="text-xs text-gray-400 mt-2 font-bold">DETIK</p>
        </div>

        <button @click="kembaliKeUjian" class="px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-2xl shadow-lg transition-transform active:scale-95 text-lg">
            KEMBALI MENGERJAKAN
        </button>
    </div>

    <div class="flex h-screen bg-gray-50 dark:bg-gray-900 overflow-hidden select-none" :class="{ 'filter blur-md pointer-events-none': showWarningOverlay }">
        
        <!-- Main Content -->
        <div class="flex-1 lg:flex-none flex flex-col h-full relative z-10 w-full lg:w-[calc(100%-20rem)]">
            
            <!-- Navbar Header -->
            <div class="bg-white dark:bg-gray-800 p-4 lg:px-8 shadow-sm flex justify-between items-center z-20 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <!-- Hamburger (Mobile) -->
                    <button @click="toggleSidebar" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="font-bold text-gray-800 dark:text-white truncate max-w-[200px] md:max-w-md lg:max-w-xl text-lg">{{ jadwal.nama_ujian }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-primary-600 bg-primary-50 dark:bg-primary-900/30 dark:text-primary-400 px-2 py-0.5 rounded border border-primary-100 dark:border-primary-800">{{ draft.nama_mapel || 'Mapel' }}</span>
                            <span v-if="IS_STRICT" class="text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 px-2 py-0.5 rounded border border-rose-100 dark:border-rose-800">STRICT MODE</span>
                            <span v-if="IS_STRICT" class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-0.5 rounded border border-amber-100 dark:border-amber-800">Nyawa: {{ (jadwal.setting_max_violation || 1) - (sesi.jml_pelanggaran || 0) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="bg-gray-900 text-white px-4 py-2 rounded-xl font-mono font-bold text-lg shadow-sm border border-gray-700 flex items-center gap-2">
                        <i class="far fa-clock text-primary-400 text-sm"></i> {{ timeRemaining }}
                    </div>
                </div>
            </div>

            <!-- Soal Container -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 relative custom-scrollbar">
                <div v-if="currentSoal" class="max-w-4xl mx-auto w-full">
                    
                    <!-- Meta Soal -->
                    <div class="flex justify-between items-center mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
                        <div class="flex items-center gap-4">
                            <span class="bg-primary-600 text-white w-12 h-12 flex items-center justify-center rounded-2xl font-bold text-xl shadow-lg shadow-primary-500/30">
                                {{ currentIndex + 1 }}
                            </span>
                            <div>
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest block">Soal Ke</span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 block">Dari {{ soal.length }} Butir</span>
                            </div>
                        </div>
                        
                        <label class="flex items-center gap-3 cursor-pointer bg-amber-50 dark:bg-amber-900/20 px-4 py-2 rounded-xl border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors select-none">
                            <input type="checkbox" class="w-5 h-5 text-amber-500 rounded border-gray-300 focus:ring-amber-500 dark:bg-gray-800 dark:border-gray-600" 
                                   :checked="stateSoal[currentIndex].ragu"
                                   @change="toggleRagu">
                            <span class="text-sm font-bold text-amber-700 dark:text-amber-400">Ragu-ragu</span>
                        </label>
                    </div>

                    <!-- Pertanyaan -->
                    <div class="text-lg text-gray-800 dark:text-gray-200 leading-relaxed mb-8 select-text">
                        <div v-html="currentSoal.pertanyaan" class="prose dark:prose-invert max-w-none"></div>
                        <img v-if="currentSoal.file_gambar" :src="`/storage/uploads/soal/${currentSoal.file_gambar}`" class="mt-4 max-w-full md:max-w-md rounded-xl shadow-sm" alt="Gambar Soal">
                        <audio v-if="currentSoal.file_audio" controls class="mt-4 w-full max-w-md rounded-full shadow-sm"><source :src="`/storage/uploads/soal/${currentSoal.file_audio}`"></audio>
                    </div>

                    <!-- Opsi Jawaban -->
                    <div class="space-y-4">
                        
                        <!-- Pilihan Ganda -->
                        <template v-if="currentSoal.jenis_soal == 1">
                            <label v-for="opsi in currentSoal.opsi" :key="opsi.id" class="group flex items-center p-4 md:p-5 border-2 rounded-2xl cursor-pointer transition-all border-gray-200 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-gray-800 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-900/20 dark:has-[:checked]:border-primary-500">
                                <input type="radio" :name="`jawaban_${currentSoal.soal_id}`" :value="opsi.id" class="peer sr-only"
                                       :checked="currentSoal.jawaban_siswa == opsi.id"
                                       @change="saveAnswerPG(currentSoal.soal_id, opsi.id)">
                                
                                <div class="w-7 h-7 border-2 border-gray-300 dark:border-gray-600 rounded-full group-has-[:checked]:bg-primary-600 group-has-[:checked]:border-primary-600 flex items-center justify-center mr-4 md:mr-5 flex-shrink-0 transition-all">
                                    <div class="w-3 h-3 bg-white rounded-full opacity-0 group-has-[:checked]:opacity-100 transition-opacity"></div>
                                </div>
                                <span class="text-gray-700 dark:text-gray-300 font-medium select-text" v-html="opsi.teks"></span>
                            </label>
                        </template>

                        <!-- Benar Salah (Matrix) -->
                        <template v-else-if="currentSoal.jenis_soal == 6">
                            <div class="bg-primary-50 dark:bg-gray-800/80 p-5 rounded-3xl border border-primary-100 dark:border-gray-700">
                                <p class="text-sm font-bold text-primary-800 dark:text-primary-300 mb-5 flex items-center">
                                    <i class="fas fa-check-double mr-2 text-lg"></i> Tentukan pernyataan berikut Benar atau Salah:
                                </p>
                                <div class="grid gap-3">
                                    <div v-for="opsi in currentSoal.opsi" :key="opsi.id" class="flex flex-col sm:flex-row sm:items-center justify-between bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                        <div class="w-full sm:w-2/3 font-medium text-gray-800 dark:text-gray-200 mb-3 sm:mb-0 pr-4 select-text" v-html="opsi.teks">
                                        </div>
                                        <div class="w-full sm:w-1/3 flex items-center justify-end gap-3">
                                            <label class="group flex items-center gap-2 cursor-pointer has-[:checked]:text-primary-600">
                                                <input type="radio" :name="`jawaban_${currentSoal.soal_id}_${opsi.id}`" value="1" class="peer sr-only"
                                                       :checked="currentSoal.jawaban_siswa_parsed?.[opsi.id] == '1'"
                                                       @change="saveAnswerBenarSalah(currentSoal.soal_id, opsi.id, '1')">
                                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full group-has-[:checked]:border-primary-600 group-has-[:checked]:bg-primary-600 flex items-center justify-center transition-all">
                                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 group-has-[:checked]:opacity-100 transition-opacity"></div>
                                                </div>
                                                <span class="text-sm font-bold">Benar</span>
                                            </label>
                                            
                                            <label class="group flex items-center gap-2 cursor-pointer has-[:checked]:text-rose-600">
                                                <input type="radio" :name="`jawaban_${currentSoal.soal_id}_${opsi.id}`" value="0" class="peer sr-only"
                                                       :checked="currentSoal.jawaban_siswa_parsed?.[opsi.id] == '0'"
                                                       @change="saveAnswerBenarSalah(currentSoal.soal_id, opsi.id, '0')">
                                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full group-has-[:checked]:border-rose-600 group-has-[:checked]:bg-rose-600 flex items-center justify-center transition-all">
                                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 group-has-[:checked]:opacity-100 transition-opacity"></div>
                                                </div>
                                                <span class="text-sm font-bold">Salah</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Pilihan Ganda Kompleks -->
                        <template v-else-if="currentSoal.jenis_soal == 3">
                            <div class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2"><i class="fas fa-check-square"></i> Pilih satu atau lebih jawaban yang benar:</div>
                            <label v-for="opsi in currentSoal.opsi" :key="opsi.id" class="flex items-center p-4 md:p-5 border-2 rounded-2xl cursor-pointer transition-all border-gray-200 dark:border-gray-700 hover:bg-primary-50 dark:hover:bg-gray-800 has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-900/20 dark:has-[:checked]:border-primary-500">
                                <input type="checkbox" :name="`jawaban_${currentSoal.soal_id}[]`" :value="opsi.id" class="peer sr-only"
                                       :checked="currentSoal.jawaban_siswa_parsed?.includes(opsi.id)"
                                       @change="saveAnswerKompleks(currentSoal.soal_id, opsi.id, $event)">
                                
                                <div class="w-7 h-7 border-2 border-gray-300 dark:border-gray-600 rounded-lg group-has-[:checked]:bg-primary-600 group-has-[:checked]:border-primary-600 flex items-center justify-center mr-4 md:mr-5 flex-shrink-0 transition-all">
                                    <i class="fas fa-check text-white text-sm opacity-0 group-has-[:checked]:opacity-100 transition-opacity"></i>
                                </div>
                                <span class="text-gray-700 dark:text-gray-300 font-medium select-text" v-html="opsi.teks"></span>
                            </label>
                        </template>

                        <!-- Menjodohkan -->
                        <template v-else-if="currentSoal.jenis_soal == 4">
                            <div class="bg-primary-50 dark:bg-gray-800/80 p-5 rounded-3xl border border-primary-100 dark:border-gray-700">
                                <p class="text-sm font-bold text-primary-800 dark:text-primary-300 mb-5 flex items-center">
                                    <i class="fas fa-link mr-2 text-lg"></i> Pasangkan pernyataan kiri dengan jawaban yang tepat di sebelah kanan:
                                </p>
                                <div class="grid gap-3">
                                    <div v-for="kiri in currentSoal.opsi_kiri" :key="kiri.id" class="flex flex-col sm:flex-row sm:items-center justify-between bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                        <div class="w-full sm:w-1/2 font-medium text-gray-800 dark:text-gray-200 mb-3 sm:mb-0 pr-4 select-text">
                                            {{ kiri.teks }}
                                        </div>
                                        <div class="w-full sm:w-1/2 relative">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                                <i class="fas fa-arrow-right"></i>
                                            </div>
                                            <select class="w-full p-3 pl-10 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-primary-500 text-sm text-gray-800 dark:text-gray-200 cursor-pointer shadow-inner"
                                                    @change="saveAnswerPasangan(currentSoal.soal_id, kiri.id, $event)">
                                                <option value="">-- Pilih Pasangan --</option>
                                                <option v-for="kanan in currentSoal.opsi_kanan" :key="kanan.id" :value="kanan.id"
                                                        :selected="currentSoal.jawaban_siswa_parsed?.[kiri.id] == kanan.id">
                                                    {{ kanan.teks }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Esai / Isian Singkat -->
                        <template v-else-if="currentSoal.jenis_soal == 2 || currentSoal.jenis_soal == 5">
                            <textarea class="w-full p-5 border-2 border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-4 focus:ring-primary-500/20 focus:border-primary-500 text-lg dark:bg-gray-800 dark:text-white transition-all shadow-inner select-text" 
                                      rows="6" placeholder="Ketik jawaban Anda di sini..." 
                                      :value="currentSoal.jawaban_siswa_parsed"
                                      @input="currentSoal.jawaban_siswa_parsed = $event.target.value; currentSoal.jawaban_siswa = $event.target.value"
                                      @blur="saveAnswerEsai(currentSoal.soal_id, $event)"></textarea>
                                      
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-medium flex items-center gap-2">
                                <i class="fas fa-info-circle text-primary-500"></i> Jawaban otomatis tersimpan saat Anda berpindah soal.
                            </p>
                        </template>

                    </div>
                </div>
            </div>

            <!-- Footer Navigation -->
            <div class="bg-white dark:bg-gray-800 p-4 border-t dark:border-gray-700 flex justify-between items-center z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <button @click="prevSoal" :disabled="isFirst" class="px-5 py-3 md:px-8 md:py-3.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-2xl font-bold disabled:opacity-50 transition-all flex items-center text-sm md:text-base">
                    <i class="fas fa-arrow-left mr-2"></i> Sebelumnya
                </button>
                
                <button v-if="!isLast" @click="nextSoal" class="px-5 py-3 md:px-8 md:py-3.5 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-bold flex items-center transition-all shadow-lg shadow-primary-500/30 text-sm md:text-base">
                    Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                </button>
                
                <button v-else @click="cekSelesai" class="px-5 py-3 md:px-8 md:py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold flex items-center transition-all shadow-lg shadow-emerald-500/30 text-sm md:text-base">
                    <i class="fas fa-check-circle mr-2"></i> Selesai Ujian
                </button>
            </div>
        </div>

        <!-- Sidebar List Soal -->
        <div :class="{'translate-x-0': isSidebarOpen, 'translate-x-full': !isSidebarOpen}" class="fixed inset-y-0 right-0 w-72 lg:w-80 bg-white dark:bg-gray-800 shadow-2xl lg:shadow-none transform lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 flex flex-col border-l border-gray-100 dark:border-gray-700">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-850">
                <h3 class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-widest text-sm flex items-center gap-2"><i class="fas fa-th text-primary-500"></i> Navigasi Soal</h3>
                <button @click="toggleSidebar" class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-500 hover:text-rose-500 transition-colors"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-5 custom-scrollbar">
                <div class="grid grid-cols-5 gap-3">
                    <button v-for="(state, idx) in stateSoal" :key="idx"
                            @click="showSoal(idx)"
                            :id="`nav-btn-${state.id}`"
                            class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-sm transition-all border-2"
                            :class="getNavClass(state, idx)">
                        {{ idx + 1 }}
                    </button>
                </div>
            </div>

            <div class="p-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-850 text-xs font-semibold text-gray-600 dark:text-gray-400 space-y-3">
                <div class="flex items-center gap-3"><div class="w-4 h-4 bg-emerald-500 rounded-md shadow-sm border border-emerald-600"></div> Sudah Dijawab</div>
                <div class="flex items-center gap-3"><div class="w-4 h-4 bg-amber-500 rounded-md shadow-sm border border-amber-600"></div> Ragu-ragu</div>
                <div class="flex items-center gap-3"><div class="w-4 h-4 bg-white dark:bg-gray-800 rounded-md shadow-sm border-2 border-gray-300 dark:border-gray-600"></div> Belum Dijawab</div>
            </div>
        </div>

        <!-- Overlay backdrop mobile sidebar -->
        <div v-if="isSidebarOpen" @click="toggleSidebar" class="fixed inset-0 bg-gray-900/50 z-30 lg:hidden backdrop-blur-sm"></div>

    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
