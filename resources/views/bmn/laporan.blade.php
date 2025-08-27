@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-lg shadow p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Laporan Peminjaman BMN</h1>
  </div>

  <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
    <div>
      <label class="block text-sm text-gray-600 mb-1">Rentang Tanggal</label>
      <input type="text" name="range" value="{{ $range ?? '' }}" class="w-full px-3 py-2 border rounded flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Status</label>
      <select name="status" class="w-full px-3 py-2 border rounded">
        <option value="">Semua</option>
        @foreach(['dipinjam'=>'Dipinjam','dikembalikan'=>'Dikembalikan'] as $val=>$lab)
          <option value="{{ $val }}" @selected(($status ?? '')===$val)>{{ $lab }}</option>
        @endforeach
      </select>
    </div>
    <div class="flex items-end">
      <button class="px-4 py-2 border rounded">Terapkan</button>
    </div>
  </form>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="text-left text-gray-500">
          <th class="py-2">Tgl Pinjam</th>
          <th class="py-2">BMN</th>
          <th class="py-2">Peminjam</th>
          <th class="py-2">Tgl Kembali</th>
          <th class="py-2">Status</th>
          <th class="py-2">Keperluan</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($items as $row)
          <tr>
            <td class="py-2">{{ $row->tanggal_pinjam }}</td>
            <td class="py-2">{{ $row->bmn?->kode_barang }}/{{ $row->bmn?->nup }} - {{ $row->bmn?->nama_barang }}</td>
            <td class="py-2">{{ $row->pegawai?->nama ?? '-' }}</td>
            <td class="py-2">{{ $row->tanggal_kembali ?? '-' }}</td>
            <td class="py-2">{{ $row->status }}</td>
            <td class="py-2">{{ $row->keperluan }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-4 text-center text-gray-500">Tidak ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
