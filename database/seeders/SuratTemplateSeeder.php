<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuratTemplate;

class SuratTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            [
                'nama_template' => 'Surat Keterangan Aktif Belajar',
                'format_nomor' => '421/02{NO}/SMK-RJ/{BLN}/{THN}',
                'isi_html' => '<p style="text-align: justify;">Yang bertanda tangan di bawah ini Kepala Sekolah menerangkan bahwa:</p>
<table border="0" cellpadding="5" cellspacing="0" style="margin-left: 20px; width: 100%;">
    <tbody>
        <tr>
            <td width="200">Nama Lengkap</td>
            <td width="10">:</td>
            <td><strong>{NAMA}</strong></td>
        </tr>
        <tr>
            <td>Nomor Induk Siswa (NIS)</td>
            <td>:</td>
            <td>{NIS}</td>
        </tr>
        <tr>
            <td>Tingkat / Kelas</td>
            <td>:</td>
            <td>{KELAS}</td>
        </tr>
        <tr>
            <td>Alamat Lengkap</td>
            <td>:</td>
            <td>{ALAMAT}</td>
        </tr>
    </tbody>
</table>
<p style="text-align: justify; margin-top: 20px;">Adalah benar yang bersangkutan merupakan siswa/siswi yang saat ini sedang aktif belajar di sekolah kami pada Tahun Ajaran {TAHUN_AJARAN}.</p>
<p style="text-align: justify;">Demikian Surat Keterangan Aktif Belajar ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya oleh yang bersangkutan.</p>',
            ],
            [
                'nama_template' => 'Surat Panggilan Orang Tua (SP 1)',
                'format_nomor' => '422/05{NO}/SMK-RJ/{BLN}/{THN}',
                'isi_html' => '<p style="text-align: justify;">Dengan hormat,</p>
<p style="text-align: justify;">Sehubungan dengan adanya evaluasi kedisiplinan dan kehadiran siswa di sekolah, kami mengharap kehadiran Bapak/Ibu/Wali murid dari siswa/siswi:</p>
<table border="0" cellpadding="5" cellspacing="0" style="margin-left: 20px; width: 100%;">
    <tbody>
        <tr>
            <td width="200">Nama Lengkap</td>
            <td width="10">:</td>
            <td><strong>{NAMA}</strong></td>
        </tr>
        <tr>
            <td>Tingkat / Kelas</td>
            <td>:</td>
            <td>{KELAS}</td>
        </tr>
    </tbody>
</table>
<p style="text-align: justify; margin-top: 20px;">Untuk hadir memenuhi panggilan sekolah pada:</p>
<table border="0" cellpadding="5" cellspacing="0" style="margin-left: 20px; width: 100%;">
    <tbody>
        <tr>
            <td width="200">Hari, Tanggal</td>
            <td width="10">:</td>
            <td>.................................................................</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>08.00 WIB - Selesai</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>Ruang Bimbingan Konseling (BK)</td>
        </tr>
        <tr>
            <td>Keperluan</td>
            <td>:</td>
            <td>Membicarakan pembinaan kedisiplinan siswa</td>
        </tr>
    </tbody>
</table>
<p style="text-align: justify; margin-top: 20px;">Mengingat sangat pentingnya pertemuan ini demi masa depan dan kemajuan anak didik kita, kami sangat memohon kehadiran Bapak/Ibu secara langsung (tidak diwakilkan) dan tepat pada waktunya.</p>
<p style="text-align: justify;">Atas perhatian dan kerjasamanya yang baik, kami ucapkan terima kasih.</p>',
            ],
            [
                'nama_template' => 'Surat Keterangan Kelakuan Baik (SKKB)',
                'format_nomor' => '421/09{NO}/SMK-RJ/{BLN}/{THN}',
                'isi_html' => '<p style="text-align: justify;">Kepala Sekolah dengan ini menerangkan dengan sesungguhnya bahwa:</p>
<table border="0" cellpadding="5" cellspacing="0" style="margin-left: 20px; width: 100%;">
    <tbody>
        <tr>
            <td width="200">Nama Lengkap</td>
            <td width="10">:</td>
            <td><strong>{NAMA}</strong></td>
        </tr>
        <tr>
            <td>Nomor Induk Siswa (NIS)</td>
            <td>:</td>
            <td>{NIS}</td>
        </tr>
        <tr>
            <td>Kelas Terakhir</td>
            <td>:</td>
            <td>{KELAS}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>{ALAMAT}</td>
        </tr>
    </tbody>
</table>
<p style="text-align: justify; margin-top: 20px;">Berdasarkan pengamatan dan catatan pada Buku Induk Sekolah, bahwa siswa tersebut di atas selama menjadi siswa di sekolah kami senantiasa <strong>Berkelakuan Baik</strong>, tidak pernah terlibat penggunaan obat-obat terlarang (Narkoba) serta tidak pernah tersangkut tindak kriminal/kriminalitas maupun kenakalan remaja yang melanggar hukum.</p>
<p style="text-align: justify;">Demikian Surat Keterangan Kelakuan Baik ini dibuat untuk dapat dipergunakan guna melengkapi persyaratan administrasi (Melanjutkan Pendidikan / Melamar Pekerjaan).</p>',
            ]
        ];

        foreach ($templates as $template) {
            SuratTemplate::updateOrCreate(
                ['nama_template' => $template['nama_template']],
                $template
            );
        }
    }
}
