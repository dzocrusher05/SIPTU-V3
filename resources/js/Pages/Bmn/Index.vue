<template>
  <AdminLayout title="Data BMN">
    <div class="max-w-7xl mx-auto">
      <PageHeader title="Data BMN" :crumbs="[{ label: 'Dashboard', href: '/spa' }, { label: 'BMN' }, { label: 'Data BMN' }]">
        <template #actions>
          <Toolbar v-model="q" placeholder="Cari kode/nup/nama" @search="search">
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
    <n-modal v-model:show="showModal" preset="dialog" :title="isEdit ? 'Ubah BMN' : 'Tambah BMN'">
      <n-form label-placement="top">
        <n-form-item label="Kode Barang" :feedback="form.errors?.kode_barang">
          <n-input v-model:value="form.kode_barang" placeholder="Kode Barang" />
        </n-form-item>
        <n-form-item label="NUP" :feedback="form.errors?.nup">
          <n-input v-model:value="form.nup" placeholder="NUP" />
        </n-form-item>
        <n-form-item label="Nama Barang" :feedback="form.errors?.nama_barang">
          <n-input v-model:value="form.nama_barang" placeholder="Nama Barang" />
        </n-form-item>
        <n-form-item label="Merek" :feedback="form.errors?.merek_barang">
          <n-input v-model:value="form.merek_barang" placeholder="Merek (opsional)" />
        </n-form-item>
        <div class="flex justify-end gap-2">
          <n-button @click="showModal=false">Batal</n-button>
          <n-button type="primary" :loading="form.processing" @click="submit">Simpan</n-button>
        </div>
      </n-form>
    </n-modal>
    <n-modal v-model:show="showImport" preset="dialog" title="Import Excel BMN (.xlsx)">
      <div class="space-y-3">
        <div class="text-sm">Unggah file Excel (.xlsx) dengan header: <code>kode_barang,nup,nama_barang,merek_barang</code></div>
        <div class="text-sm">Template Excel: <a href="/bmn/template" class="underline text-blue-600">download</a></div>
        <input type="file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" @change="onFileChange" />
        <div class="flex justify-end gap-2">
          <n-button @click="showImport=false">Batal</n-button>
          <n-button type="primary" :loading="importing" :disabled="!importFile" @click="submitImport">Upload</n-button>
        </div>
      </div>
    </n-modal>
  </AdminLayout>
  
</template>

<script setup>
import AdminLayout from '../../Layouts/AdminLayout.vue'
import PageHeader from '../../Components/PageHeader.vue'
import Toolbar from '../../Components/Toolbar.vue'
import { NButton, NDataTable, NInput, NPagination, NModal, NForm, NFormItem, NInputNumber } from 'naive-ui'
import { computed, ref, watch, h } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'

const pageProps = usePage().props
const props = defineProps({
  bmns: { type: Object, required: true },
  search: { type: String, default: '' },
  sort: { type: String, default: '' },
  dir: { type: String, default: 'asc' }
})

const q = ref(props.search || '')
const page = ref(props.bmns.current_page || 1)
const pageCount = computed(() => props.bmns.last_page || 1)

const rows = computed(() => props.bmns.data || [])

const columns = [
  { title: 'Kode Barang', key: 'kode_barang', sorter: 'default' },
  { title: 'NUP', key: 'nup', sorter: 'default' },
  { title: 'Nama Barang', key: 'nama_barang', sorter: 'default' },
  { title: 'Merek', key: 'merek_barang', sorter: 'default' },
  {
    title: '',
    key: 'actions',
    render(row){
      return h('div', { class: 'text-right' }, [
        h(NButton, { text: true, size: 'small', onClick: () => openEdit(row) }, { default: () => 'Edit' }),
        h(NButton, { text: true, type: 'error', size: 'small', onClick: () => doDelete(row) }, { default: () => 'Hapus' }),
      ])
    }
  }
]

function search(){
  router.get('/spa/bmn', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}
function goPage(p){
  router.get('/spa/bmn', { q: q.value, page: p, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}

watch(() => props.bmns.current_page, (val) => { if (val) page.value = val })
const sort = ref(props.sort || '')
const dir = ref(props.dir || 'asc')

function onSort(sorter){
  if (!sorter || !sorter.columnKey || !sorter.order){ sort.value=''; dir.value='asc' }
  else { sort.value = sorter.columnKey; dir.value = sorter.order === 'descend' ? 'desc' : 'asc' }
  router.get('/spa/bmn', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}

// Create/Edit modal
const showModal = ref(false)
const isEdit = ref(false)
const form = useForm({ id: null, kode_barang: '', nup: '', nama_barang: '', merek_barang: '' })

function openCreate(){
  isEdit.value = false
  form.reset()
  showModal.value = true
}
function openEdit(row){
  isEdit.value = true
  form.id = row.id
  form.kode_barang = row.kode_barang
  form.nup = row.nup
  form.nama_barang = row.nama_barang
  form.merek_barang = row.merek_barang || ''
  showModal.value = true
}
function submit(){
  if (isEdit.value){
    form.put(`/spa/bmn/${form.id}`, { onSuccess: () => { showModal.value = false } })
  } else {
    form.post('/spa/bmn', { onSuccess: () => { showModal.value = false } })
  }
}
function doDelete(row){
  router.delete(`/spa/bmn/${row.id}`, { preserveState: true })
}

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
  f.post('/spa/bmn/import', { forceFormData: true, onFinish: () => { importing.value = false; importFile.value=null; showImport.value=false } })
}
</script>
