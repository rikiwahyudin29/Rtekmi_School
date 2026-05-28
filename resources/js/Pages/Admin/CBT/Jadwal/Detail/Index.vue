<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PesertaModal from './PesertaModal.vue';
import KoreksiModal from './KoreksiModal.vue';
import axios from 'axios';

const props = defineProps({
    id: [String, Number]
});

const page_title = ref('Detail Jadwal Ujian');
const jadwalData = ref(null);
const statusData = ref(null);
const progressData = ref([]);
const kelasList = ref([]);
const selectedIds = ref([]);
const selectAll = ref(false);

const filter = ref({
    kelas: 'all',
    status: 'all',
    query: ''
});

// Pagination
const currentPage = ref(1);
const perPage = ref(10);
const isLoading = ref(true);
const autoRefresh = ref(false);
const autoRefreshCounter = ref(10);
let refreshInterval = null;
let countdownInterval = null;
const serverTimeOffset = ref(0);
const now = ref(Math.floor(Date.now() / 1000));

// Modals State
const showFilterModal = ref(false);
const showDownloadModal = ref(false);
const showExtraTimeModal = ref(false);

// Child Components Refs
const pu = ref(null);
const jawabanCorrection = ref(null);

const loadStatus = async () => {
    try {
        const response = await axios.get(route('admin.cbt.jadwal-ujian.status', props.id));
        statusData.value = response.data;
    } catch (error) {
        console.error("Gagal memuat status:", error);
    }
};

const loadProgress = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post(route('admin.cbt.jadwal-ujian.progress', props.id), {
            kelas: filter.value.kelas,
            status: filter.value.status
        });
        
        serverTimeOffset.value = Math.floor(Date.now() / 1000) - response.data.server_time;
        progressData.value = response.data.data.map(p => ({
            ...p,
            checked: false,
            extra_time: 1
        }));
        
        // Extract unique kelas for filter
        const uniqueKelas = [...new Set(response.data.data.filter(p => p.nama_kelas).map(p => p.nama_kelas))];
        kelasList.value = uniqueKelas.map(k => ({ value: k, text: k }));
        
    } catch (error) {
        console.error("Gagal memuat progress:", error);
    } finally {
        isLoading.value = false;
    }
};

const reload = () => {
    loadStatus();
    loadProgress();
};

const toggleAutoRefresh = () => {
    if (autoRefresh.value) {
        refreshInterval = setInterval(() => {
            autoRefreshCounter.value--;
            if (autoRefreshCounter.value <= 0) {
                reload();
                autoRefreshCounter.value = 10;
            }
        }, 1000);
    } else {
        clearInterval(refreshInterval);
        autoRefreshCounter.value = 10;
    }
};

const formatTimeLeft = (secs) => {
    if (secs < 0) secs = 0;
    let h = Math.floor(secs / 3600); 
    let m = Math.floor((secs % 3600) / 60); 
    let s = Math.ceil((secs % 3600) % 60);
    let text = "";
    if (h > 0) text += h + " Jam ";
    if (m > 0) text += m + " Mnt ";
    if (s > 0) text += s + " Dtk";
    return text.trim() || "0 Menit";
};

const parsedProgressData = computed(() => {
    let filtered = progressData.value;
    
    // Apply local search query
    if (filter.value.query) {
        const q = filter.value.query.toLowerCase();
        filtered = filtered.filter(p => 
            (p.nama && p.nama.toLowerCase().includes(q)) || 
            (p.nomor_induk && p.nomor_induk.toLowerCase().includes(q))
        );
    }
    
    if (filter.value.kelas !== 'all') {
        filtered = filtered.filter(p => p.id_kelas == filter.value.kelas);
    }
    
    if (filter.value.status !== 'all') {
        filtered = filtered.filter(p => p.status == filter.value.status);
    }

    return filtered.map(p => {
        let elapsed = 0;
        let timeLeft = 0;
        
        if (p.start_at && statusData.value?.endDateTime) {
            elapsed = now.value - p.start_at;
            let timeLimit = p.lama_ujian * 60;
            if (p.sisa_waktu && p.sisa_waktu > 0) {
                timeLimit = p.sisa_waktu;
            }
            timeLeft = timeLimit - elapsed;
            if (timeLeft < 0) timeLeft = 0;
            
            let timeEnd = now.value - statusData.value.endDateTime;
            if (timeEnd > 0) {
                timeLeft = 0;
            }
        }
        
        return {
            ...p,
            parsedTimeLeft: p.status == 2 ? 'Selesai' : (p.status == 1 ? formatTimeLeft(timeLeft) : '-')
        };
    });
});

