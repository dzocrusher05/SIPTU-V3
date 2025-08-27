<?php

namespace App\Http\Controllers;

use App\Models\KgbUpdate;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KgbUpdateController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $items = KgbUpdate::with('pegawai')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('pegawai', function ($p) use ($search) {
                    $p->where('nip','like',"%$search%")
                      ->orWhere('nama','like',"%$search%");
                });
            })
            ->orderByDesc('tanggal_kgb')
            ->paginate(10)
            ->withQueryString();
        return view('kgb_update.index', compact('items','search'));
    }

    public function create()
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('kgb_update.create', compact('pegawais'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pegawai_id' => ['required','exists:pegawais,id'],
            'tanggal_kgb' => ['required','date'],
            'jumlah_tahun' => ['required','integer','min:1','max:10'],
            'tanggal_kgb_berikutnya' => ['nullable','date','after_or_equal:tanggal_kgb'],
            'catatan' => ['nullable','string'],
        ]);
        $item = KgbUpdate::create($data);
        // sinkron ringkasan
        $pegawai = $item->pegawai; $pegawai->tanggal_kgb_terakhir = $data['tanggal_kgb']; $pegawai->jumlah_tahun_kgb = $data['jumlah_tahun']; $pegawai->save();
        return redirect()->route('kgb-updates.index')->with('success','Update KGB berhasil ditambahkan.');
    }

    public function edit(KgbUpdate $kgb_update)
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('kgb_update.edit', ['item'=>$kgb_update, 'pegawais'=>$pegawais]);
    }

    public function update(Request $request, KgbUpdate $kgb_update)
    {
        $data = $request->validate([
            'pegawai_id' => ['required','exists:pegawais,id'],
            'tanggal_kgb' => ['required','date'],
            'jumlah_tahun' => ['required','integer','min:1','max:10'],
            'tanggal_kgb_berikutnya' => ['nullable','date','after_or_equal:tanggal_kgb'],
            'catatan' => ['nullable','string'],
        ]);
        $kgb_update->update($data);
        $pegawai = $kgb_update->pegawai; $pegawai->tanggal_kgb_terakhir = $data['tanggal_kgb']; $pegawai->jumlah_tahun_kgb = $data['jumlah_tahun']; $pegawai->save();
        return redirect()->route('kgb-updates.index')->with('success','Update KGB berhasil diperbarui.');
    }

    public function destroy(KgbUpdate $kgb_update)
    {
        $kgb_update->delete();
        return redirect()->route('kgb-updates.index')->with('success','Update KGB berhasil dihapus.');
    }

    public function showImport()
    {
        return view('kgb_update.import');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kgb_update_template.csv"',
        ];
        $columns = ['nip','tanggal_kgb','jumlah_tahun','tanggal_kgb_berikutnya(optional)','catatan(optional)'];
        return response()->stream(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            fputcsv($out, ['1987654321010001','2025-01-01','2','2027-01-01','Naik berkala']);
            fclose($out);
        }, 200, $headers);
    }

    public function handleImport(Request $request)
    {
        $request->validate(['file' => ['required','file','mimetypes:text/plain,text/csv,text/tsv,application/vnd.ms-excel','max:5120']]);
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) return back()->withErrors(['file' => 'Tidak dapat membaca file.']);

        $header = fgetcsv($handle);
        $expected = ['nip','tanggal_kgb','jumlah_tahun','tanggal_kgb_berikutnya(optional)','catatan(optional)'];
        if (!$header || array_map('strtolower', $header) !== $expected) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header CSV tidak sesuai. Harus: '.implode(',', $expected)]);
        }

        $rowNum = 1; $ok = 0; $fail = 0; $errors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
            [$nip, $tgl, $tahun, $tglNext, $cat] = array_map('trim', $row + [null,null,null,null,null]);
            if (!$nip || !$tgl || !$tahun) { $fail++; $errors[] = "Baris $rowNum: kolom wajib kosong"; continue; }
            $pegawai = Pegawai::where('nip',$nip)->first();
            if (!$pegawai) { $fail++; $errors[] = "Baris $rowNum: Pegawai NIP $nip tidak ditemukan"; continue; }
            try {
                KgbUpdate::create([
                    'pegawai_id' => $pegawai->id,
                    'tanggal_kgb' => $tgl,
                    'jumlah_tahun' => (int)$tahun,
                    'tanggal_kgb_berikutnya' => $tglNext ?: null,
                    'catatan' => $cat ?: null,
                ]);
                $pegawai->tanggal_kgb_terakhir = $tgl;
                $pegawai->jumlah_tahun_kgb = (int)$tahun;
                $pegawai->save();
                $ok++;
            } catch (\Throwable $e) {
                $fail++; $errors[] = "Baris $rowNum: ".$e->getMessage();
            }
        }
        fclose($handle);

        return redirect()->route('kgb-updates.index')->with('success', "Import selesai: $ok berhasil, $fail gagal")->with('import_errors', $errors);
    }
}
