<?php

namespace App\Http\Controllers;

use App\Models\Bmn;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BmnController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $bmns = Bmn::when($search, function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%$search%")
                  ->orWhere('nup', 'like', "%$search%")
                  ->orWhere('nama_barang', 'like', "%$search%");
            })
            ->orderBy('kode_barang')
            ->orderBy('nup')
            ->paginate(10)
            ->withQueryString();

        return view('bmn.index', compact('bmns', 'search'));
    }

    public function create()
    {
        return view('bmn.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_barang'  => ['required','string','max:100'],
            'nup'          => ['required','string','max:100'],
            'nama_barang'  => ['required','string','max:255'],
            'merek_barang' => ['nullable','string','max:255'],
        ]);

        // Enforce uniqueness pair manually to give friendly message
        $exists = Bmn::where('kode_barang', $data['kode_barang'])
            ->where('nup', $data['nup'])
            ->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['nup' => 'Kombinasi Kode Barang + NUP sudah ada.']);
        }

        Bmn::create($data);
        return redirect()->route('bmn.index')->with('success', 'BMN berhasil ditambahkan.');
    }

    public function edit(Bmn $bmn)
    {
        return view('bmn.edit', compact('bmn'));
    }

    public function update(Request $request, Bmn $bmn)
    {
        $data = $request->validate([
            'kode_barang'  => ['required','string','max:100'],
            'nup'          => ['required','string','max:100'],
            'nama_barang'  => ['required','string','max:255'],
            'merek_barang' => ['nullable','string','max:255'],
        ]);

        $exists = Bmn::where('kode_barang', $data['kode_barang'])
            ->where('nup', $data['nup'])
            ->where('id', '!=', $bmn->id)
            ->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['nup' => 'Kombinasi Kode Barang + NUP sudah ada.']);
        }

        $bmn->update($data);
        return redirect()->route('bmn.index')->with('success', 'BMN berhasil diperbarui.');
    }

    public function destroy(Bmn $bmn)
    {
        $bmn->delete();
        return redirect()->route('bmn.index')->with('success', 'BMN berhasil dihapus.');
    }

    public function showImport()
    {
        return view('bmn.import');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="bmn_template.xlsx"',
        ];
        return response()->stream(function () {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([
                ['kode_barang','nup','nama_barang','merek_barang'],
                ['01.02.03.04','0001','Laptop','Lenovo ThinkPad'],
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
        $expected = ['kode_barang','nup','nama_barang','merek_barang'];
        if (!$header || array_map('strtolower', $header) !== $expected) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header CSV tidak sesuai. Harus: '.implode(',', $expected)]);
        }

        $rowNum = 1; $ok = 0; $fail = 0; $errors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
            [$kode, $nup, $nama, $merek] = array_map('trim', $row + [null,null,null,null]);
            if (!$kode || !$nup || !$nama) { $fail++; $errors[] = "Baris $rowNum: kolom wajib kosong"; continue; }
            try {
                $bmn = Bmn::firstOrNew(['kode_barang' => $kode, 'nup' => $nup]);
                $bmn->nama_barang = $nama;
                $bmn->merek_barang = $merek ?: null;
                $bmn->save();
                $ok++;
            } catch (\Throwable $e) {
                $fail++; $errors[] = "Baris $rowNum: ".$e->getMessage();
            }
        }
        fclose($handle);

        return redirect()->route('bmn.index')->with('success', "Import selesai: $ok berhasil, $fail gagal")->with('import_errors', $errors);
    }
}
