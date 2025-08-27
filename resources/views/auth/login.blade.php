<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — {{ config('app.name','SIPTU.V3') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
  </head>
  <body class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
      <div class="hidden md:block">
        <div class="rounded-2xl bg-gray-900 text-white p-8 h-full shadow-xl">
          <div class="flex items-center gap-3 text-2xl font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 01-.53 1.28h-.69v6.5a.75.75 0 01-.75.75h-4.5a.75.75 0 01-.75-.75v-4.25a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 008 16.06V20.5a.75.75 0 01-.75.75h-4.5a.75.75 0 01-.75-.75V13.81h-.69a.75.75 0 01-.53-1.28l8.69-8.69z"/></svg>
            {{ config('app.name','SIPTU.V3') }}
          </div>
          <p class="mt-4 text-gray-300 leading-relaxed">Selamat datang kembali. Kelola data pegawai, BMN, keuangan, dan tugas dalam satu antarmuka yang cepat dan modern.</p>
          <div class="mt-8 grid grid-cols-2 gap-4 text-sm">
            <div class="rounded-lg bg-gray-800/60 p-4">
              <div class="font-medium">Kepegawaian</div>
              <div class="text-gray-400">Data pegawai, KGB, dan surat tugas</div>
            </div>
            <div class="rounded-lg bg-gray-800/60 p-4">
              <div class="font-medium">BMN</div>
              <div class="text-gray-400">Inventaris dan peminjaman</div>
            </div>
            <div class="rounded-lg bg-gray-800/60 p-4">
              <div class="font-medium">Keuangan</div>
              <div class="text-gray-400">MAK dan LPJ</div>
            </div>
            <div class="rounded-lg bg-gray-800/60 p-4">
              <div class="font-medium">IT Aset</div>
              <div class="text-gray-400">Perangkat dan penanggung jawab</div>
            </div>
          </div>
        </div>
      </div>
      <div>
        <div class="rounded-2xl bg-white p-8 shadow-lg border border-gray-100">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Masuk</h1>
            <p class="text-gray-500 text-sm mt-1">Gunakan kredensial akun Anda</p>
          </div>

          @if (session('status'))
            <div class="mb-4 p-3 rounded bg-blue-50 text-blue-700">{{ session('status') }}</div>
          @endif

          <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
              <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="mt-1 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
              @error('email')<div class="mt-2 text-sm text-red-600">{{ $message }}</div>@enderror
            </div>
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
              <input id="password" type="password" name="password" required autocomplete="current-password" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" />
              @error('password')<div class="mt-2 text-sm text-red-600">{{ $message }}</div>@enderror
            </div>
            <div class="flex items-center justify-between">
              <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"> Ingat saya
              </label>
              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-gray-900">Lupa password?</a>
              @endif
            </div>
            <button class="w-full py-2.5 rounded-lg bg-gray-900 text-white hover:bg-black transition">Masuk</button>
          </form>
          @if (Route::has('register'))
            <div class="mt-4 text-center text-sm text-gray-600">
              Belum punya akun?
              <a href="{{ route('register') }}" class="font-medium text-gray-900 hover:underline">Daftar</a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </body>
</html>
