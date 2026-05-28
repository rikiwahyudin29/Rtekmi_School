<?php

namespace App\Http\Controllers\Admin\CBT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\DraftUjian;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\SoalData;
use Illuminate\Support\Facades\DB;

class DraftUjianController extends Controller
{
    public function index(Request $request)
    {
        $query = DraftUjian::with(['bankSoal', 'mapel']);

        $userRoles = session('roles', []);
        $isAdmin = in_array('admin', $userRoles) || in_array('superadmin', $userRoles) || in_array('kurikulum', $userRoles) || in_array('kepsek', $userRoles);
        
        if (!$isAdmin && in_array('guru', $userRoles)) {
            $query->whereHas('bankSoal', function($q) {
                $q->where('user_id', auth()->id());
            });
        }

        if ($request->has('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $drafts = $query->orderBy('id', 'desc')->paginate(10);

        foreach ($drafts as $draft) {
            $draft->count_pg = SoalData::where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 1)->count();
            $draft->count_pgmulti = SoalData::where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 2)->count();
            $draft->count_menjodohkan = SoalData::where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 3)->count();
            $draft->count_isian = SoalData::where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 4)->count();
            $draft->count_esai = SoalData::where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 5)->count();
            $draft->count_bs = SoalData::where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 6)->count();
        }

        return Inertia::render('Admin/CBT/DraftUjian/Index', [
            'drafts' => $drafts,
            'filters' => $request->only(['search']),
            // Untuk Form Select
            'mapels' => Mapel::select('id', 'nama_mapel')->get(),
            'bank_soals' => BankSoal::when(!$isAdmin && in_array('guru', $userRoles), function($q) {
                return $q->where('user_id', auth()->id());
            })->select('id', 'kode', 'deskripsi')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'bank_soal_id' => 'required|exists:m_banksoal,id',
            'mapel_id' => 'required|exists:tbl_mapel,id',
            'acak_soal' => 'boolean',
            'acak_opsi' => 'boolean',
            'bobot_tf' => 'numeric',
            'bobot_esai' => 'numeric',
            'timeout' => 'integer',
            'minFinishTime' => 'integer',
            'visible_pg' => 'integer|min:0',
            'visible_esai' => 'integer|min:0',
            'visible_pgmulti' => 'integer|min:0',
            'visible_pgtf' => 'integer|min:0',
            'visible_pgcouple' => 'integer|min:0',
            'visible_shortentry' => 'integer|min:0',
        ]);

        $validated['acak_soal'] = $request->boolean('acak_soal');
        $validated['acak_opsi'] = $request->boolean('acak_opsi');

        DraftUjian::create($validated);

        return redirect()->back()->with('success', 'Draft Ujian berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'bank_soal_id' => 'required|exists:m_banksoal,id',
            'mapel_id' => 'required|exists:tbl_mapel,id',
            'acak_soal' => 'boolean',
            'acak_opsi' => 'boolean',
            'bobot_tf' => 'numeric',
            'bobot_esai' => 'numeric',
            'timeout' => 'integer',
            'minFinishTime' => 'integer',
            'visible_pg' => 'integer|min:0',
            'visible_esai' => 'integer|min:0',
            'visible_pgmulti' => 'integer|min:0',
            'visible_pgtf' => 'integer|min:0',
            'visible_pgcouple' => 'integer|min:0',
            'visible_shortentry' => 'integer|min:0',
        ]);

        $validated['acak_soal'] = $request->boolean('acak_soal');
        $validated['acak_opsi'] = $request->boolean('acak_opsi');

        $draft = DraftUjian::findOrFail($id);
        $draft->update($validated);

        return redirect()->back()->with('success', 'Draft Ujian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $draft = DraftUjian::findOrFail($id);
        
        // TODO: Check if draft is used in Jadwal Ujian
        // $isUsed = JadwalUjian::where('draft_id', $id)->exists();
        // if($isUsed) return back()->with('error', 'Draft sudah digunakan pada Jadwal Ujian');

        $draft->soals()->detach();
        $draft->delete();

        return redirect()->back()->with('success', 'Draft Ujian berhasil dihapus.');
    }

    // -- ATUR SOAL --
    public function aturSoal($id)
    {
        $draft = DraftUjian::with(['bankSoal'])->findOrFail($id);
        
        // Ambil semua soal dari bank soal ini
        $soals = SoalData::where('bank_id', $draft->bank_soal_id)
            ->with(['opsi', 'couples'])
            ->get();
            
        // Tandai yang sudah terpilih (ada di pivot)
        $selectedIds = $draft->soals()->pluck('soal_data.id')->toArray();
        
        $soals->transform(function($item) use ($selectedIds) {
            $item->selected = in_array($item->id, $selectedIds);
            return $item;
        });

        // Urutkan sesuai order_soal jika ada
        $orderedSoals = collect();
        if ($draft->order_soal) {
            $orderArr = explode(',', $draft->order_soal);
            foreach ($orderArr as $oId) {
                $found = $soals->firstWhere('id', $oId);
                if ($found) {
                    $orderedSoals->push($found);
                    $soals = $soals->reject(fn($s) => $s->id == $oId); // Hapus dari daftar sisa
                }
            }
        }
        
        // Gabungkan: yang terurut di atas, sisanya di bawah
        $finalSoals = $orderedSoals->merge($soals)->values();

        return Inertia::render('Admin/CBT/DraftUjian/AturSoal', [
            'draft' => $draft,
            'soals' => $finalSoals
        ]);
    }

    public function saveSoal(Request $request, $id)
    {
        $draft = DraftUjian::findOrFail($id);
        $soalIds = $request->soal_ids ?? [];
        $orderSoal = implode(',', $soalIds);

        DB::transaction(function() use ($draft, $soalIds, $orderSoal) {
            $draft->soals()->sync($soalIds);
            $draft->update(['order_soal' => $orderSoal]);
        });

        return redirect()->route('admin.cbt.draft-ujian.index')->with('success', 'Susunan Soal berhasil disimpan.');
    }

    public function cetak($id)
    {
        $draft = DraftUjian::with(['bankSoal', 'mapel'])->findOrFail($id);
        
        $selectedIds = $draft->soals()->pluck('soal_data.id')->toArray();
        $soals = SoalData::whereIn('id', $selectedIds)->with(['opsi', 'couples'])->get();

        $orderedSoals = collect();
        if ($draft->order_soal) {
            $orderArr = explode(',', $draft->order_soal);
            foreach ($orderArr as $oId) {
                $found = $soals->firstWhere('id', $oId);
                if ($found) {
                    $orderedSoals->push($found);
                    $soals = $soals->reject(fn($s) => $s->id == $oId);
                }
            }
        }
        
        $finalSoals = $orderedSoals->merge($soals)->values();
        $sekolah = \App\Models\Sekolah::first();

        return Inertia::render('Admin/CBT/DraftUjian/Cetak', [
            'draft' => $draft,
            'soals' => $finalSoals,
            'sekolah' => $sekolah,
            'baseUrl' => url('/')
        ]);
    }
}