const totalPages = computed(() => Math.ceil(parsedProgressData.value.length / perPage.value));
const paginatedProgressData = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return parsedProgressData.value.slice(start, end);
});

const toggleSelectAll = (e) => {
    selectAll.value = e.target.checked;
    if (selectAll.value) {
        selectedIds.value = paginatedProgressData.value.map(p => p.id);
        paginatedProgressData.value.forEach(p => p.checked = true);
    } else {
        selectedIds.value = [];
        paginatedProgressData.value.forEach(p => p.checked = false);
    }
};

const updateSelectAllState = () => {
    selectAll.value = paginatedProgressData.value.length > 0 && selectedIds.value.length === paginatedProgressData.value.length;
    // sync checked prop
    paginatedProgressData.value.forEach(p => {
        p.checked = selectedIds.value.includes(p.id);
    });
};

const stateText = computed(() => {
    if (!statusData.value) return 'Menghitung...';
    if (statusData.value.status != 1) return 'Tidak Aktif';
    
    let time = now.value - statusData.value.startDateTime;
    let timeend = now.value - statusData.value.endDateTime;
    let future = false;
    
    if (time < 0) {
        future = true;
        time = -time;
    }
    
    let text = formatTimeLeft(time);
    
    if (timeend > 0) {
        return 'Selesai';
    } else if (future) {
        return text + ' Lagi';
    } else {
        return text + ' Lalu';
    }
});

const stateIcon = computed(() => {
    let state = stateText.value;
    if (state === 'Selesai') return 'far fa-calendar-check';
    if (state === 'Tidak Aktif') return 'far fa-calendar-times';
    if (state.includes('Lalu')) return 'fas fa-hourglass-start fa-spin';
    return 'far fa-clock';
});

const formatDateTime = (unix) => {
    if (!unix) return '-';
    const date = new Date(unix * 1000);
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0') + ' ' + String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0');
};

const loadToken = async () => {
    if (statusData.value?.use_token != 1) return;
    try {
        const response = await axios.post(route('admin.cbt.jadwal-ujian.token', props.id));
        statusData.value.token = response.data.token;
        Swal.fire('Berhasil', 'Token ujian diperbarui', 'success');
    } catch (error) {
        Swal.fire('Gagal', 'Gagal memperbarui token', 'error');
    }
};

// ==========================================
// Aksi Massal & Tombol Bar
// ==========================================

const applyFilter = () => {
    currentPage.value = 1;
    showFilterModal.value = false;
    reload();
};

const resetPengerjaan = (id) => {
    Swal.fire({
        title: 'Generate / Reset Soal Ujian?',
        text: "Jika siswa sudah pernah ujian, seluruh jawaban dan nilainya akan dihapus agar bisa mengulang dari awal!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan!',
        confirmButtonColor: '#ef4444',
    }).then((res) => {
        if(res.isConfirmed) {
            axios.post(route('admin.cbt.jadwal-ujian.generate_single', props.id), { ujian_id: id })
            .then(() => {
                Swal.fire('Berhasil!', 'Soal berhasil di-generate.', 'success');
                reload();
            }).catch(e => {
                Swal.fire('Gagal!', e.response?.data?.message || 'Terjadi kesalahan', 'error');
            });
        }
    });
};

const finishSingle = (id) => {
    Swal.fire({
        title: 'Selesaikan Ujian?',
        text: "Peserta ini akan dipaksa selesai ujian dan tidak bisa lanjut.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesaikan!',
        confirmButtonColor: '#ef4444',
    }).then((res) => {
        if(res.isConfirmed) {
            axios.put(route('admin.cbt.jadwal-ujian.finish-batch', props.id), { id: [id] })
            .then(() => {
                Swal.fire('Berhasil!', 'Ujian telah diselesaikan.', 'success');
                reload();
            }).catch(e => Swal.fire('Gagal!', 'Terjadi kesalahan', 'error'));
        }
    });
};

