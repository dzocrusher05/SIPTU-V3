<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $pegawais = Pegawai::when($search, function ($q) use ($search) {
                $q->where('nip', 'like', "%$search%")
                  ->orWhere('nama', 'like', "%$search%")
                  ->orWhere('jabatan', 'like', "%$search%");
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('pegawai.index', compact('pegawais', 'search'));
    }

    public function create()
    {
        return view('pegawai.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nip' => ['required','string','max:50','unique:pegawais,nip'],
            'nama' => ['required','string','max:255'],
            'pangkat_gol' => ['required','string','max:100'],
            'jabatan' => ['required','string','max:150'],
            'tanggal_kgb_terakhir' => ['nullable','date'],
            'jumlah_tahun_kgb' => ['nullable','integer','min:1','max:10'],
        ]);

        Pegawai::create($data);
        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(Pegawai $pegawai)
    {
        return view('pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $data = $request->validate([
            'nip' => ['required','string','max:50','unique:pegawais,nip,'.$pegawai->id],
            'nama' => ['required','string','max:255'],
            'pangkat_gol' => ['required','string','max:100'],
            'jabatan' => ['required','string','max:150'],
            'tanggal_kgb_terakhir' => ['nullable','date'],
            'jumlah_tahun_kgb' => ['nullable','integer','min:1','max:10'],
        ]);

        $pegawai->update($data);
        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();
        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }

    public function showImport()
    {
        return view('pegawai.import');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="pegawai_template.xlsx"',
        ];
        return response()->stream(function () {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([
                ['nip','nama','pangkat_gol','jabatan','tanggal_kgb_terakhir','jumlah_tahun_kgb'],
                ['1987654321010001','Budi Santoso','III/b','Analis TU','2024-01-15',''],
            ]);
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $headers);
    }

    public function handleImport(Request $request)
    {
        $request->validate(['file' => ['required','file','mimetypes:text/plain,text/csv,text/tsv,application/vnd.ms-excel','max:5120']]);
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) return back()->withErrors(['file' => 'Tidak dapat membaca file.']);

        $header = fgetcsv($handle);
        $expected = ['nip','nama','pangkat_gol','jabatan','tanggal_kgb_terakhir','jumlah_tahun_kgb'];
        if (!$header || array_map('strtolower', $header) !== $expected) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header CSV tidak sesuai. Harus: '.implode(',', $expected)]);
        }

        $rowNum = 1; $ok = 0; $fail = 0; $errors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
            [$nip, $nama, $pangkat, $jabatan, $tgl, $tahun] = array_map('trim', $row + [null,null,null,null,null,null]);
            if (!$nip || !$nama || !$pangkat || !$jabatan) { $fail++; $errors[] = "Baris $rowNum: kolom wajib kosong"; continue; }
            try {
                $pegawai = Pegawai::firstOrNew(['nip' => $nip]);
                $pegawai->nama = $nama;
                $pegawai->pangkat_gol = $pangkat;
                $pegawai->jabatan = $jabatan;
                $pegawai->tanggal_kgb_terakhir = $tgl ?: null;
                if (trim((string)$tahun) === '') {
                    $pegawai->jumlah_tahun_kgb = null;
                } else {
                    $val = (int)$tahun;
                    $pegawai->jumlah_tahun_kgb = ($val >= 1 && $val <= 10) ? $val : null;
                }
                $pegawai->save();
                $ok++;
            } catch (\Throwable $e) {
                $fail++; $errors[] = "Baris $rowNum: ".$e->getMessage();
            }
        }
        fclose($handle);

        return redirect()->route('pegawai.index')->with('success', "Import selesai: $ok berhasil, $fail gagal")->with('import_errors', $errors);
    }
}
