<template>
  <AdminLayout title="Data Pegawai">
    <div class="max-w-7xl mx-auto">
      <PageHeader title="Data Pegawai" :crumbs="[{ label: 'Dashboard', href: '/spa' }, { label: 'Kepegawaian' }, { label: 'Data Pegawai' }]">
        <template #actions>
          <Toolbar v-model="q" placeholder="Cari NIP/nama" @search="search">
            <n-button @click="openImport">Import CSV</n-button>
            <n-button type="primary" @click="openCreate">Tambah</n-button>
          </Toolbar>
        </template>
      </PageHeader>
      <div class="overflow-x-auto -mx-1 sm:mx-0">
        <div class="min-w-[640px] sm:min-w-0">
          <n-data-table :columns="columns" :data="rows" :bordered="false" size="small" :remote="true" @update:sorter="onSort" />
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <n-pagination :page="page" :page-count="pageCount" @update:page="goPage" />
      </div>
    </div>
  </AdminLayout>
      <n-modal v-model:show="showImport" preset="dialog" title="Import Excel Pegawai (.xlsx)">
        <div class="space-y-3">
          <div class="text-sm">Unggah file Excel (.xlsx). Header wajib: <code>nip,nama</code>. Opsional: <code>pangkat_gol,jabatan,tanggal_kgb_terakhir,jumlah_tahun_kgb</code>. Kolom jumlah tahun KGB dan KGB terakhir boleh dikosongkan.</div>
          <div class="text-sm">Template Excel: <a href="/pegawai/template" class="underline text-blue-600">download</a></div>
          <input type="file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" @change="onFileChange" />
          <div class="flex justify-end gap-2">
            <n-button @click="showImport=false">Batal</n-button>
            <n-button type="primary" :loading="importing" :disabled="!importFile" @click="submitImport">Upload</n-button>
          </div>
        </div>
      </n-modal>
      <n-modal v-model:show="showModal" preset="dialog" :title="isEdit ? 'Ubah Pegawai' : 'Tambah Pegawai'">
        <n-form label-placement="top">
          <n-form-item label="NIP" :feedback="form.errors?.nip">
            <n-input v-model:value="form.nip" placeholder="NIP" />
          </n-form-item>
          <n-form-item label="Nama" :feedback="form.errors?.nama">
            <n-input v-model:value="form.nama" placeholder="Nama" />
          </n-form-item>
          <n-form-item label="Pangkat/Gol" :feedback="form.errors?.pangkat_gol">
            <n-input v-model:value="form.pangkat_gol" placeholder="Pangkat/Gol" />
          </n-form-item>
          <n-form-item label="Jabatan" :feedback="form.errors?.jabatan">
            <n-input v-model:value="form.jabatan" placeholder="Jabatan" />
          </n-form-item>
          <n-form-item label="Tanggal KGB Terakhir" :feedback="form.errors?.tanggal_kgb_terakhir">
            <DatePicker v-model="form.tanggal_kgb_terakhir" />
          </n-form-item>
          <n-form-item label="Jumlah Tahun KGB" :feedback="form.errors?.jumlah_tahun_kgb">
            <n-input v-model:value="form.jumlah_tahun_kgb" placeholder="Misal: 2" />
          </n-form-item>
          <div class="flex justify-end gap-2">
            <n-button @click="showModal=false">Batal</n-button>
            <n-button type="primary" :loading="form.processing" @click="submit">Simpan</n-button>
          </div>
        </n-form>
      </n-modal>
    </template>

<script setup>
import AdminLayout from '../../Layouts/AdminLayout.vue'
import PageHeader from '../../Components/PageHeader.vue'
import Toolbar from '../../Components/Toolbar.vue'
import { NButton, NDataTable, NInput, NPagination, NModal, NForm, NFormItem } from 'naive-ui'
import { computed, ref, watch, h } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import DatePicker from '../../Components/DatePicker.vue'

const props = defineProps({ pegawais: Object, search: String, sort: String, dir: String })
const q = ref(props.search || '')
const page = ref(props.pegawais.current_page || 1)
const pageCount = computed(() => props.pegawais.last_page || 1)
const rows = computed(() => props.pegawais.data || [])
const columns = [
  { title: 'NIP', key: 'nip', sorter: 'default' },
  { title: 'Nama', key: 'nama', sorter: 'default' },
  { title: 'Pangkat/Gol', key: 'pangkat_gol', sorter: 'default' },
  { title: 'Jabatan', key: 'jabatan', sorter: 'default' },
  { title: 'Tgl KGB Terakhir', key: 'tanggal_kgb_terakhir', sorter: 'default' },
  {
    title: '', key: 'actions', render(row){
      return h('div', { class: 'text-right' }, [
        h(NButton, { text: true, size: 'small', onClick: () => openEdit(row) }, { default: () => 'Edit' }),
        h(NButton, { text: true, type: 'error', size: 'small', onClick: () => doDelete(row) }, { default: () => 'Hapus' }),
      ])
    }
  }
]
function search(){ router.get('/spa/pegawai', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
function goPage(p){ router.get('/spa/pegawai', { q: q.value, page: p, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
watch(() => props.pegawais.current_page, (val)=>{ if (val) page.value = val })
const sort = ref(props.sort || '')
const dir = ref(props.dir || 'asc')
function onSort(sorter){
  if (!sorter || !sorter.columnKey || !sorter.order){ sort.value=''; dir.value='asc' }
  else { sort.value = sorter.columnKey; dir.value = sorter.order === 'descend' ? 'desc' : 'asc' }
  router.get('/spa/pegawai', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}

// modal form
const showModal = ref(false)
const isEdit = ref(false)
const form = useForm({ id: null, nip: '', nama: '', pangkat_gol: '', jabatan: '', tanggal_kgb_terakhir: '', jumlah_tahun_kgb: '' })
function openCreate(){ isEdit.value = false; form.reset(); showModal.value = true }
function openEdit(row){
  isEdit.value = true
  form.id = row.id
  form.nip = row.nip
  form.nama = row.nama
  form.pangkat_gol = row.pangkat_gol || ''
  form.jabatan = row.jabatan || ''
  form.tanggal_kgb_terakhir = row.tanggal_kgb_terakhir || ''
  form.jumlah_tahun_kgb = row.jumlah_tahun_kgb || ''
  showModal.value = true
}
function submit(){
  if (isEdit.value){
    form.put(`/spa/pegawai/${form.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/spa/pegawai', { onSuccess: () => { showModal.value = false } })
  }
}
function doDelete(row){ router.delete(`/spa/pegawai/${row.id}`, { preserveState: true }) }

// Import CSV
const showImport = ref(false)
const importFile = ref(null)
const importing = ref(false)
function openImport(){ showImport.value = true }
function onFileChange(e){ importFile.value = e.target.files?.[0] || null }
function submitImport(){
  if (!importFile.value) return
  importing.value = true
  const f = useForm({ file: importFile.value })
  f.post('/spa/pegawai/import', { forceFormData: true, onFinish: () => { importing.value = false; importFile.value=null; showImport.value=false } })
}
</script>
