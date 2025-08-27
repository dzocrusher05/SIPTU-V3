<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Bmn;
use App\Models\Pegawai;
use App\Models\PeminjamanBmn;
use App\Models\Lpj;
use App\Models\Mak;
use App\Models\ItAsset;
use App\Models\KgbUpdate;
use App\Models\SuratTugas;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('spa.dashboard') : redirect()->route('login');
});

// (Public forms removed by request)

Route::get('/dashboard', function () {
    return redirect()->route('spa.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // SPA-only UI: arahkan akses non-SPA ke halaman SPA
    Route::any('bmn/{any?}', fn() => redirect('spa/bmn'))->where('any','.*');
    Route::any('pegawai/{any?}', fn() => redirect('spa/pegawai'))->where('any','.*');
    Route::any('lpj/{any?}', fn() => redirect('spa/lpj'))->where('any','.*');
    Route::any('peminjaman-bmn/{any?}', fn() => redirect('spa/peminjaman-bmn'))->where('any','.*');
    Route::any('kgb-updates/{any?}', fn() => redirect('spa/kgb-updates'))->where('any','.*');
    Route::any('surat-tugas/{any?}', fn() => redirect('spa/surat-tugas'))->where('any','.*');
    Route::any('mak/{any?}', fn() => redirect('spa/mak'))->where('any','.*');
    Route::any('it-assets/{any?}', fn() => redirect('spa/it-assets'))->where('any','.*');

    // Preview new SPA dashboard (Inertia + Vue)
    Route::get('spa', function () {
        $today = \Carbon\Carbon::today();
        $in30 = \Carbon\Carbon::today()->addDays(30);

        $metrics = [
            'bmn' => Bmn::count(),
            'pegawai' => Pegawai::count(),
            'peminjaman_aktif' => PeminjamanBmn::whereNull('tanggal_kembali')->count(),
            'lpj_pending' => Lpj::whereNull('status')->orWhere('status', '!=', 'Selesai')->count(),
            'it_assets' => ItAsset::count(),
            'surat_tugas_aktif' => SuratTugas::whereDate('tanggal_mulai', '<=', $today)->whereDate('tanggal_selesai', '>=', $today)->count(),
            'kgb_due_30' => KgbUpdate::whereNotNull('tanggal_kgb_berikutnya')->whereBetween('tanggal_kgb_berikutnya', [$today, $in30])->count(),
        ];

        // Build 6-month timeseries for simple charts
        $labels = [];
        $peminjamanSeries = [];
        $lpjSeries = [];
        $stSeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = $today->copy()->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $labels[] = $start->isoFormat('MMM YY');
            $peminjamanSeries[] = PeminjamanBmn::whereBetween('tanggal_pinjam', [$start, $end])->count();
            $lpjSeries[] = Lpj::whereBetween('tanggal_masuk', [$start, $end])->count();
            $stSeries[] = SuratTugas::whereBetween('tanggal_st', [$start, $end])->count();
        }
        $timeseries = [
            'labels' => $labels,
            'datasets' => [
                [ 'name' => 'Peminjaman', 'color' => '#2563eb', 'data' => $peminjamanSeries ],
                [ 'name' => 'LPJ', 'color' => '#10b981', 'data' => $lpjSeries ],
                [ 'name' => 'Surat Tugas', 'color' => '#f59e0b', 'data' => $stSeries ],
            ],
        ];

        $shortcuts = [
            [ 'label' => 'Data Pegawai', 'href' => '/spa/pegawai' ],
            [ 'label' => 'Data BMN', 'href' => '/spa/bmn' ],
            [ 'label' => 'Peminjaman BMN', 'href' => '/spa/peminjaman-bmn' ],
            [ 'label' => 'LPJ', 'href' => '/spa/lpj' ],
            [ 'label' => 'MAK', 'href' => '/spa/mak' ],
            [ 'label' => 'IT Aset', 'href' => '/spa/it-assets' ],
            [ 'label' => 'Surat Tugas', 'href' => '/spa/surat-tugas' ],
        ];

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'shortcuts' => $shortcuts,
            'timeseries' => $timeseries,
        ]);
    })->name('spa.dashboard');

    // SPA: BMN index (Inertia)
    Route::get('spa/bmn', function() {
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $bmns = Bmn::when($search, function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%$search%")
                  ->orWhere('nup', 'like', "%$search%")
                  ->orWhere('nama_barang', 'like', "%$search%")
                  ->orWhere('merek_barang', 'like', "%$search%");
            })
            ->when(in_array($sort, ['kode_barang','nup','nama_barang','merek_barang']), function($q) use($sort,$dir){ $q->orderBy($sort, $dir); })
            ->when(!in_array($sort, ['kode_barang','nup','nama_barang','merek_barang']), function($q){ $q->orderBy('kode_barang')->orderBy('nup'); })
            ->paginate(10)
            ->appends(['q' => $search, 'sort' => $sort, 'dir' => $dir]);
        return Inertia::render('Bmn/Index', [
            'bmns' => $bmns,
            'search' => $search,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    })->name('spa.bmn.index');

    // SPA: Pegawai index
    Route::get('spa/pegawai', function() {
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $pegawais = Pegawai::when($search, function($q) use ($search){
                $q->where('nip','like',"%$search%")
                  ->orWhere('nama','like',"%$search%");
            })
            ->when(in_array($sort, ['nip','nama','pangkat_gol','jabatan','tanggal_kgb_terakhir']), function($q) use($sort,$dir){ $q->orderBy($sort, $dir); })
            ->when(!in_array($sort, ['nip','nama','pangkat_gol','jabatan','tanggal_kgb_terakhir']), function($q){ $q->orderBy('nama'); })
            ->paginate(10)
            ->appends(['q'=>$search,'sort'=>$sort,'dir'=>$dir]);
        return Inertia::render('Pegawai/Index', [ 'pegawais' => $pegawais, 'search' => $search, 'sort'=>$sort, 'dir'=>$dir ]);
    })->name('spa.pegawai.index');

    // SPA: Peminjaman BMN index
    Route::get('spa/peminjaman-bmn', function(){
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = PeminjamanBmn::with(['bmn','pegawai'])
            ->when($search, function($q) use ($search){
                $q->whereHas('bmn', function($b) use ($search){
                    $b->where('kode_barang','like',"%$search%")
                      ->orWhere('nup','like',"%$search%")
                      ->orWhere('nama_barang','like',"%$search%");
                })->orWhereHas('pegawai', function($p) use ($search){
                    $p->where('nama','like',"%$search%");
                });
            })
            ->when(in_array($sort, ['tanggal_pinjam','tanggal_kembali','status']), function($q) use($sort,$dir){ $q->orderBy($sort,$dir); })
            ->when(!in_array($sort, ['tanggal_pinjam','tanggal_kembali','status']), function($q){ $q->orderByDesc('tanggal_pinjam'); })
            ->paginate(10)
            ->through(function($row){
                return [
                    'id' => $row->id,
                    'bmn_id' => $row->bmn_id,
                    'pegawai_id' => $row->pegawai_id,
                    'bmn_label' => $row->bmn? $row->bmn->kode_barang.'-'.$row->bmn->nup.' '.$row->bmn->nama_barang : '-',
                    'pegawai_nama' => $row->pegawai->nama ?? '-',
                    'tanggal_pinjam' => $row->tanggal_pinjam,
                    'tanggal_kembali' => $row->tanggal_kembali,
                    'status' => $row->status,
                ];
            })
            ->appends(['q'=>$search,'sort'=>$sort,'dir'=>$dir]);
        $bmnsOptions = Bmn::orderBy('kode_barang')->orderBy('nup')->limit(200)->get()->map(fn($b) => [ 'label' => $b->kode_barang.'-'.$b->nup.' '.$b->nama_barang, 'value' => $b->id ]);
        $pegawaiOptions = Pegawai::orderBy('nama')->limit(200)->get()->map(fn($p) => [ 'label' => $p->nama, 'value' => $p->id ]);
        return Inertia::render('PeminjamanBmn/Index', [ 'items' => $items, 'search' => $search, 'sort'=>$sort, 'dir'=>$dir, 'bmnsOptions' => $bmnsOptions, 'pegawaiOptions' => $pegawaiOptions ]);
    })->name('spa.peminjaman-bmn.index');

    // SPA: Peminjaman BMN CRUD
    Route::post('spa/peminjaman-bmn', function(){
        $data = request()->validate([
            'bmn_id' => ['required','exists:bmns,id'],
            'pegawai_id' => ['required','exists:pegawais,id'],
            'tanggal_pinjam' => ['required','date'],
            'tanggal_kembali' => ['nullable','date'],
            'status' => ['required','string','max:50'],
            'keperluan' => ['nullable','string','max:255'],
            'keterangan' => ['nullable','string','max:255'],
        ]);
        PeminjamanBmn::create($data);
        return back()->with('success','Peminjaman berhasil ditambahkan.');
    })->name('spa.peminjaman-bmn.store');
    Route::put('spa/peminjaman-bmn/{item}', function(PeminjamanBmn $item){
        $data = request()->validate([
            'bmn_id' => ['required','exists:bmns,id'],
            'pegawai_id' => ['required','exists:pegawais,id'],
            'tanggal_pinjam' => ['required','date'],
            'tanggal_kembali' => ['nullable','date'],
            'status' => ['required','string','max:50'],
            'keperluan' => ['nullable','string','max:255'],
            'keterangan' => ['nullable','string','max:255'],
        ]);
        $item->update($data);
        return back()->with('success','Peminjaman berhasil diperbarui.');
    })->name('spa.peminjaman-bmn.update');
    Route::delete('spa/peminjaman-bmn/{item}', function(PeminjamanBmn $item){
        $item->delete();
        return back()->with('success','Peminjaman berhasil dihapus.');
    })->name('spa.peminjaman-bmn.destroy');

    // SPA: Pegawai CRUD
    Route::post('spa/pegawai', function(){
        $data = request()->validate([
            'nip' => ['required','string','max:50'],
            'nama' => ['required','string','max:255'],
            'pangkat_gol' => ['nullable','string','max:100'],
            'jabatan' => ['nullable','string','max:100'],
            'tanggal_kgb_terakhir' => ['nullable','date'],
            'jumlah_tahun_kgb' => ['nullable','numeric'],
        ]);
        Pegawai::create($data);
        return back()->with('success','Pegawai berhasil ditambahkan.');
    })->name('spa.pegawai.store');
    Route::put('spa/pegawai/{pegawai}', function(Pegawai $pegawai){
        $data = request()->validate([
            'nip' => ['required','string','max:50'],
            'nama' => ['required','string','max:255'],
            'pangkat_gol' => ['nullable','string','max:100'],
            'jabatan' => ['nullable','string','max:100'],
            'tanggal_kgb_terakhir' => ['nullable','date'],
            'jumlah_tahun_kgb' => ['nullable','numeric'],
        ]);
        $pegawai->update($data);
        return back()->with('success','Pegawai berhasil diperbarui.');
    })->name('spa.pegawai.update');
    Route::delete('spa/pegawai/{pegawai}', function(Pegawai $pegawai){
        $pegawai->delete();
        return back()->with('success','Pegawai berhasil dihapus.');
    })->name('spa.pegawai.destroy');

    // SPA: MAK CRUD
    Route::post('spa/mak', function(){
        $data = request()->validate([
            'kode' => ['required','string','max:50'],
            'uraian' => ['required','string','max:255'],
        ]);
        Mak::create($data);
        return back()->with('success','MAK berhasil ditambahkan.');
    })->name('spa.mak.store');
    Route::put('spa/mak/{mak}', function(Mak $mak){
        $data = request()->validate([
            'kode' => ['required','string','max:50'],
            'uraian' => ['required','string','max:255'],
        ]);
        $mak->update($data);
        return back()->with('success','MAK berhasil diperbarui.');
    })->name('spa.mak.update');
    Route::delete('spa/mak/{mak}', function(Mak $mak){
        $mak->delete();
        return back()->with('success','MAK berhasil dihapus.');
    })->name('spa.mak.destroy');

    // SPA: LPJ CRUD
    Route::post('spa/lpj', function(){
        $data = request()->validate([
            'nomor_lpj' => ['required','string','max:100'],
            'tanggal_masuk' => ['required','date'],
            'kegiatan' => ['required','string','max:255'],
            'nilai' => ['nullable','numeric'],
            'status' => ['nullable','string','max:50'],
        ]);
        Lpj::create($data);
        return back()->with('success','LPJ berhasil ditambahkan.');
    })->name('spa.lpj.store');
    Route::put('spa/lpj/{lpj}', function(Lpj $lpj){
        $data = request()->validate([
            'nomor_lpj' => ['required','string','max:100'],
            'tanggal_masuk' => ['required','date'],
            'kegiatan' => ['required','string','max:255'],
            'nilai' => ['nullable','numeric'],
            'status' => ['nullable','string','max:50'],
        ]);
        $lpj->update($data);
        return back()->with('success','LPJ berhasil diperbarui.');
    })->name('spa.lpj.update');
    Route::delete('spa/lpj/{lpj}', function(Lpj $lpj){
        $lpj->delete();
        return back()->with('success','LPJ berhasil dihapus.');
    })->name('spa.lpj.destroy');

    // SPA: IT Assets CRUD
    Route::post('spa/it-assets', function(){
        $data = request()->validate([
            'kode_aset' => ['required','string','max:100'],
            'nama_perangkat' => ['required','string','max:255'],
            'merek_model' => ['nullable','string','max:255'],
            'serial_number' => ['nullable','string','max:255'],
            'kondisi' => ['nullable','string','max:100'],
            'lokasi' => ['nullable','string','max:255'],
            'penanggung_jawab' => ['nullable','string','max:255'],
            'keterangan' => ['nullable','string','max:255'],
        ]);
        ItAsset::create($data);
        return back()->with('success','IT Aset berhasil ditambahkan.');
    })->name('spa.it-assets.store');
    Route::put('spa/it-assets/{itAsset}', function(ItAsset $itAsset){
        $data = request()->validate([
            'kode_aset' => ['required','string','max:100'],
            'nama_perangkat' => ['required','string','max:255'],
            'merek_model' => ['nullable','string','max:255'],
            'serial_number' => ['nullable','string','max:255'],
            'kondisi' => ['nullable','string','max:100'],
            'lokasi' => ['nullable','string','max:255'],
            'penanggung_jawab' => ['nullable','string','max:255'],
            'keterangan' => ['nullable','string','max:255'],
        ]);
        $itAsset->update($data);
        return back()->with('success','IT Aset berhasil diperbarui.');
    })->name('spa.it-assets.update');
    Route::delete('spa/it-assets/{itAsset}', function(ItAsset $itAsset){
        $itAsset->delete();
        return back()->with('success','IT Aset berhasil dihapus.');
    })->name('spa.it-assets.destroy');

    // SPA: KGB Updates CRUD
    Route::post('spa/kgb-updates', function(){
        $data = request()->validate([
            'pegawai_id' => ['required','exists:pegawais,id'],
            'tanggal_kgb' => ['required','date'],
            'jumlah_tahun' => ['required','numeric'],
            'tanggal_kgb_berikutnya' => ['nullable','date'],
            'catatan' => ['nullable','string','max:255'],
        ]);
        KgbUpdate::create($data);
        return back()->with('success','KGB update berhasil ditambahkan.');
    })->name('spa.kgb-updates.store');
    Route::put('spa/kgb-updates/{item}', function(KgbUpdate $item){
        $data = request()->validate([
            'pegawai_id' => ['required','exists:pegawais,id'],
            'tanggal_kgb' => ['required','date'],
            'jumlah_tahun' => ['required','numeric'],
            'tanggal_kgb_berikutnya' => ['nullable','date'],
            'catatan' => ['nullable','string','max:255'],
        ]);
        $item->update($data);
        return back()->with('success','KGB update berhasil diperbarui.');
    })->name('spa.kgb-updates.update');
    Route::delete('spa/kgb-updates/{item}', function(KgbUpdate $item){
        $item->delete();
        return back()->with('success','KGB update berhasil dihapus.');
    })->name('spa.kgb-updates.destroy');

    // SPA: Surat Tugas CRUD with relations
    Route::post('spa/surat-tugas', function(){
        $validated = request()->validate([
            'nomor_st' => ['required','string','max:100'],
            'tanggal_st' => ['required','date'],
            'tanggal_mulai' => ['required','date'],
            'tanggal_selesai' => ['required','date'],
            'lokasi_tugas' => ['required','in:Kota. Palopo,Kab. Luwu,Kab. Luwu Utara,Kab. Luwu Timur,Kab. Toraja Utara,Kab. Tana Toraja,Kab. Enrekang,Lainnya'],
            'lokasi_tugas_custom' => ['nullable','string','max:255','required_if:lokasi_tugas,Lainnya'],
            'deskripsi_tugas' => ['nullable','string','max:500'],
            'pegawai_ids' => ['array'],
            'pegawai_ids.*' => ['exists:pegawais,id'],
            'mak_ids' => ['array'],
            'mak_ids.*' => ['exists:maks,id'],
        ]);
        if (($validated['lokasi_tugas'] ?? null) === 'Lainnya') {
            $validated['lokasi_tugas'] = $validated['lokasi_tugas_custom'] ?? '';
        }
        unset($validated['lokasi_tugas_custom']);
        $pegawaiIds = $validated['pegawai_ids'] ?? [];
        $makIds = $validated['mak_ids'] ?? [];
        unset($validated['pegawai_ids'], $validated['mak_ids']);
        $item = SuratTugas::create($validated);
        if (!empty($pegawaiIds)) { $item->pegawai()->sync($pegawaiIds); }
        if (!empty($makIds)) { $item->maks()->sync($makIds); }
        return back()->with('success','Surat Tugas berhasil ditambahkan.');
    })->name('spa.surat-tugas.store');
    Route::put('spa/surat-tugas/{item}', function(SuratTugas $item){
        $validated = request()->validate([
            'nomor_st' => ['required','string','max:100'],
            'tanggal_st' => ['required','date'],
            'tanggal_mulai' => ['required','date'],
            'tanggal_selesai' => ['required','date'],
            'lokasi_tugas' => ['required','in:Kota. Palopo,Kab. Luwu,Kab. Luwu Utara,Kab. Luwu Timur,Kab. Toraja Utara,Kab. Tana Toraja,Kab. Enrekang,Lainnya'],
            'lokasi_tugas_custom' => ['nullable','string','max:255','required_if:lokasi_tugas,Lainnya'],
            'deskripsi_tugas' => ['nullable','string','max:500'],
            'pegawai_ids' => ['array'],
            'pegawai_ids.*' => ['exists:pegawais,id'],
            'mak_ids' => ['array'],
            'mak_ids.*' => ['exists:maks,id'],
        ]);
        if (($validated['lokasi_tugas'] ?? null) === 'Lainnya') {
            $validated['lokasi_tugas'] = $validated['lokasi_tugas_custom'] ?? '';
        }
        unset($validated['lokasi_tugas_custom']);
        $pegawaiIds = $validated['pegawai_ids'] ?? [];
        $makIds = $validated['mak_ids'] ?? [];
        unset($validated['pegawai_ids'], $validated['mak_ids']);
        $item->update($validated);
        $item->pegawai()->sync($pegawaiIds);
        $item->maks()->sync($makIds);
        return back()->with('success','Surat Tugas berhasil diperbarui.');
    })->name('spa.surat-tugas.update');
    Route::delete('spa/surat-tugas/{item}', function(SuratTugas $item){
        $item->delete();
        return back()->with('success','Surat Tugas berhasil dihapus.');
    })->name('spa.surat-tugas.destroy');

    // SPA: LPJ index
    Route::get('spa/lpj', function(){
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = Lpj::when($search, function($q) use ($search){
                $q->where('nomor_lpj','like',"%$search%")
                  ->orWhere('kegiatan','like',"%$search%");
            })
            ->when(in_array($sort, ['nomor_lpj','tanggal_masuk','kegiatan','nilai','status']), function($q) use($sort,$dir){ $q->orderBy($sort,$dir); })
            ->when(!in_array($sort, ['nomor_lpj','tanggal_masuk','kegiatan','nilai','status']), function($q){ $q->orderByDesc('tanggal_masuk'); })
            ->paginate(10)
            ->appends(['q'=>$search,'sort'=>$sort,'dir'=>$dir]);
        return Inertia::render('Lpj/Index', [ 'items' => $items, 'search'=>$search, 'sort'=>$sort, 'dir'=>$dir ]);
    })->name('spa.lpj.index');

    // SPA: MAK index
    Route::get('spa/mak', function(){
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = Mak::when($search, function($q) use ($search){
                $q->where('kode','like',"%$search%")
                  ->orWhere('uraian','like',"%$search%");
            })
            ->when(in_array($sort, ['kode','uraian']), function($q) use($sort,$dir){ $q->orderBy($sort,$dir); })
            ->when(!in_array($sort, ['kode','uraian']), function($q){ $q->orderBy('kode'); })
            ->paginate(10)
            ->appends(['q'=>$search,'sort'=>$sort,'dir'=>$dir]);
        return Inertia::render('Mak/Index', [ 'items' => $items, 'search'=>$search, 'sort'=>$sort, 'dir'=>$dir ]);
    })->name('spa.mak.index');

    // SPA: IT Assets index
    Route::get('spa/it-assets', function(){
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = ItAsset::when($search, function($q) use ($search){
                $q->where('kode_aset','like',"%$search%")
                  ->orWhere('nama_perangkat','like',"%$search%")
                  ->orWhere('merek_model','like',"%$search%");
            })
            ->when(in_array($sort, ['kode_aset','nama_perangkat','merek_model','serial_number','kondisi','lokasi','penanggung_jawab']), function($q) use($sort,$dir){ $q->orderBy($sort,$dir); })
            ->when(!in_array($sort, ['kode_aset','nama_perangkat','merek_model','serial_number','kondisi','lokasi','penanggung_jawab']), function($q){ $q->orderBy('kode_aset'); })
            ->paginate(10)
            ->appends(['q'=>$search,'sort'=>$sort,'dir'=>$dir]);
        return Inertia::render('ItAssets/Index', [ 'items' => $items, 'search'=>$search, 'sort'=>$sort, 'dir'=>$dir ]);
    })->name('spa.it-assets.index');

    // SPA: KGB Updates index
    Route::get('spa/kgb-updates', function(){
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = KgbUpdate::with('pegawai')
            ->when($search, function($q) use ($search){
                $q->whereHas('pegawai', function($p) use ($search){
                    $p->where('nama','like',"%$search%");
                });
            })
            ->when(in_array($sort, ['tanggal_kgb','jumlah_tahun','tanggal_kgb_berikutnya']), function($q) use($sort,$dir){ $q->orderBy($sort,$dir); })
            ->when(!in_array($sort, ['tanggal_kgb','jumlah_tahun','tanggal_kgb_berikutnya']), function($q){ $q->orderByDesc('tanggal_kgb'); })
            ->paginate(10)
            ->through(function($row){
                return [
                    'id' => $row->id,
                    'pegawai_id' => $row->pegawai_id,
                    'pegawai_nama' => $row->pegawai->nama ?? '-',
                    'tanggal_kgb' => $row->tanggal_kgb,
                    'jumlah_tahun' => $row->jumlah_tahun,
                    'tanggal_kgb_berikutnya' => $row->tanggal_kgb_berikutnya,
                    'catatan' => $row->catatan,
                ];
            })
            ->appends(['q'=>$search,'sort'=>$sort,'dir'=>$dir]);
        $pegawaiOptions = Pegawai::orderBy('nama')->limit(200)->get()->map(fn($p) => [ 'label' => $p->nama, 'value' => $p->id ]);
        return Inertia::render('KgbUpdates/Index', [ 'items' => $items, 'search'=>$search, 'sort'=>$sort,'dir'=>$dir, 'pegawaiOptions' => $pegawaiOptions ]);
    })->name('spa.kgb-updates.index');

    // SPA: Surat Tugas index
    Route::get('spa/surat-tugas', function(){
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = SuratTugas::with(['pegawai:id', 'maks:id'])
            ->when($search, function($q) use ($search){
                $q->where('nomor_st','like',"%$search%")
                  ->orWhere('lokasi_tugas','like',"%$search%");
            })
            ->when(in_array($sort, ['nomor_st','tanggal_st','tanggal_mulai','tanggal_selesai','lokasi_tugas']), function($q) use($sort,$dir){ $q->orderBy($sort,$dir); })
            ->when(!in_array($sort, ['nomor_st','tanggal_st','tanggal_mulai','tanggal_selesai','lokasi_tugas']), function($q){ $q->orderByDesc('tanggal_st'); })
            ->paginate(10)
            ->through(function($row){
                return [
                    'id' => $row->id,
                    'nomor_st' => $row->nomor_st,
                    'tanggal_st' => $row->tanggal_st,
                    'tanggal_mulai' => $row->tanggal_mulai,
                    'tanggal_selesai' => $row->tanggal_selesai,
                    'lokasi_tugas' => $row->lokasi_tugas,
                    'deskripsi_tugas' => $row->deskripsi_tugas,
                    'pegawai_ids' => $row->pegawai->pluck('id')->all(),
                    'mak_ids' => $row->maks->pluck('id')->all(),
                ];
            })
            ->appends(['q'=>$search,'sort'=>$sort,'dir'=>$dir]);
        $pegawaiOptions = Pegawai::orderBy('nama')->limit(200)->get()->map(fn($p) => [ 'label' => $p->nama, 'value' => $p->id ]);
        $makOptions = Mak::orderBy('kode')->limit(200)->get()->map(fn($m) => [ 'label' => $m->kode.' - '.$m->uraian, 'value' => $m->id ]);
        return Inertia::render('SuratTugas/Index', [ 'items' => $items, 'search'=>$search, 'sort'=>$sort,'dir'=>$dir, 'pegawaiOptions' => $pegawaiOptions, 'makOptions' => $makOptions ]);
    })->name('spa.surat-tugas.index');

    // SPA: Peminjaman BMN index
    Route::get('spa/peminjaman-bmn', function(){
        $search = request('q');
        $sort = request('sort');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $items = PeminjamanBmn::with(['bmn','pegawai'])
            ->when($search, function($q) use ($search){
                $q->whereHas('bmn', function($b) use ($search){
                    $b->where('kode_barang','like',"%$search%")
                      ->orWhere('nup','like',"%$search%")
                      ->orWhere('nama_barang','like',"%$search%");
                })->orWhereHas('pegawai', function($p) use ($search){
                    $p->where('nama','like',"%$search%");
                });
            })
            ->when(in_array($sort, ['tanggal_pinjam','tanggal_kembali','status']), function($q) use($sort,$dir){ $q->orderBy($sort,$dir); })
            ->when(!in_array($sort, ['tanggal_pinjam','tanggal_kembali','status']), function($q){ $q->orderByDesc('tanggal_pinjam'); })
            ->paginate(10)
            ->through(function($row){
                return [
                    'id' => $row->id,
                    'bmn_id' => $row->bmn_id,
                    'pegawai_id' => $row->pegawai_id,
                    'bmn_label' => ($row->bmn?->kode_barang).' / '.($row->bmn?->nup).' — '.($row->bmn?->nama_barang),
                    'pegawai_nama' => $row->pegawai?->nama ?? '-',
                    'tanggal_pinjam' => $row->tanggal_pinjam,
                    'tanggal_kembali' => $row->tanggal_kembali,
                    'status' => $row->status,
                    'keperluan' => $row->keperluan,
                    'lokasi_tujuan' => $row->lokasi_tujuan,
                    'keterangan' => $row->keterangan,
                ];
            })
            ->appends(['q'=>$search,'sort'=>$sort,'dir'=>$dir]);
        $bmnsOptions = Bmn::orderBy('kode_barang')->limit(200)->get()->map(fn($b)=> [ 'label' => $b->kode_barang.' / '.$b->nup.' — '.$b->nama_barang, 'value' => $b->id ]);
        $pegawaiOptions = Pegawai::orderBy('nama')->limit(200)->get()->map(fn($p)=> [ 'label' => $p->nama, 'value' => $p->id ]);
        return Inertia::render('PeminjamanBmn/Index', [ 'items' => $items, 'search' => $search, 'sort'=>$sort, 'dir'=>$dir, 'bmnsOptions' => $bmnsOptions, 'pegawaiOptions' => $pegawaiOptions ]);
    })->name('spa.peminjaman-bmn.index');

    // SPA: Peminjaman BMN store/update/delete + approval
    Route::post('spa/peminjaman-bmn', function(){
        $validated = request()->validate([
            'bmn_id' => ['required','exists:bmns,id'],
            'pegawai_id' => ['nullable','exists:pegawais,id'],
            'tanggal_mulai' => ['required','date'],
            'tanggal_sampai' => ['required','date','after_or_equal:tanggal_mulai'],
            'keperluan' => ['nullable','string'],
            'lokasi_tujuan' => ['required','string','max:100'],
            'keterangan' => ['nullable','string'],
        ]);
        PeminjamanBmn::create([
            'bmn_id' => $validated['bmn_id'],
            'pegawai_id' => $validated['pegawai_id'] ?? null,
            'tanggal_pinjam' => $validated['tanggal_mulai'],
            'tanggal_kembali' => $validated['tanggal_sampai'],
            'status' => 'pending',
            'keperluan' => $validated['keperluan'] ?? null,
            'lokasi_tujuan' => $validated['lokasi_tujuan'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);
        return back()->with('success','Peminjaman berhasil ditambahkan.');
    })->name('spa.peminjaman-bmn.store');

    Route::put('spa/peminjaman-bmn/{item}', function(PeminjamanBmn $item){
        $validated = request()->validate([
            'bmn_id' => ['required','exists:bmns,id'],
            'pegawai_id' => ['nullable','exists:pegawais,id'],
            'tanggal_mulai' => ['required','date'],
            'tanggal_sampai' => ['required','date','after_or_equal:tanggal_mulai'],
            'keperluan' => ['nullable','string'],
            'lokasi_tujuan' => ['required','string','max:100'],
            'keterangan' => ['nullable','string'],
        ]);
        $item->update([
            'bmn_id' => $validated['bmn_id'],
            'pegawai_id' => $validated['pegawai_id'] ?? null,
            'tanggal_pinjam' => $validated['tanggal_mulai'],
            'tanggal_kembali' => $validated['tanggal_sampai'],
            'keperluan' => $validated['keperluan'] ?? null,
            'lokasi_tujuan' => $validated['lokasi_tujuan'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);
        return back()->with('success','Peminjaman berhasil diperbarui.');
    })->name('spa.peminjaman-bmn.update');

    Route::delete('spa/peminjaman-bmn/{item}', function(PeminjamanBmn $item){
        $item->delete();
        return back()->with('success','Peminjaman berhasil dihapus.');
    })->name('spa.peminjaman-bmn.destroy');

    Route::put('spa/peminjaman-bmn/{item}/approve', function(PeminjamanBmn $item){
        if ($item->status === 'pending') $item->update(['status'=>'dipinjam']);
        return back()->with('success','Status peminjaman diperbarui.');
    })->name('spa.peminjaman-bmn.approve');
    Route::put('spa/peminjaman-bmn/{item}/return', function(PeminjamanBmn $item){
        if ($item->status === 'dipinjam') $item->update(['status'=>'dikembalikan']);
        return back()->with('success','Status peminjaman diperbarui.');
    })->name('spa.peminjaman-bmn.return');

    // SPA: BMN store
    Route::post('spa/bmn', function() {
        $data = request()->validate([
            'kode_barang' => ['required','string','max:50'],
            'nup' => ['required','string','max:50'],
            'nama_barang' => ['required','string','max:255'],
            'merek_barang' => ['nullable','string','max:255'],
        ]);

        $exists = \App\Models\Bmn::where('kode_barang', $data['kode_barang'])
            ->where('nup', $data['nup'])
            ->exists();
        if ($exists) {
            return back()->with('error', 'BMN dengan kombinasi kode/nup sudah ada.')->withInput();
        }
        \App\Models\Bmn::create($data);
        return back()->with('success', 'BMN berhasil ditambahkan.');
    })->name('spa.bmn.store');

    // SPA: BMN update
    Route::put('spa/bmn/{bmn}', function(\App\Models\Bmn $bmn) {
        $data = request()->validate([
            'kode_barang' => ['required','string','max:50'],
            'nup' => ['required','string','max:50'],
            'nama_barang' => ['required','string','max:255'],
            'merek_barang' => ['nullable','string','max:255'],
        ]);

        $exists = \App\Models\Bmn::where('kode_barang', $data['kode_barang'])
            ->where('nup', $data['nup'])
            ->where('id','!=',$bmn->id)
            ->exists();
        if ($exists) {
            return back()->with('error', 'BMN dengan kombinasi kode/nup sudah ada.')->withInput();
        }
        $bmn->update($data);
        return back()->with('success', 'BMN berhasil diperbarui.');
    })->name('spa.bmn.update');

    // SPA: BMN delete
    Route::delete('spa/bmn/{bmn}', function(\App\Models\Bmn $bmn) {
        $bmn->delete();
        return back()->with('success', 'BMN berhasil dihapus.');
    })->name('spa.bmn.destroy');

    // SPA: BMN CSV import
    Route::post('spa/bmn/import', function() {
        request()->validate([
            'file' => ['required','file','mimes:xlsx,xls'],
        ]);
        $path = request()->file('file')->getRealPath();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // indexed by letters
        if (count($rows) < 2) return back()->with('error','File kosong.');
        $headers = array_map(fn($v) => strtolower(trim((string)$v)), array_values($rows[1] ?? []));
        $map = [ 'kode_barang' => null, 'nup' => null, 'nama_barang' => null, 'merek_barang' => null ];
        foreach ($headers as $i => $h) { if (array_key_exists($h, $map)) $map[$h] = $i; }
        if ($map['kode_barang']===null || $map['nup']===null || $map['nama_barang']===null) {
            return back()->with('error','Header wajib: kode_barang,nup,nama_barang,(merek_barang opsional).');
        }
        $inserted=0;$updated=0;$skipped=0;
        foreach ($rows as $idx => $cols) {
            if ($idx === 1) continue; // skip header
            $vals = array_values($cols);
            if (count(array_filter($vals, fn($v)=>$v!==null && $v!==''))===0) continue;
            $data = [
                'kode_barang' => (string)($vals[$map['kode_barang']] ?? ''),
                'nup' => (string)($vals[$map['nup']] ?? ''),
                'nama_barang' => (string)($vals[$map['nama_barang']] ?? ''),
                'merek_barang' => $map['merek_barang']!==null ? (string)($vals[$map['merek_barang']] ?? '') : null,
            ];
            if ($data['merek_barang']==='') $data['merek_barang']=null;
            if (!$data['kode_barang'] || !$data['nup'] || !$data['nama_barang']) { $skipped++; continue; }
            $existing = \App\Models\Bmn::where('kode_barang',$data['kode_barang'])->where('nup',$data['nup'])->first();
            if ($existing) { $existing->update($data); $updated++; } else { \App\Models\Bmn::create($data); $inserted++; }
        }
        return back()->with('success', "Import BMN selesai: tambah $inserted, ubah $updated, lewati $skipped.");
    })->name('spa.bmn.import');

    // SPA: Pegawai CSV import
    Route::post('spa/pegawai/import', function() {
        request()->validate([
            'file' => ['required','file','mimes:xlsx,xls'],
        ]);
        $path = request()->file('file')->getRealPath();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        if (count($rows) < 2) return back()->with('error','File kosong.');
        $headers = array_map(fn($v) => strtolower(trim((string)$v)), array_values($rows[1] ?? []));
        $map = [ 'nip'=>null,'nama'=>null,'pangkat_gol'=>null,'jabatan'=>null,'tanggal_kgb_terakhir'=>null,'jumlah_tahun_kgb'=>null ];
        foreach ($headers as $i => $h) { if (array_key_exists($h, $map)) $map[$h] = $i; }
        if ($map['nip']===null || $map['nama']===null) return back()->with('error','Header wajib: nip,nama,(pangkat_gol,jabatan,tanggal_kgb_terakhir,jumlah_tahun_kgb opsional).');
        $inserted=0;$updated=0;$skipped=0;
        foreach ($rows as $idx => $cols) {
            if ($idx === 1) continue;
            $vals = array_values($cols);
            if (count(array_filter($vals, fn($v)=>$v!==null && $v!==''))===0) continue;
            $data = [
                'nip' => (string)($vals[$map['nip']] ?? ''),
                'nama' => (string)($vals[$map['nama']] ?? ''),
                'pangkat_gol' => $map['pangkat_gol']!==null ? (string)($vals[$map['pangkat_gol']] ?? '') : null,
                'jabatan' => $map['jabatan']!==null ? (string)($vals[$map['jabatan']] ?? '') : null,
                'tanggal_kgb_terakhir' => $map['tanggal_kgb_terakhir']!==null ? (string)($vals[$map['tanggal_kgb_terakhir']] ?? '') : null,
                'jumlah_tahun_kgb' => $map['jumlah_tahun_kgb']!==null ? (string)($vals[$map['jumlah_tahun_kgb']] ?? '') : null,
            ];
            if ($data['pangkat_gol']==='') $data['pangkat_gol']=null;
            if ($data['jabatan']==='') $data['jabatan']=null;
            if ($data['tanggal_kgb_terakhir']==='') $data['tanggal_kgb_terakhir']=null;
            if ($data['jumlah_tahun_kgb']==='') $data['jumlah_tahun_kgb']=null;
            if (!$data['nip'] || !$data['nama']) { $skipped++; continue; }
            $existing = \App\Models\Pegawai::where('nip',$data['nip'])->first();
            if ($existing) { $existing->update($data); $updated++; } else { \App\Models\Pegawai::create($data); $inserted++; }
        }
        return back()->with('success', "Import Pegawai selesai: tambah $inserted, ubah $updated, lewati $skipped.");
    })->name('spa.pegawai.import');

    // SPA: MAK CSV import
    Route::post('spa/mak/import', function() {
        request()->validate([
            'file' => ['required','file','mimes:xlsx,xls'],
        ]);
        $path = request()->file('file')->getRealPath();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        if (count($rows) < 2) return back()->with('error','File kosong.');
        $headers = array_map(fn($v) => strtolower(trim((string)$v)), array_values($rows[1] ?? []));
        $map = [ 'kode'=>null,'uraian'=>null ];
        foreach ($headers as $i => $h) { if (array_key_exists($h, $map)) $map[$h] = $i; }
        if ($map['kode']===null || $map['uraian']===null) return back()->with('error','Header wajib: kode,uraian');
        $inserted=0;$updated=0;$skipped=0;
        foreach ($rows as $idx => $cols) {
            if ($idx === 1) continue;
            $vals = array_values($cols);
            if (count(array_filter($vals, fn($v)=>$v!==null && $v!==''))===0) continue;
            $data = [ 'kode' => (string)($vals[$map['kode']] ?? ''), 'uraian' => (string)($vals[$map['uraian']] ?? '') ];
            if (!$data['kode']) { $skipped++; continue; }
            if ($data['uraian']==='') $data['uraian']=null;
            $existing = \App\Models\Mak::where('kode',$data['kode'])->first();
            if ($existing) { $existing->update($data); $updated++; } else { \App\Models\Mak::create($data); $inserted++; }
        }
        return back()->with('success', "Import MAK selesai: tambah $inserted, ubah $updated, lewati $skipped.");
    })->name('spa.mak.import');
});

require __DIR__.'/auth.php';
