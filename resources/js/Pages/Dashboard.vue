<template>
  <AdminLayout title="Dashboard">
    <div class="max-w-7xl mx-auto">
      <PageHeader
        title="Dashboard"
        subtitle="Ringkasan cepat data penting dan pintasan aksi"
        :crumbs="[{ label: 'Dashboard' }]"
      />

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div>
          <n-card size="small" :content-style="{ padding: '12px 14px' }" class="border-l-4 border-blue-700 bg-blue-600 text-white hover:shadow-md transition-shadow h-28">
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide font-medium">BMN</div>
                <div class="text-2xl font-extrabold leading-tight">{{ metrics.bmn }}</div>
                <div class="text-xs opacity-90">Total BMN terdaftar</div>
                <Link href="/spa/bmn" class="text-xs underline opacity-90">Lihat</Link>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 opacity-80">
                <path :d="iconPaths.briefcase" />
              </svg>
            </div>
          </n-card>
        </div>
        <div>
          <n-card size="small" :content-style="{ padding: '12px 14px' }" class="border-l-4 border-emerald-700 bg-emerald-600 text-white hover:shadow-md transition-shadow h-28">
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide font-medium">Pegawai</div>
                <div class="text-2xl font-extrabold leading-tight">{{ metrics.pegawai }}</div>
                <div class="text-xs opacity-90">Total pegawai aktif</div>
                <Link href="/spa/pegawai" class="text-xs underline opacity-90">Lihat</Link>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 opacity-80">
                <path :d="iconPaths.users" />
              </svg>
            </div>
          </n-card>
        </div>
        <div>
          <n-card size="small" :content-style="{ padding: '12px 14px' }" class="border-l-4 border-amber-700 bg-amber-600 text-white hover:shadow-md transition-shadow h-28">
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide font-medium">Peminjaman</div>
                <div class="text-2xl font-extrabold leading-tight">{{ metrics.peminjaman_aktif }}</div>
                <div class="text-xs opacity-90">Sedang berjalan</div>
                <Link href="/spa/peminjaman-bmn" class="text-xs underline opacity-90">Lihat</Link>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 opacity-80">
                <path :d="iconPaths.folder" />
              </svg>
            </div>
          </n-card>
        </div>
        <div>
          <n-card size="small" :content-style="{ padding: '12px 14px' }" class="border-l-4 border-fuchsia-700 bg-fuchsia-600 text-white hover:shadow-md transition-shadow h-28">
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide font-medium">LPJ</div>
                <div class="text-2xl font-extrabold leading-tight">{{ metrics.lpj_pending }}</div>
                <div class="text-xs opacity-90">Belum selesai</div>
                <Link href="/spa/lpj" class="text-xs underline opacity-90">Lihat</Link>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 opacity-80">
                <path :d="iconPaths.doc" />
              </svg>
            </div>
          </n-card>
        </div>
        <div>
          <n-card size="small" :content-style="{ padding: '12px 14px' }" class="border-l-4 border-indigo-700 bg-indigo-600 text-white hover:shadow-md transition-shadow h-28">
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide font-medium">Surat Tugas</div>
                <div class="text-2xl font-extrabold leading-tight">{{ metrics.surat_tugas_aktif }}</div>
                <div class="text-xs opacity-90">Aktif hari ini</div>
                <Link href="/spa/surat-tugas" class="text-xs underline opacity-90">Lihat</Link>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 opacity-80">
                <path :d="iconPaths.briefcase" />
              </svg>
            </div>
          </n-card>
        </div>
        <div>
          <n-card size="small" :content-style="{ padding: '12px 14px' }" class="border-l-4 border-sky-700 bg-sky-600 text-white hover:shadow-md transition-shadow h-28">
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide font-medium">KGB</div>
                <div class="text-2xl font-extrabold leading-tight">{{ metrics.kgb_due_30 }}</div>
                <div class="text-xs opacity-90">Jatuh tempo 30 hari</div>
                <Link href="/spa/kgb-updates" class="text-xs underline opacity-90">Lihat</Link>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 opacity-80">
                <path :d="iconPaths.calendar" />
              </svg>
            </div>
          </n-card>
        </div>
        <div>
          <n-card size="small" :content-style="{ padding: '12px 14px' }" class="border-l-4 border-cyan-700 bg-cyan-600 text-white hover:shadow-md transition-shadow h-28">
            <div class="flex items-center justify-between gap-3">
              <div>
                <div class="text-xs uppercase tracking-wide font-medium">IT Aset</div>
                <div class="text-2xl font-extrabold leading-tight">{{ metrics.it_assets }}</div>
                <div class="text-xs opacity-90">Total aset TI</div>
                <Link href="/spa/it-assets" class="text-xs underline opacity-90">Lihat</Link>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 opacity-80">
                <path :d="iconPaths.chip" />
              </svg>
            </div>
          </n-card>
        </div>
      </div>

      <div class="mt-6">
        <n-card title="Tren Aktivitas (6 Bulan)" size="small" class="mb-4">
          <SimpleBarChart :labels="timeseries.labels" :datasets="timeseries.datasets" title="Per modul" />
        </n-card>
        <n-card title="Pintasan Cepat" size="small">
          <div class="flex flex-wrap gap-2">
            <Link v-for="(s, i) in shortcuts" :key="i" :href="s.href" class="px-3 py-1 border rounded hover:bg-gray-50 dark:hover:bg-gray-800">
              {{ s.label }}
            </Link>
          </div>
        </n-card>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../Layouts/AdminLayout.vue'
