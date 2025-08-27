<?php

namespace App\Http\Controllers;

use App\Models\Bmn;
use App\Models\Pegawai;
use App\Models\PeminjamanBmn;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PeminjamanBmnController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $items = PeminjamanBmn::with(['bmn','pegawai'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('bmn', function ($b) use ($search) {
                    $b->where('kode_barang','like',"%$search%")
                      ->orWhere('nup','like',"%$search%")
                      ->orWhere('nama_barang','like',"%$search%");
                })->orWhereHas('pegawai', function ($p) use ($search) {
                    $p->where('nip','like',"%$search%")
                      ->orWhere('nama','like',"%$search%");
                });
            })
            ->orderByDesc('tanggal_pinjam')
            ->paginate(10)
            ->withQueryString();
        return view('peminjaman_bmn.index', compact('items','search'));
    }

    public function create()
    {
        $bmns = Bmn::orderBy('kode_barang')->orderBy('nup')->get();
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('peminjaman_bmn.create', compact('bmns','pegawais'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bmn_id' => ['required','exists:bmns,id'],
            'pegawai_id' => ['nullable','exists:pegawais,id'],
            'tanggal_range' => ['required','string'],
            'keperluan' => ['nullable','string'],
            'lokasi_tujuan' => ['required','string','max:100'],
            'keterangan' => ['nullable','string'],
        ]);
        [$mulai,$sampai] = array_map('trim', explode(' to ', $data['tanggal_range']) + [null,null]);
        if (!$mulai || !$sampai) return back()->withInput()->withErrors(['tanggal_range'=>'Rentang tanggal tidak valid.']);
        PeminjamanBmn::create([
            'bmn_id' => $data['bmn_id'],
            'pegawai_id' => $data['pegawai_id'] ?? null,
            'tanggal_pinjam' => $mulai,
            'tanggal_kembali' => $sampai,
            'status' => 'pending',
            'keperluan' => $data['keperluan'] ?? null,
            'lokasi_tujuan' => $data['lokasi_tujuan'],
            'keterangan' => $data['keterangan'] ?? null,
        ]);
        return redirect()->route('peminjaman-bmn.index')->with('success','Peminjaman berhasil ditambahkan.');
    }

    public function edit(PeminjamanBmn $peminjaman_bmn)
    {
        $bmns = Bmn::orderBy('kode_barang')->orderBy('nup')->get();
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('peminjaman_bmn.edit', ['item' => $peminjaman_bmn, 'bmns' => $bmns, 'pegawais' => $pegawais]);
    }

    public function update(Request $request, PeminjamanBmn $peminjaman_bmn)
    {
        $data = $request->validate([
            'bmn_id' => ['required','exists:bmns,id'],
            'pegawai_id' => ['nullable','exists:pegawais,id'],
            'tanggal_range' => ['required','string'],
            'keperluan' => ['nullable','string'],
            'lokasi_tujuan' => ['required','string','max:100'],
            'keterangan' => ['nullable','string'],
        ]);
        [$mulai,$sampai] = array_map('trim', explode(' to ', $data['tanggal_range']) + [null,null]);
        if (!$mulai || !$sampai) return back()->withInput()->withErrors(['tanggal_range'=>'Rentang tanggal tidak valid.']);
        $peminjaman_bmn->update([
            'bmn_id' => $data['bmn_id'],
            'pegawai_id' => $data['pegawai_id'] ?? null,
            'tanggal_pinjam' => $mulai,
            'tanggal_kembali' => $sampai,
            'keperluan' => $data['keperluan'] ?? null,
            'lokasi_tujuan' => $data['lokasi_tujuan'],
            'keterangan' => $data['keterangan'] ?? null,
        ]);
        return redirect()->route('peminjaman-bmn.index')->with('success','Peminjaman berhasil diperbarui.');
    }

    public function approve(PeminjamanBmn $item)
    {
        if ($item->status !== 'pending') return back()->with('success','Status tidak diubah (bukan pending).');
        $item->update(['status' => 'dipinjam']);
        return back()->with('success','Peminjaman disetujui.');
    }

    public function markReturn(PeminjamanBmn $item)
    {
        if ($item->status === 'dikembalikan') return back()->with('success','Sudah dikembalikan.');
        if ($item->status === 'pending') return back()->with('success','Belum disetujui.');
        $item->update(['status' => 'dikembalikan']);
        return back()->with('success','Barang ditandai dikembalikan.');
    }

    public function destroy(PeminjamanBmn $peminjaman_bmn)
    {
        $peminjaman_bmn->delete();
        return redirect()->route('peminjaman-bmn.index')->with('success','Peminjaman berhasil dihapus.');
    }

    public function showImport()
    {
        return view('peminjaman_bmn.import');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="peminjaman_bmn_template.csv"',
        ];
        $columns = ['kode_barang','nup','nip(optional)','tanggal_pinjam','tanggal_kembali(optional)','status(optional)','keperluan(optional)','keterangan(optional)'];
        return response()->stream(function () use ($columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            fputcsv($out, ['01.02.03.04','0001','1987654321010001','2025-08-10','','dipinjam','Rapat','-']);
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
        $expected = ['kode_barang','nup','nip(optional)','tanggal_pinjam','tanggal_kembali(optional)','status(optional)','keperluan(optional)','keterangan(optional)'];
        if (!$header || array_map('strtolower', $header) !== $expected) {
            fclose($handle);
            return back()->withErrors(['file' => 'Header CSV tidak sesuai. Harus: '.implode(',', $expected)]);
        }

        $rowNum = 1; $ok = 0; $fail = 0; $errors = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;
            [$kode, $nup, $nip, $tglPinjam, $tglKembali, $status, $keperluan, $keterangan] = array_map('trim', $row + [null,null,null,null,null,null,null,null]);
            if (!$kode || !$nup || !$tglPinjam) { $fail++; $errors[] = "Baris $rowNum: kolom wajib kosong"; continue; }
            $bmn = Bmn::where('kode_barang',$kode)->where('nup',$nup)->first();
            if (!$bmn) { $fail++; $errors[] = "Baris $rowNum: BMN ($kode/$nup) tidak ditemukan"; continue; }
            $pegawaiId = null;
            if ($nip) {
                $pegawaiId = optional(Pegawai::where('nip',$nip)->first())->id;
                if (!$pegawaiId) { $errors[] = "Baris $rowNum: Pegawai NIP $nip tidak ditemukan (diabaikan)"; }
            }
            try {
                PeminjamanBmn::create([
                    'bmn_id' => $bmn->id,
                    'pegawai_id' => $pegawaiId,
                    'tanggal_pinjam' => $tglPinjam,
                    'tanggal_kembali' => $tglKembali ?: null,
                    'status' => $status ?: 'pending',
                    'keperluan' => $keperluan ?: null,
                    'keterangan' => $keterangan ?: null,
                ]);
                $ok++;
            } catch (\Throwable $e) {
                $fail++; $errors[] = "Baris $rowNum: ".$e->getMessage();
            }
        }
        fclose($handle);

        return redirect()->route('peminjaman-bmn.index')->with('success', "Import selesai: $ok berhasil, $fail gagal")->with('import_errors', $errors);
    }

    public function laporan(Request $request)
    {
        $range = $request->string('range')->toString();
        $status = $request->string('status')->toString();
        $mulai = $selesai = null;
        if ($range) {
            [$mulai,$selesai] = array_map('trim', explode(' to ', $range) + [null,null]);
        }
        $query = PeminjamanBmn::with(['bmn','pegawai']);
        if ($mulai && $selesai) {
            $query->whereBetween('tanggal_pinjam', [$mulai, $selesai]);
        }
        if ($status) {
            $query->where('status', $status);
        }
        $items = $query->orderByDesc('tanggal_pinjam')->paginate(20)->withQueryString();
        return view('bmn.laporan', compact('items','range','status'));
    }
}