const unlockSingle = (id) => {
    Swal.fire({
        title: 'Buka Kunci?',
        text: "Perangkat peserta akan di-unlock untuk login kembali.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buka!',
        confirmButtonColor: '#3b82f6',
    }).then((res) => {
        if(res.isConfirmed) {
            axios.post(route('admin.cbt.jadwal-ujian.unlock_batch', props.id), { id: [id] })
            .then(() => {
                Swal.fire('Berhasil!', 'Kunci perangkat berhasil dibuka.', 'success');
                reload();
            }).catch(e => Swal.fire('Gagal!', 'Terjadi kesalahan', 'error'));
        }
    });
};

// Batch Actions
const finishBatch = () => {
    if (selectedIds.value.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu peserta yang sedang/belum ujian!', 'warning');
        return;
    }
    Swal.fire({
        title: 'Selesaikan Ujian Terpilih?',
        text: "Peserta yang dipilih akan dipaksa selesai ujian.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesaikan!',
        confirmButtonColor: '#ef4444',
    }).then((res) => {
        if(res.isConfirmed) {
            Swal.showLoading();
            axios.put(route('admin.cbt.jadwal-ujian.finish-batch', props.id), { id: selectedIds.value })
            .then(() => {
                Swal.fire('Berhasil!', 'Ujian berhasil diselesaikan massal.', 'success');
                selectedIds.value = [];
                selectAll.value = false;
                reload();
            }).catch(e => Swal.fire('Gagal!', 'Terjadi kesalahan', 'error'));
        }
    });
};

const unlockBatch = () => {
    if (selectedIds.value.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu peserta yang terkunci (mengerjakan)!', 'warning');
        return;
    }
    Swal.fire({
        title: 'Buka Kunci Terpilih?',
        text: "Perangkat peserta yang dipilih akan di-unlock.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buka Kunci!',
        confirmButtonColor: '#3b82f6',
    }).then((res) => {
        if(res.isConfirmed) {
            Swal.showLoading();
            axios.post(route('admin.cbt.jadwal-ujian.unlock_batch', props.id), { id: selectedIds.value })
            .then(() => {
                Swal.fire('Berhasil!', 'Kunci perangkat berhasil dibuka massal.', 'success');
                selectedIds.value = [];
                selectAll.value = false;
                reload();
            }).catch(e => Swal.fire('Gagal!', 'Terjadi kesalahan', 'error'));
        }
    });
};

