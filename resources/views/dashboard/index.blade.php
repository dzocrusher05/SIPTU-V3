@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
  <div class="bg-white rounded-lg shadow p-4">
    <div class="text-sm text-gray-500">BMN</div>
    <div class="text-2xl font-semibold">Ringkasan</div>
  </div>
  <div class="bg-white rounded-lg shadow p-4">
    <div class="text-sm text-gray-500">Pegawai</div>
    <div class="text-2xl font-semibold">Ringkasan</div>
  </div>
  <div class="bg-white rounded-lg shadow p-4">
    <div class="text-sm text-gray-500">Keuangan</div>
    <div class="text-2xl font-semibold">Ringkasan</div>
  </div>
</div>

<div class="mt-6 bg-white rounded-lg shadow p-4">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold">Jadwal Tugas (Contoh Flatpickr)</h2>
  </div>
  <input type="text" class="flatpickr-range w-full md:w-1/2 px-3 py-2 border rounded" placeholder="Pilih rentang tanggal" />
</div>
@endsection