import PageHeader from '../Components/PageHeader.vue'
import SimpleBarChart from '../Components/SimpleBarChart.vue'
import { NCard } from 'naive-ui'
import { Link } from '@inertiajs/vue3'

// Inline icon paths (Heroicons-like) to avoid extra deps
const iconPaths = {
  briefcase: 'M9 3.75A2.25 2.25 0 0 1 11.25 1.5h1.5A2.25 2.25 0 0 1 15 3.75V6h3.75A1.75 1.75 0 0 1 20.5 7.75v9.5A1.75 1.75 0 0 1 18.75 19H5.25A1.75 1.75 0 0 1 3.5 17.25v-9.5A1.75 1.75 0 0 1 5.25 6H9V3.75zM10.5 6h3V3.75a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75V6z',
  users: 'M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM4.5 18.75a6.75 6.75 0 0 1 13.5 0v.75h-13.5v-.75z',
  doc: 'M6.75 2.25A1.5 1.5 0 0 0 5.25 3.75v16.5A1.5 1.5 0 0 0 6.75 21.75h10.5a1.5 1.5 0 0 0 1.5-1.5V8.56a1.5 1.5 0 0 0-.44-1.06l-4.56-4.56a1.5 1.5 0 0 0-1.06-.44H6.75zM9 9.75h6a.75.75 0 1 1 0 1.5H9a.75.75 0 0 1 0-1.5zm0 3h6a.75.75 0 1 1 0 1.5H9a.75.75 0 0 1 0-1.5z',
  folder: 'M2.25 6.75A1.75 1.75 0 0 1 4 5h4.19a1.5 1.5 0 0 1 1.06.44L10.81 7H20a1.75 1.75 0 0 1 1.75 1.75v7.5A1.75 1.75 0 0 1 20 18H4a1.75 1.75 0 0 1-1.75-1.75v-9.5z',
  chip: 'M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5A2.25 2.25 0 0 1 17.25 19.5H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75zM9 9h6v6H9V9z',
  calendar: 'M6.75 3A.75.75 0 0 1 7.5 2.25h.75V3a.75.75 0 0 0 1.5 0V2.25h4.5V3a.75.75 0 0 0 1.5 0V2.25h.75c.414 0 .75.336.75.75V6h-12V3.75c0-.414.336-.75.75-.75zM3 7.5h18v11.25A1.25 1.25 0 0 1 19.75 20H4.25A1.25 1.25 0 0 1 3 18.75V7.5zm3.75 3h3v3h-3v-3z',
}

const props = defineProps({
  metrics: { type: Object, required: true },
  shortcuts: { type: Array, default: () => [] },
  timeseries: { type: Object, default: () => ({ labels: [], datasets: [] }) }
})

const { metrics, shortcuts, timeseries } = props
</script>
