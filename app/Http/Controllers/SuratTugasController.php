<?php

namespace App\Http\Controllers;

use App\Models\Mak;
use App\Models\Pegawai;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuratTugasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $items = SuratTugas::withCount(['pegawai','maks'])
            ->when($search, function ($q) use ($search) {
                $q->where('nomor_st','like',"%$search%")
                  ->orWhere('lokasi_tugas','like',"%$search%");
            })
            ->orderByDesc('tanggal_st')
            ->paginate(10)
            ->withQueryString();
        return view('surat_tugas.index', compact('items','search'));
    }

    public function create()
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        $maks = Mak::orderBy('kode')->get();
        return view('surat_tugas.create', compact('pegawais','maks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_st' => ['required','string','max:150','unique:surat_tugas,nomor_st'],
            'tanggal_st' => ['required','date'],
            'tanggal_range' => ['required','string'],
            'lokasi_tugas' => ['required','string','max:255'],
            'deskripsi_tugas' => ['nullable','string'],
            'pegawai_ids' => ['array'],
            'pegawai_ids.*' => ['integer','exists:pegawais,id'],
            'mak_ids' => ['array'],
            'mak_ids.*' => ['integer','exists:maks,id'],
        ]);

        [$mulai, $selesai] = $this->parseRange($data['tanggal_range']);
        if (!$mulai || !$selesai) {
            return back()->withInput()->withErrors(['tanggal_range' => 'Rentang tanggal tidak valid.']);
        }

        $st = SuratTugas::create([
            'nomor_st' => $data['nomor_st'],
            'tanggal_st' => $data['tanggal_st'],
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
            'lokasi_tugas' => $data['lokasi_tugas'],
            'deskripsi_tugas' => $data['deskripsi_tugas'] ?? null,
        ]);

        if (!empty($data['pegawai_ids'])) $st->pegawai()->sync($data['pegawai_ids']);
        if (!empty($data['mak_ids'])) $st->maks()->sync($data['mak_ids']);
        return redirect()->route('surat-tugas.index')->with('success','Surat Tugas berhasil ditambahkan.');
    }

    public function edit(SuratTugas $surat_tuga)
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        $maks = Mak::orderBy('kode')->get();
        $item = $surat_tuga;
        return view('surat_tugas.edit', compact('pegawais','maks','item'));
    }

    public function update(Request $request, SuratTugas $surat_tuga)
    {
        $data = $request->validate([
            'nomor_st' => ['required','string','max:150','unique:surat_tugas,nomor_st,'.$surat_tuga->id],
            'tanggal_st' => ['required','date'],
            'tanggal_range' => ['required','string'],
            'lokasi_tugas' => ['required','string','max:255'],
            'deskripsi_tugas' => ['nullable','string'],
            'pegawai_ids' => ['array'],
            'pegawai_ids.*' => ['integer','exists:pegawais,id'],
            'mak_ids' => ['array'],
            'mak_ids.*' => ['integer','exists:maks,id'],
        ]);
        [$mulai, $selesai] = $this->parseRange($data['tanggal_range']);
        if (!$mulai || !$selesai) {
            return back()->withInput()->withErrors(['tanggal_range' => 'Rentang tanggal tidak valid.']);
        }
        $surat_tuga->update([
            'nomor_st' => $data['nomor_st'],
            'tanggal_st' => $data['tanggal_st'],
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
            'lokasi_tugas' => $data['lokasi_tugas'],
            'deskripsi_tugas' => $data['deskripsi_tugas'] ?? null,
        ]);
        $surat_tuga->pegawai()->sync($data['pegawai_ids'] ?? []);
        $surat_tuga->maks()->sync($data['mak_ids'] ?? []);
        return redirect()->route('surat-tugas.index')->with('success','Surat Tugas berhasil diperbarui.');
    }

    public function destroy(SuratTugas $surat_tuga)
    {
        $surat_tuga->delete();
        return redirect()->route('surat-tugas.index')->with('success','Surat Tugas berhasil dihapus.');
    }

    private function parseRange(string $value): array
    {
        $parts = array_map('trim', explode(' to ', $value));
        if (count($parts) !== 2) return [null, null];
        return [$parts[0] ?: null, $parts[1] ?: null];
    }

    public function showImport()
    {
        return view('surat_tugas.import');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="surat_tugas_template.csv"',
        ];
        $columns = ['nomor_st','tanggal_st','tanggal_mulai','tanggal_selesai','lokasi_tugas','deskripsi_tugas(optional)','maks(kode;pisahkan;dengan;titikKoma)','pegawai_nips(daftar_nip;pisahkan;dengan;titikKoma)'];
        return response()->stream(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            fputcsv($out, ['ST-001/2025','2025-08-05','2025-08-10','2025-08-12','Surabaya','Rapat koordinasi','524119;524111','1987654321010001;1976543210980002']);
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
        $expected = ['nomor_st','tanggal_st','tanggal_mulai','tanggal_selesai','lokasi_tugas','deskripsi_tugas(optional)','maks(kode;pisahkan;dengan;titikKoma)','pegawai_nips(daftar_nip;pisahkan;dengan;titikKoma)'];
        if (!$header || array_map('strtolower', $header) !== $expected) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header CSV tidak sesuai. Harus: '.implode(',', $expected)]);
        }

        $rowNum = 1; $ok = 0; $fail = 0; $errors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
            [$nomor, $tglSt, $tglMulai, $tglSelesai, $lokasi, $desk, $maksCodes, $nipList] = array_map('trim', $row + [null,null,null,null,null,null,null,null]);
            if (!$nomor || !$tglSt || !$tglMulai || !$tglSelesai || !$lokasi) { $fail++; $errors[] = "Baris $rowNum: kolom wajib kosong"; continue; }
            try {
                $st = SuratTugas::firstOrNew(['nomor_st' => $nomor]);
                $st->tanggal_st = $tglSt;
                $st->tanggal_mulai = $tglMulai;
                $st->tanggal_selesai = $tglSelesai;
                $st->lokasi_tugas = $lokasi;
                $st->deskripsi_tugas = $desk ?: null;
                $st->save();

                if ($maksCodes) {
                    $ids = [];
                    foreach (explode(';', $maksCodes) as $code) {
                        $code = trim($code);
                        if ($code === '') continue;
                        $mak = Mak::firstOrCreate(['kode' => $code], ['uraian' => null]);
                        $ids[] = $mak->id;
                    }
                    if ($ids) $st->maks()->syncWithoutDetaching($ids);
                }

                if ($nipList) {
                    $nips = array_filter(array_map('trim', explode(';', $nipList)));
                    $pegawaiIds = Pegawai::whereIn('nip', $nips)->pluck('id', 'nip');
                    $missing = array_diff($nips, $pegawaiIds->keys()->all());
                    if (!empty($missing)) {
                        $errors[] = "Baris $rowNum: NIP tidak ditemukan: ".implode(';', $missing);
                    }
                    if ($pegawaiIds->isNotEmpty()) $st->pegawai()->syncWithoutDetaching($pegawaiIds->values()->all());
                }

                $ok++;
            } catch (\Throwable $e) {
                $fail++; $errors[] = "Baris $rowNum: ".$e->getMessage();
            }
        }
        fclose($handle);

        return redirect()->route('surat-tugas.index')->with('success', "Import selesai: $ok berhasil, $fail gagal")->with('import_errors', $errors);
    }
}
