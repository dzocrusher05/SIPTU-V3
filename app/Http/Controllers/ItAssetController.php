<?php

namespace App\Http\Controllers;

use App\Models\ItAsset;
use Illuminate\Http\Request;

class ItAssetController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('q')->toString();
        $items = ItAsset::when($search, function ($q) use ($search) {
                $q->where('kode_aset','like',"%$search%")
                  ->orWhere('nama_perangkat','like',"%$search%")
                  ->orWhere('serial_number','like',"%$search%")
                  ->orWhere('lokasi','like',"%$search%");
            })
            ->orderBy('kode_aset')
            ->paginate(10)
            ->withQueryString();
        return view('it_assets.index', compact('items','search'));
    }

    public function create()
    {
        return view('it_assets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_aset' => ['required','string','max:100','unique:it_assets,kode_aset'],
            'nama_perangkat' => ['required','string','max:255'],
            'merek_model' => ['nullable','string','max:255'],
            'serial_number' => ['nullable','string','max:255'],
            'kondisi' => ['required','string','max:50'],
            'lokasi' => ['nullable','string','max:255'],
            'penanggung_jawab' => ['nullable','string','max:255'],
            'keterangan' => ['nullable','string'],
        ]);
        ItAsset::create($data);
        return redirect()->route('it-assets.index')->with('success','Aset TI berhasil ditambahkan.');
    }

    public function edit(ItAsset $it_asset)
    {
        return view('it_assets.edit', ['item'=>$it_asset]);
    }

    public function update(Request $request, ItAsset $it_asset)
    {
        $data = $request->validate([
            'kode_aset' => ['required','string','max:100','unique:it_assets,kode_aset,'.$it_asset->id],
            'nama_perangkat' => ['required','string','max:255'],
            'merek_model' => ['nullable','string','max:255'],
            'serial_number' => ['nullable','string','max:255'],
            'kondisi' => ['required','string','max:50'],
            'lokasi' => ['nullable','string','max:255'],
            'penanggung_jawab' => ['nullable','string','max:255'],
            'keterangan' => ['nullable','string'],
        ]);
        $it_asset->update($data);
        return redirect()->route('it-assets.index')->with('success','Aset TI berhasil diperbarui.');
    }

    public function destroy(ItAsset $it_asset)
    {
        $it_asset->delete();
        return redirect()->route('it-assets.index')->with('success','Aset TI berhasil dihapus.');
    }
}

