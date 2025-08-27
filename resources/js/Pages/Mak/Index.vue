<template>
  <AdminLayout title="MAK">
    <div class="max-w-7xl mx-auto">
      <PageHeader title="MAK" :crumbs="[{ label: 'Dashboard', href: '/spa' }, { label: 'Keuangan' }, { label: 'MAK' }]">
        <template #actions>
          <Toolbar v-model="q" placeholder="Cari kode/uraian" @search="search">
            <n-button @click="openImport">Import CSV</n-button>
            <n-button type="primary" @click="openCreate">Tambah</n-button>
          </Toolbar>
        </template>
      </PageHeader>
      <div class="overflow-x-auto -mx-1 sm:mx-0">
        <div class="min-w-[560px] sm:min-w-0">
          <n-data-table :columns="columns" :data="rows" :bordered="false" size="small" :remote="true" @update:sorter="onSort" />
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <n-pagination :page="page" :page-count="pageCount" @update:page="goPage" />
      </div>
    </div>
  </AdminLayout>
      <n-modal v-model:show="showImport" preset="dialog" title="Import Excel MAK (.xlsx)">
        <div class="space-y-3">
          <div class="text-sm">Unggah file Excel (.xlsx). Header wajib: <code>kode,uraian</code></div>
          <div class="text-sm">Template Excel: <a href="/mak/template" class="underline text-blue-600">download</a></div>
          <input type="file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" @change="onFileChange" />
          <div class="flex justify-end gap-2">
            <n-button @click="showImport=false">Batal</n-button>
            <n-button type="primary" :loading="importing" :disabled="!importFile" @click="submitImport">Upload</n-button>
          </div>
        </div>
      </n-modal>
      <n-modal v-model:show="showModal" preset="dialog" :title="isEdit ? 'Ubah MAK' : 'Tambah MAK'">
        <n-form label-placement="top">
          <n-form-item label="Kode" :feedback="form.errors?.kode">
            <n-input v-model:value="form.kode" placeholder="Kode" />
          </n-form-item>
          <n-form-item label="Uraian" :feedback="form.errors?.uraian">
            <n-input v-model:value="form.uraian" placeholder="Uraian" />
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

const props = defineProps({ items: Object, search: String, sort: String, dir: String })
const q = ref(props.search || '')
const page = ref(props.items.current_page || 1)
const pageCount = computed(() => props.items.last_page || 1)
const rows = computed(() => props.items.data || [])
const columns = [
  { title: 'Kode', key: 'kode', sorter: 'default' },
  { title: 'Uraian', key: 'uraian', sorter: 'default' },
  { title: '', key: 'actions', render(row){ return h('div', { class: 'text-right' }, [
    h(NButton, { text: true, size: 'small', onClick: () => openEdit(row) }, { default: () => 'Edit' }),
    h(NButton, { text: true, type: 'error', size: 'small', onClick: () => doDelete(row) }, { default: () => 'Hapus' }),
  ]) } }
]
function search(){ router.get('/spa/mak', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
function goPage(p){ router.get('/spa/mak', { q: q.value, page: p, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
watch(() => props.items.current_page, (val)=>{ if (val) page.value = val })
const sort = ref(props.sort || '')
const dir = ref(props.dir || 'asc')
function onSort(sorter){
  if (!sorter || !sorter.columnKey || !sorter.order){ sort.value=''; dir.value='asc' }
  else { sort.value = sorter.columnKey; dir.value = sorter.order === 'descend' ? 'desc' : 'asc' }
  router.get('/spa/mak', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}
const showModal = ref(false)
const isEdit = ref(false)
const form = useForm({ id: null, kode: '', uraian: '' })
function openCreate(){ isEdit.value=false; form.reset(); showModal.value=true }
function openEdit(row){ isEdit.value=true; form.id=row.id; form.kode=row.kode; form.uraian=row.uraian; showModal.value=true }
function submit(){ if(isEdit.value){ form.put(`/spa/mak/${form.id}`, { onSuccess: ()=> showModal.value=false }) } else { form.post('/spa/mak', { onSuccess: ()=> showModal.value=false }) } }
function doDelete(row){ router.delete(`/spa/mak/${row.id}`, { preserveState: true }) }

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
  f.post('/spa/mak/import', { forceFormData: true, onFinish: () => { importing.value = false; importFile.value=null; showImport.value=false } })
}
</script>
