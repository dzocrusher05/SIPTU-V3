<?php

namespace App\Http\Controllers;

use App\Models\Mak;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MakController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $maks = Mak::when($search, fn($q) => $q->where('kode','like',"%$search%")->orWhere('uraian','like',"%$search%"))
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();
        return view('mak.index', compact('maks', 'search'));
    }

    public function showImport()
    {
        return view('mak.import');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="mak_template.xlsx"',
        ];
        return response()->stream(function () {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([
                ['kode','uraian'],
                ['524119','Belanja perjalanan dinas'],
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
        $expected = ['kode','uraian(optional)'];
        if (!$header || array_map('strtolower', $header) !== $expected) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header CSV tidak sesuai. Harus: '.implode(',', $expected)]);
        }

        $rowNum = 1; $ok = 0; $fail = 0; $errors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
            [$kode, $uraian] = array_map('trim', $row + [null,null]);
            if (!$kode) { $fail++; $errors[] = "Baris $rowNum: kode wajib"; continue; }
            try {
                Mak::updateOrCreate(['kode' => $kode], ['uraian' => $uraian ?: null]);
                $ok++;
            } catch (\Throwable $e) {
                $fail++; $errors[] = "Baris $rowNum: ".$e->getMessage();
            }
        }
        fclose($handle);

        return redirect()->route('mak.index')->with('success', "Import selesai: $ok berhasil, $fail gagal")->with('import_errors', $errors);
    }
}
