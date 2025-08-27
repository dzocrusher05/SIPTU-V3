<?php

namespace App\Http\Controllers;

use App\Models\Lpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LpjController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $lpjs = Lpj::when($search, function ($q) use ($search) {
                $q->where('nomor_lpj', 'like', "%$search%")
                  ->orWhere('kegiatan', 'like', "%$search%")
                  ->orWhere('status', 'like', "%$search%");
            })
            ->orderByDesc('tanggal_masuk')
            ->paginate(10)
            ->withQueryString();

        return view('lpj.index', compact('lpjs', 'search'));
    }

    public function create()
    {
        return view('lpj.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomor_lpj' => ['required','string','max:100'],
            'tanggal_masuk' => ['required','date'],
            'kegiatan' => ['nullable','string','max:255'],
            'nilai' => ['nullable','numeric','min:0'],
            'status' => ['required','string','max:50'],
            'file' => ['nullable','file','max:20480'],
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('lpj', 'public');
        }

        Lpj::create($data);
        return redirect()->route('lpj.index')->with('success', 'LPJ berhasil ditambahkan.');
    }

    public function edit(Lpj $lpj)
    {
        return view('lpj.edit', compact('lpj'));
    }

    public function update(Request $request, Lpj $lpj)
    {
        $data = $request->validate([
            'nomor_lpj' => ['required','string','max:100'],
            'tanggal_masuk' => ['required','date'],
            'kegiatan' => ['nullable','string','max:255'],
            'nilai' => ['nullable','numeric','min:0'],
            'status' => ['required','string','max:50'],
            'file' => ['nullable','file','max:20480'],
        ]);

        if ($request->hasFile('file')) {
            if ($lpj->file_path) {
                Storage::disk('public')->delete($lpj->file_path);
            }
            $data['file_path'] = $request->file('file')->store('lpj', 'public');
        }

        $lpj->update($data);
        return redirect()->route('lpj.index')->with('success', 'LPJ berhasil diperbarui.');
    }

    public function destroy(Lpj $lpj)
    {
        if ($lpj->file_path) {
            Storage::disk('public')->delete($lpj->file_path);
        }
        $lpj->delete();
        return redirect()->route('lpj.index')->with('success', 'LPJ berhasil dihapus.');
    }

    public function showImport()
    {
        return view('lpj.import');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="lpj_template.csv"',
        ];
        $columns = ['nomor_lpj','tanggal_masuk','kegiatan','nilai','status'];
        return response()->stream(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            fputcsv($out, ['LPJ-001/2025','2025-08-01','Perjalanan Dinas','1500000','baru']);
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
        $expected = ['nomor_lpj','tanggal_masuk','kegiatan','nilai','status'];
        if (!$header || array_map('strtolower', $header) !== $expected) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header CSV tidak sesuai. Harus: '.implode(',', $expected)]);
        }

        $rowNum = 1; $ok = 0; $fail = 0; $errors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
            [$nomor, $tgl, $keg, $nilai, $status] = array_map('trim', $row + [null,null,null,null,null]);
            if (!$nomor || !$tgl || !$status) { $fail++; $errors[] = "Baris $rowNum: kolom wajib kosong"; continue; }
            try {
                $lpj = Lpj::firstOrNew(['nomor_lpj' => $nomor]);
                $lpj->tanggal_masuk = $tgl;
                $lpj->kegiatan = $keg ?: null;
                $lpj->nilai = $nilai !== '' ? (float)$nilai : null;
                $lpj->status = $status;
                $lpj->save();
                $ok++;
            } catch (\Throwable $e) {
                $fail++; $errors[] = "Baris $rowNum: ".$e->getMessage();
            }
        }
        fclose($handle);

        return redirect()->route('lpj.index')->with('success', "Import selesai: $ok berhasil, $fail gagal")->with('import_errors', $errors);
    }
}