const generateBatch = () => {
    let msg = "Akan meng-generate soal untuk semua peserta ujian. Ini mungkin memakan waktu lama tergantung jumlah peserta.";
    if (selectedIds.value.length > 0) {
        msg = `Akan meng-generate soal untuk ${selectedIds.value.length} peserta yang dipilih.`;
    }

    Swal.fire({
        title: 'Generate Soal Ujian?',
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan!',
        confirmButtonColor: '#f59e0b',
    }).then((res) => {
        if(res.isConfirmed) {
            Swal.fire({ title: 'Sedang Memproses...', text: 'Mohon tunggu sebentar', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            axios.post(route('admin.cbt.jadwal-ujian.generate', props.id), { ids: selectedIds.value })
            .then(() => {
                Swal.fire('Berhasil!', 'Soal telah berhasil digenerate.', 'success');
                selectedIds.value = [];
                selectAll.value = false;
                reload();
            }).catch(e => Swal.fire('Gagal!', 'Gagal generate soal.', 'error'));
        }
    });
};

const addExtraTime = () => {
    const checkedData = progressData.value.filter(p => p.checked && [1,2].includes(p.status));
    if(checkedData.length === 0) return Swal.fire('Peringatan', 'Pilih minimal satu peserta yang sudah/sedang mengerjakan.', 'warning');
    
    let payload = checkedData.map(p => ({
        id: p.id, extra_time: p.extra_time
    }));

    Swal.showLoading();
    axios.post(route('admin.cbt.jadwal-ujian.extra_time', props.id), { data: payload })
    .then(() => {
        Swal.fire('Berhasil', 'Tambahan waktu berhasil disimpan.', 'success');
        showExtraTimeModal.value = false;
        reload();
    }).catch(e => Swal.fire('Gagal', 'Terjadi kesalahan', 'error'));
};

const printKehadiran = () => { window.open(route('admin.cbt.jadwal-ujian.print_hadir', props.id), '_blank'); };
const printNilai = () => { window.open(route('admin.cbt.jadwal-ujian.print_nilai', props.id), '_blank'); };
const exportBatch = () => { window.open(route('admin.cbt.jadwal-ujian.export_excel', props.id), '_blank'); };
const printJawaban = () => { window.open(route('admin.cbt.jadwal-ujian.print_jawaban', props.id), '_blank'); };

// Extra time helper
const isAllExtraTimeChecked = computed(() => {
    const validData = progressData.value.filter(v => [1,2].includes(v.status));
    return validData.length > 0 && validData.every(v => v.checked);
});
const toggleAllExtraTime = (e) => {
    const checked = e.target.checked;
    progressData.value.forEach(p => {
        if([1,2].includes(p.status)) p.checked = checked;
    });
};

onMounted(() => {
    reload();
    countdownInterval = setInterval(() => now.value = Math.floor(Date.now() / 1000) - serverTimeOffset.value, 1000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
    if (countdownInterval) clearInterval(countdownInterval);
});
</script>

<template>
    <Head :title="page_title" />
    <DashboardLayout>
        <!-- CSS INJECTED UNTUK OBAT KUAT ANTI-TAILWIND -->
        <component :is="'style'">
            /* 🔥 MAKE OVER KOTAK STATUS UJIAN 🔥 */
            .icon-card { background: #ffffff; border-radius: 12px; padding: 20px; display: flex; align-items: center; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #f1f5f9; }
            .icon-card .icon { height: 60px; width: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin-right: 20px; flex-shrink: 0; }
            .icon.purple { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
            .icon.success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
            .icon.orange { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
            .icon.primary { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
            .icon.danger { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
            .icon-card .content h6 { font-size: 14px; color: #64748b; margin-bottom: 4px; font-weight: 600; }
            .icon-card .content h3 { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; }
            .card-style { background: #ffffff; padding: 20px; border-radius: 12px; border: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
            
            /* 🔥 OBAT KUAT ANTI-TAILWIND: TOMBOL ATAS TABEL 🔥 */
            button.main-btn {
                display: inline-flex !important; align-items: center !important; justify-content: center !important;
                border: none !important; border-radius: 8px !important;
                color: #ffffff !important; margin-left: 6px !important; margin-bottom: 6px !important; cursor: pointer !important;
                transition: all 0.2s ease-in-out !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
                padding: 8px 12px !important;
            }
            button.main-btn.btn-sm { height: 38px !important; min-width: 38px !important; }
            button.main-btn i { font-size: 1.15rem !important; margin: 0 !important; }
            button.main-btn:hover { transform: translateY(-3px) !important; box-shadow: 0 6px 12px rgba(0,0,0,0.2) !important; filter: brightness(1.1); }
            
            button.primary-btn { background-color: #3b82f6 !important; }
            button.success-btn { background-color: #10b981 !important; }
            button.warning-btn { background-color: #f59e0b !important; color: #ffffff !important; }
            button.danger-btn  { background-color: #ef4444 !important; }

            /* 🔥 OBAT KUAT ANTI-TAILWIND: TOMBOL AKSI PER SISWA 🔥 */
            table button.btn {
                display: inline-flex !important; align-items: center !important; justify-content: center !important;
                padding: 8px !important; width: 34px !important; height: 34px !important;
                border-radius: 6px !important; font-weight: 600 !important; cursor: pointer !important;
                border: none !important; transition: all 0.2s !important; margin: 2px !important;
                box-shadow: 0 1px 2px rgba(0,0,0,0.1) !important; color: #ffffff !important;
            }
            table button.btn i { font-size: 1.1rem !important; }
            table button.btn-primary { background-color: #3b82f6 !important; }
            table button.btn-success { background-color: #10b981 !important; }
            table button.btn-danger { background-color: #ef4444 !important; }
            table button.btn-warning { background-color: #f59e0b !important; color: #000 !important; }
            table button.btn:hover { opacity: 0.9 !important; transform: translateY(-2px) !important; box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important; }

            /* 🔥 AUTO REFRESH TOGGLE CI4 🔥 */
            input[type="checkbox"].form-check-input.toggle-switch {
                -webkit-appearance: none !important; appearance: none !important;
                width: 44px !important; height: 24px !important; background-color: #cbd5e1 !important;
                border-radius: 9999px !important; position: relative !important; cursor: pointer !important;
                outline: none !important; vertical-align: middle !important; transition: all 0.3s ease !important;
                margin-right: 10px !important; border: none !important; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1) !important;
                display: inline-block !important;
            }
            input[type="checkbox"].form-check-input.toggle-switch::after {
                content: '' !important; position: absolute !important; top: 2px !important; left: 2px !important;
                width: 20px !important; height: 20px !important; background-color: white !important;
                border-radius: 50% !important; transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1) !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
            }
            input[type="checkbox"].form-check-input.toggle-switch:checked { background-color: #3b82f6 !important; }
            input[type="checkbox"].form-check-input.toggle-switch:checked::after { transform: translateX(20px) !important; }
            
            /* 🔥 CHECKBOX NORMAL 🔥 */
            input[type="checkbox"]:not(.toggle-switch) { width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6; outline: none !important; box-shadow: none !important; border: 1px solid #cbd5e1; border-radius: 4px; }
            
            /* MODAL BACKDROP */
            .custom-modal-backdrop {
                position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;
                background-color: rgba(15, 23, 42, 0.7); overflow-y: auto; backdrop-filter: blur(3px);
                display: flex; align-items: center; justify-content: center;
            }
            
            /* DATATABLE OVERRIDE */
            .table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
        </component>

        <div class="p-2 sm:ml-0 mt-4" id="appJadwalDetail">
            
            <!-- CI4 <jadwal-detail-status> Replication -->
            <div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 px-1">
                    <div class="col-span-1 lg:col-span-4 md:col-span-2">
                        <div class="icon-card !mb-0">
                            <div class="icon purple">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="content">
                                <h6>Nama Ujian</h6>
                                <h3>{{ statusData?.nama || 'Memuat...' }}</h3>
                                <span class="text-sm text-emerald-600">{{ statusData?.startDateTime ? formatDateTime(statusData.startDateTime) : '-' }}</span>
                                <span class="text-sm text-gray-500 mx-1">s/d</span>
                                <span class="text-sm text-red-500">{{ statusData?.endDateTime ? formatDateTime(statusData.endDateTime) : '-' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-span-1 md:col-span-1 lg:col-span-2">
                        <div class="icon-card h-full !mb-0">
                            <div class="icon success">
                                <i :class="stateIcon"></i>
                            </div>
                            <div class="content">
                                <h6>Status Ujian</h6>
                                <h2 style="font-size:20px; font-weight:800; margin:0;">{{ stateText }}</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-span-1 md:col-span-1 lg:col-span-2">
                        <div class="icon-card h-full !mb-0">
                            <div @click="loadToken" style="cursor:pointer" class="icon orange" title="Klik untuk Refresh Token">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="content">
                                <h6>Token</h6>
                                <h2 style="font-size:20px; font-weight:800; margin:0;">{{ statusData?.use_token == 1 ? (statusData?.token || '-') : '-' }}</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-span-1">
                        <div class="icon-card h-full !mb-0">
                            <div class="icon primary"><i class="fas fa-user-times"></i></div>
                            <div class="content"><h6>Belum Ujian</h6><h3>{{ statusData?.belum_ujian || 0 }}</h3></div>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <div class="icon-card h-full !mb-0">
                            <div class="icon purple"><i class="fas fa-spinner fa-spin"></i></div>
                            <div class="content"><h6>Sedang Ujian</h6><h3>{{ statusData?.sedang_ujian || 0 }}</h3></div>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <div class="icon-card h-full !mb-0">
                            <div class="icon success"><i class="fas fa-check-circle"></i></div>
                            <div class="content"><h6>Sudah Ujian</h6><h3>{{ statusData?.sudah_ujian || 0 }}</h3></div>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <div class="icon-card h-full !mb-0">
                            <div class="icon orange"><i class="fas fa-file-alt"></i></div>
                            <div class="content"><h6>Jenis Ujian</h6><h3>{{ statusData?.jenis_ujian || '-' }}</h3></div>
                        </div>
                    </div>
                </div>

                <!-- CI4 <jadwal-detail-progress> Replication -->
                <div class="card-style mt-6 mx-1">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                        <div>
                            <div class="flex items-center">
                                <input class="form-check-input toggle-switch" type="checkbox" v-model="autoRefresh" @change="toggleAutoRefresh">
                                <label class="font-semibold text-gray-700 cursor-pointer select-none" @click="autoRefresh = !autoRefresh; toggleAutoRefresh()">Auto Refresh <span v-if="autoRefresh" class="text-blue-500">({{autoRefreshCounter}})</span></label>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-wrap justify-end">
                            <button class="px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-all border border-gray-200 shadow-sm" @click="showFilterModal = true">
                                <i class="fas fa-filter text-gray-400"></i> Filter
                            </button>
                            <button class="px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-all border border-gray-200 shadow-sm" @click="generateBatch">
                                <i class="fas fa-cogs text-gray-400"></i> Generate Soal
                            </button>
                            <button class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-sm font-medium transition-all border border-blue-200 shadow-sm" @click="unlockBatch">
                                <i class="fas fa-unlock"></i> Buka Kunci
                            </button>
                            <button class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium transition-all border border-emerald-200 shadow-sm" @click="showExtraTimeModal = true">
                                <i class="fas fa-clock"></i> Tambah Waktu
                            </button>
                            <button class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-sm font-medium transition-all border border-rose-200 shadow-sm" @click="finishBatch">
                                <i class="fas fa-stop-circle"></i> Selesai Massal
                            </button>
                            <div class="relative group">
                                <button class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-all shadow-md shadow-emerald-500/30">
                                    <i class="fas fa-print"></i> Cetak / Ekspor <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                </button>
                                <div class="absolute right-0 top-full mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-20 overflow-hidden">
                                    <a href="#" @click.prevent="printKehadiran" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-clipboard-list w-5 text-emerald-500"></i> Daftar Hadir</a>
                                    <a href="#" @click.prevent="printNilai" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-award w-5 text-emerald-500"></i> Daftar Nilai</a>
                                    <a href="#" @click.prevent="exportBatch" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-file-excel w-5 text-emerald-500"></i> Ekspor Excel</a>
                                    <a href="#" @click.prevent="printJawaban" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-print w-5 text-emerald-500"></i> Jawaban</a>
                                </div>
                            </div>
                            <button class="px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-all border border-gray-200 shadow-sm" @click="reload">
                                <i class="fas fa-sync-alt" :class="{'fa-spin': isLoading}"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4 flex flex-wrap gap-4 items-center justify-between">
                        <div class="flex items-center">
                            <span class="mr-2 text-sm text-gray-600">Show</span>
                            <select v-model="perPage" class="border-gray-300 rounded text-sm w-20">
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                            <span class="ml-2 text-sm text-gray-600">entries</span>
                        </div>
                        <div class="flex items-center">
                            <span class="mr-2 text-sm text-gray-600">Search:</span>
                            <input type="text" v-model="filter.query" class="border-gray-300 rounded text-sm px-3 py-1.5 w-full sm:w-64">
                        </div>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-sm text-left border-collapse table-striped">
                            <thead class="border-b-2 border-gray-200" style="background-color: #f8fafc; color: #475569;">
                                <tr>
                                    <th class="p-3 text-center w-12"><input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event)"></th>
                                    <th class="p-3 font-bold uppercase text-xs">Nomor Induk</th>
                                    <th class="p-3 font-bold uppercase text-xs">Nama</th>
                                    <th class="p-3 font-bold uppercase text-xs">Kelas</th>
                                    <th class="p-3 font-bold uppercase text-xs text-center">Jawaban</th>
                                    <th class="p-3 font-bold uppercase text-xs text-center">Nilai</th>
                                    <th class="p-3 font-bold uppercase text-xs">Lama Ujian</th>
                                    <th class="p-3 font-bold uppercase text-xs text-center">Status</th>
                                    <th class="p-3 font-bold uppercase text-xs text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="isLoading && paginatedProgressData.length === 0">
                                    <td colspan="9" class="p-6 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i></td>
                                </tr>
                                <tr v-for="(peserta, i) in paginatedProgressData" :key="peserta.id" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-center"><input type="checkbox" :value="peserta.id" v-model="selectedIds" @change="updateSelectAllState"></td>
                                    <td class="p-3 text-gray-600">{{ peserta.nomor_induk }}</td>
                                    <td class="p-3 font-semibold text-gray-800 whitespace-nowrap">{{ peserta.nama }}</td>
                                    <td class="p-3 text-gray-600">{{ peserta.nama_kelas || '-' }}</td>
                                    <td class="p-3 text-center font-bold text-indigo-600 whitespace-nowrap">
                                        {{ (peserta.soal_pg_benar || 0) + (peserta.soal_pgmulti_benar || 0) + (peserta.soal_pgtf_benar || 0) + (peserta.soal_pgcouple_benar || 0) + (peserta.soal_shortentry_benar || 0) }} / 
                                        {{ (peserta.visible_pg || 0) + (peserta.visible_pgmulti || 0) + (peserta.visible_pgtf || 0) + (peserta.visible_pgcouple || 0) + (peserta.visible_shortentry || 0) + (peserta.visible_esai || 0) }}
                                    </td>
                                    <td class="p-3 text-center font-bold text-blue-600">{{ peserta.nilai || '0.0' }}</td>
                                    <td class="p-3 text-gray-600 whitespace-nowrap">{{ peserta.parsedTimeLeft }}</td>
                                    <td class="p-3 text-center">
                                        <span v-if="peserta.hasCheating == 1" class="bg-rose-100 text-rose-700 text-xs font-bold px-2 py-1 rounded">Terkunci</span>
                                        <span v-else-if="peserta.status == 0" class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-1 rounded">Belum Ujian</span>
                                        <span v-else-if="peserta.status == 1" class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-1 rounded">Dikerjakan</span>
                                        <span v-else-if="peserta.status == 2" class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded">Selesai</span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center flex-wrap">
                                            <template v-if="peserta.hasCheating == 1">
                                                <button @click="resetPengerjaan(peserta.id)" class="btn btn-warning" title="Generate/Reset Soal"><i class="fas fa-sync-alt"></i></button>
                                                <button @click="unlockSingle(peserta.id)" class="btn btn-success" title="Buka Kunci"><i class="fas fa-unlock"></i></button>
                                            </template>
                                            <template v-else-if="peserta.status == 0">
                                                <button @click="resetPengerjaan(peserta.id)" class="btn btn-warning" title="Generate/Reset Soal"><i class="fas fa-sync-alt"></i></button>
                                            </template>
                                            <template v-else-if="peserta.status == 1">
                                                <button @click="finishSingle(peserta.id)" class="btn btn-danger" title="Selesaikan Ujian"><i class="fas fa-flag-checkered"></i></button>
                                                <button @click="resetPengerjaan(peserta.id)" class="btn btn-warning" title="Generate/Reset Soal"><i class="fas fa-sync-alt"></i></button>
                                            </template>
                                            <template v-else-if="peserta.status == 2">
                                                <button @click="resetPengerjaan(peserta.id)" class="btn btn-warning" title="Generate/Reset Soal"><i class="fas fa-sync-alt"></i></button>
                                                <button @click="$refs.jawabanCorrection.open(peserta)" class="btn btn-primary" title="Koreksi Jawaban"><i class="fas fa-edit"></i></button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600 gap-4">
                        <div>Showing {{ parsedProgressData.length > 0 ? ((currentPage - 1) * perPage) + 1 : 0 }} to {{ Math.min(currentPage * perPage, parsedProgressData.length) }} of {{ parsedProgressData.length }} entries</div>
                        <div class="flex space-x-1 overflow-x-auto max-w-full pb-2">
                            <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50">Previous</button>
                            <button v-for="page in totalPages" :key="page" @click="currentPage = page" class="px-3 py-1 border rounded" :class="currentPage === page ? 'bg-blue-500 text-white border-blue-500' : 'hover:bg-gray-100'">{{ page }}</button>
                            <button @click="currentPage++" :disabled="currentPage === totalPages || totalPages === 0" class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50">Next</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Filter -->
            <div v-if="showFilterModal" class="custom-modal-backdrop px-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-gray-200">
                    <div class="flex justify-between items-center p-5 border-b border-gray-100">
                        <h5 class="text-xl font-bold text-slate-800">Filter</h5>
                        <button @click="showFilterModal = false" class="text-red-500 hover:text-red-700 text-2xl transition-transform hover:scale-110"><i class="fas fa-times-circle"></i></button>
                    </div>
                    <div class="p-5">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Kelas</label>
                            <select v-model="filter.kelas" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                                <option value="all">Semua</option>
                                <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Status</label>
                            <select v-model="filter.status" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                                <option value="all">Semua</option>
                                <option value="0">Belum Dikerjakan</option>
                                <option value="1">Dikerjakan</option>
                                <option value="2">Selesai</option>
                            </select>
                        </div>
                        <div class="text-right mt-6">
                            <button @click="applyFilter" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-bold shadow hover:bg-blue-700 transition-colors">Terapkan</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Unduh Data -->
            <div v-if="showDownloadModal" class="custom-modal-backdrop px-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-gray-200">
                    <div class="flex justify-between items-center p-5 border-b border-gray-100">
                        <h5 class="text-xl font-bold text-slate-800">Unduh Data</h5>
                        <button @click="showDownloadModal = false" class="text-red-500 hover:text-red-700 text-2xl transition-transform hover:scale-110"><i class="fas fa-times-circle"></i></button>
                    </div>
                    <div class="p-6 flex flex-col gap-3">
                        <button @click="printKehadiran" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg flex items-center shadow transition-colors"><i class="fas fa-print text-xl mr-3 w-6 text-center"></i> <span>Daftar Kehadiran</span></button>
                        <button @click="printNilai" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg flex items-center shadow transition-colors"><i class="fas fa-print text-xl mr-3 w-6 text-center"></i> <span>Print Daftar Nilai</span></button>
                        <button @click="exportBatch" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg flex items-center shadow transition-colors"><i class="fas fa-file-excel text-xl mr-3 w-6 text-center"></i> <span>Export Nilai</span></button>
                        <button @click="printJawaban" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg flex items-center shadow transition-colors"><i class="fas fa-print text-xl mr-3 w-6 text-center"></i> <span>Jawaban Peserta</span></button>
                    </div>
                </div>
            </div>

            <!-- Modal Tambah Waktu -->
            <div v-if="showExtraTimeModal" class="custom-modal-backdrop p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl overflow-hidden border border-gray-200 max-h-full flex flex-col">
                    <div class="flex justify-between items-center p-5 border-b border-gray-100 shrink-0">
                        <h5 class="text-xl font-bold text-slate-800">Tambah Waktu</h5>
                        <button @click="showExtraTimeModal = false" class="text-red-500 hover:text-red-700 text-2xl transition-transform hover:scale-110"><i class="fas fa-times-circle"></i></button>
                    </div>
                    <div class="p-5 overflow-y-auto grow">
                        <table class="w-full text-sm text-left border-collapse table-striped">
                            <thead class="border-b-2 border-gray-200" style="background-color: #f8fafc; color: #475569;">
                                <tr>
                                    <th class="p-3 w-12 text-center"><input type="checkbox" :checked="isAllExtraTimeChecked" @change="toggleAllExtraTime($event)"></th>
                                    <th class="p-3 font-bold uppercase">Nama</th>
                                    <th class="p-3 font-bold text-center uppercase">Status</th>
                                    <th class="p-3 font-bold text-center uppercase">Lama Ujian</th>
                                    <th class="p-3 font-bold text-center uppercase">Waktu Tambahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in progressData.filter(v=>[1,2].includes(v.status))" :key="p.id" class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-center"><input type="checkbox" v-model="p.checked"></td>
                                    <td class="p-3 whitespace-nowrap">{{ p.nomor_induk }} - {{ p.nama }}</td>
                                    <td class="p-3 text-center">{{ p.status == 1 ? 'Dikerjakan' : 'Selesai' }}</td>
                                    <td class="p-3 text-center whitespace-nowrap">{{ p.parsedTimeLeft }}</td>
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center">
                                            <input type="number" min="1" v-model="p.extra_time" class="border-gray-300 rounded p-1 w-20 text-center"> <span class="ml-2">Menit</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-5 border-t border-gray-100 flex justify-end gap-3 shrink-0 bg-gray-50">
                        <button @click="showExtraTimeModal = false" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition-colors">Batal</button>
                        <button @click="addExtraTime" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg font-bold shadow hover:bg-blue-700 transition-colors">Simpan Waktu</button>
                    </div>
                </div>
            </div>

            <!-- Peserta Modal Component -->
            <PesertaModal ref="pu" :jadwal-id="id" />
            
            <!-- Koreksi Jawaban Component -->
            <KoreksiModal ref="jawabanCorrection" @changed="reload" />
            
        </div>
    </DashboardLayout>
</template>
