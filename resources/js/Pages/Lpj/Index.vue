<template>
  <AdminLayout title="LPJ">
    <div class="max-w-7xl mx-auto">
      <PageHeader title="LPJ" :crumbs="[{ label: 'Dashboard', href: '/spa' }, { label: 'Keuangan' }, { label: 'LPJ' }]">
        <template #actions>
          <Toolbar v-model="q" placeholder="Cari nomor/kegiatan" @search="search">
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
      <n-modal v-model:show="showModal" preset="dialog" :title="isEdit ? 'Ubah LPJ' : 'Tambah LPJ'">
        <n-form label-placement="top">
          <n-form-item label="Nomor" :feedback="form.errors?.nomor_lpj">
            <n-input v-model:value="form.nomor_lpj" placeholder="Nomor LPJ" />
          </n-form-item>
          <n-form-item label="Tanggal Masuk" :feedback="form.errors?.tanggal_masuk">
            <DatePicker v-model="form.tanggal_masuk" />
          </n-form-item>
          <n-form-item label="Kegiatan" :feedback="form.errors?.kegiatan">
            <n-input v-model:value="form.kegiatan" placeholder="Kegiatan" />
          </n-form-item>
          <n-form-item label="Nilai" :feedback="form.errors?.nilai">
            <n-input v-model:value="form.nilai" placeholder="Nilai" />
          </n-form-item>
          <n-form-item label="Status" :feedback="form.errors?.status">
            <n-input v-model:value="form.status" placeholder="Status" />
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

const props = defineProps({ items: Object, search: String, sort: String, dir: String })
const q = ref(props.search || '')
const page = ref(props.items.current_page || 1)
const pageCount = computed(() => props.items.last_page || 1)
const rows = computed(() => props.items.data || [])
const columns = [
  { title: 'Nomor', key: 'nomor_lpj', sorter: 'default' },
  { title: 'Tanggal Masuk', key: 'tanggal_masuk', sorter: 'default' },
  { title: 'Kegiatan', key: 'kegiatan', sorter: 'default' },
  { title: 'Nilai', key: 'nilai', sorter: 'default' },
  { title: 'Status', key: 'status', sorter: 'default' },
  { title: '', key: 'actions', render(row){ return h('div', { class: 'text-right' }, [
    h(NButton, { text: true, size: 'small', onClick: () => openEdit(row) }, { default: () => 'Edit' }),
    h(NButton, { text: true, type: 'error', size: 'small', onClick: () => doDelete(row) }, { default: () => 'Hapus' }),
  ]) } }
]
function search(){ router.get('/spa/lpj', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
function goPage(p){ router.get('/spa/lpj', { q: q.value, page: p, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true }) }
watch(() => props.items.current_page, (val)=>{ if (val) page.value = val })
const sort = ref(props.sort || '')
const dir = ref(props.dir || 'asc')
function onSort(sorter){
  if (!sorter || !sorter.columnKey || !sorter.order){ sort.value=''; dir.value='asc' }
  else { sort.value = sorter.columnKey; dir.value = sorter.order === 'descend' ? 'desc' : 'asc' }
  router.get('/spa/lpj', { q: q.value, sort: sort.value, dir: dir.value }, { preserveState: true, replace: true })
}
const showModal = ref(false)
const isEdit = ref(false)
const form = useForm({ id: null, nomor_lpj: '', tanggal_masuk: '', kegiatan: '', nilai: '', status: '' })
function openCreate(){ isEdit.value=false; form.reset(); showModal.value=true }
function openEdit(row){ isEdit.value=true; Object.assign(form, { id: row.id, nomor_lpj: row.nomor_lpj, tanggal_masuk: row.tanggal_masuk, kegiatan: row.kegiatan, nilai: row.nilai, status: row.status }); showModal.value=true }
function submit(){ if(isEdit.value){ form.put(`/spa/lpj/${form.id}`, { onSuccess: ()=> showModal.value=false }) } else { form.post('/spa/lpj', { onSuccess: ()=> showModal.value=false }) } }
function doDelete(row){ router.delete(`/spa/lpj/${row.id}`, { preserveState: true }) }
</script>
